<?php

namespace DixonsCz\Chuck\Jira\Request;

class Issue implements \DixonsCz\Chuck\Jira\IRequest
{        
    protected $key;
    
    protected $path = 'issue/';
    
    public function __construct($key)
    {
        $this->key = $key;
    }
    
    /**
     * 
     * @param \DixonsCz\Chuck\Jira\IClient $client
     * @return \DixonsCz\Chuck\Jira\IResponse
     */
    public function send(\DixonsCz\Chuck\Jira\IClient $client)
    {
        return $client->requestPath($this->path . $this->key);
    }

}
