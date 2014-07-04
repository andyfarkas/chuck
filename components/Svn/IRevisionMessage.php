<?php

namespace DixonsCz\Chuck\Svn;

interface IRevisionMessage
{
    /**
     * 
     * @param \DixonsCz\Chuck\Jira\Wrapper $jira
     * @return \DixonsCz\Chuck\Jira\IIssue
     */
    public function findJiraIssue(\DixonsCz\Chuck\Jira\Wrapper $jira);

    /**
     * @return mixed[]
     */
    public function toArray();
}