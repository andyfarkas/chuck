<?php

namespace DixonsCz\Chuck\Log;

/**
 * @author Michal Svec <michal.svec@dixonsretail.com>
 */
class Generator
{
    const ORDER_PRIORITY  = 'priority';
    const ORDER_ISSUETYPE = 'type';

    /**
     *
     * @var \DixonsCz\Chuck\Jira\Wrapper
     */
    private $jiraHelper;

    /**
     *
     * @var \DixonsCz\Chuck\Svn\RevisionMessage\IParser
     */
    private $revisionMessageParser;
    
    /**
     * 
     * @param \DixonsCz\Chuck\Jira\Wrapper $jiraHelper
     * @param \DixonsCz\Chuck\Svn\RevisionMessage\IParser $revisionMessageParser
     */
    public function __construct(\DixonsCz\Chuck\Jira\Wrapper $jiraHelper, \DixonsCz\Chuck\Svn\RevisionMessage\IParser $revisionMessageParser)
    {
        $this->jiraHelper = $jiraHelper;
        $this->revisionMessageParser = $revisionMessageParser;
    }

    /**
     * @param  array  $revisions
     * @param  string $orderBy
     * @return array
     */
    public function generateTicketLog($revisions, $orderBy = self::ORDER_ISSUETYPE)
    {
        $log = $this->getTicketInformation($revisions);

        switch ($orderBy) {
            case self::ORDER_PRIORITY:
                $log['ALL'] = $this->orderByPriority($log['ALL']);
                break;

            case self::ORDER_ISSUETYPE:
            default:
                $log['ALL'] = $this->orderByIssueType($log['ALL']);
                break;
        }

        return $log;
    }

   /**
     * Gets the array with all necessary information about tickets
     *
     * @param  array  $logList
     * @return array
     */
    protected function getTicketInformation($logList)
    {
        $ticketLog = array(
            'ALL' => array(),
            'OTHER' => array(),
        );
        $issues = array();
        foreach ($logList as $logLine)
        {
            $revisionMessage = $this->revisionMessageParser->parseFromString($logLine['msg']);
            $jiraIssue = $revisionMessage->findJiraIssue($this->jiraHelper);
            if ($jiraIssue != null)
            {
                $issues[] = $jiraIssue;
            }
            else
            {
                $ticketLog['ALL'][] = $revisionMessage->toArray();
                $ticketLog['OTHER'][] = $revisionMessage->toArray();
            }
        }

        $rfcIssues = array_filter($issues, function(\DixonsCz\Chuck\Jira\IIssue $issue){
            return $issue->isRFC();
        });
        
        $bugIssues = array_filter($issues, function(\DixonsCz\Chuck\Jira\IIssue $issue){
            return $issue->isBug();
        });
        
        $supportIssues = array_filter($issues, function(\DixonsCz\Chuck\Jira\IIssue $issue){
            return $issue->isSupportRequest();
        });
        
        $otherIssues = array_filter($issues, function(\DixonsCz\Chuck\Jira\IIssue $issue){
            return $issue->isOther();
        });        
        
        $issueToArrayFormatter = function(\DixonsCz\Chuck\Jira\IIssue $issue)
        {
            return $issue->toArray();
        };
        
        $ticketLog['RFC'] = array_map($issueToArrayFormatter, $rfcIssues);
        $ticketLog['BUG'] = array_map($issueToArrayFormatter, $bugIssues);
        $ticketLog['SUPPORT'] = array_map($issueToArrayFormatter, $supportIssues);
        $ticketLog['OTHER'] = array_merge($ticketLog['OTHER'], array_map($issueToArrayFormatter, $otherIssues));
        $ticketLog['ALL'] = array_merge($ticketLog['ALL'], array_map($issueToArrayFormatter, $issues));

        return $ticketLog;
    }

    /**
     * Orders that RFCs are 1st, bugfixes 2nd, support 3rd and the rest below
     *
     * @param  array $issues
     * @return array
     */
    private function orderByIssueType($issues)
    {
        return $issues;
    }

    /**
     * P1 at the top, then P2, .....
     *
     * @param $issues
     * @return mixed
     */
    private function orderByPriority($issues)
    {
        return $issues;
    }

    /**
     * @param $message
     * @return array
     */
    public function parseRevisionMessage($message)
    {
        preg_match('~([A-Z]+[-][0-9]+)~s', $message, $matches);

        // remove ticket number because it will be added manually from parsed number
        if (isset($matches[1])) {
            $message = str_replace(array("[{$matches[1]}]", $matches[1], "[]"), "", $message);
        }

        return array(
            'ticket' => isset($matches[1]) ? $matches[1] : "", // trim($matches[2], "[]"),
            'message' => $message
        );
    }

}
