<?php

namespace DixonsCz\Chuck\Jira;

use Psr\Log\LoggerInterface;

/**
 * Example: https://jira.example.com/rest/api/latest/issue/EXPVY-325
 *
 * @author Michal Svec <michal.svec@dixonsretail.com>
 */
class Wrapper extends \Nette\Object
{
    /**
     * @var Issue\IRepository
     */
    private $issuesRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @param \DixonsCz\Chuck\Jira\Issue\IRepository $issuesRepository
     * @param LoggerInterface $logger
     */
    public function __construct(Issue\IRepository $issuesRepository, LoggerInterface $logger)
    {
        $this->issuesRepository = $issuesRepository;
        $this->logger = $logger;
    }

    /**
     *
     * @param string $key
     * @return IIssue
     */
    public function getTicketInfo($key)
    {
        $this->logger->info('JIRA Request: ' . $key);
        $issue = $this->issuesRepository->findIssueByKey($key);
        $this->logger->info('JIRA Request: ' . $key, $issue->toArray());

        return $issue;
    }

}
