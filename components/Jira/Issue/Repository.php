<?php

namespace DixonsCz\Chuck\Jira\Issue;

class Repository implements IRepository
{
    
    /**
     *
     * @var \DixonsCz\Chuck\Jira\Request\IFactory
     */
    protected $requestsFactory;
    
    /**
     *
     * @var \DixonsCz\Chuck\Jira\IClient
     */
    protected $client;
    
    /**
     *
     * @var \DixonsCz\Chuck\Jira\Response\ITransformer
     */
    protected $singleIssueTransformer;
    
    /**
     * 
     * @param \DixonsCz\Chuck\Jira\Request\IFactory $requestFactory
     * @param \DixonsCz\Chuck\Jira\IClient $client
     * @param \DixonsCz\Chuck\Jira\Response\Transformer\ISingleIssue
     */
    public function __construct(\DixonsCz\Chuck\Jira\Request\IFactory $requestFactory, \DixonsCz\Chuck\Jira\IClient $client, \DixonsCz\Chuck\Jira\Response\Transformer\ISingleIssue $singleIssueTransformer)
    {
        $this->requestsFactory = $requestFactory;
        $this->client = $client;
        $this->singleIssueTransformer = $singleIssueTransformer;
    }
    
    /**
     * 
     * @param string $key
     * @return \DixonsCz\Chuck\Jira\IIssue
     */
    public function findIssueByKey($key)
    {
        $request = $this->requestsFactory->createIssueRequestByKey($key);
        $response = $request->send($this->client);

        return $response->transform($this->singleIssueTransformer);
    }
}
