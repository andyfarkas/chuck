<?php

namespace DixonsCz\Chuck\Jira;

class Client implements IClient
{
    /**
     * @var IConfiguration
     */
    protected $configuration;
    
    /**
     * @var IHttpRequest
     */
    protected $request;
    
    /**
     * @param IConfiguration $config
     * @param IHttpRequest $request
     */
    public function __construct(IConfiguration $config, IHttpRequest $request)
    {
        $this->configuration = $config;
        $this->request = $request;
    }

    /**
     *
     * @param string $path
     * @throws \DixonsCz\Jira\JiraException
     * @return IResponse
     */
    public function requestPath($path)
    {
        $requestUrl = $this->configuration->getApiUrl() . $path;
        $responseBody = $this->request->getAuthorizationResponse($requestUrl, $this->configuration->getUsername(), $this->configuration->getPassword());

        if($responseBody === null) {
            throw new \DixonsCz\Jira\JiraException("Issue not found.");
        }
        return new Response($responseBody);
    }

}