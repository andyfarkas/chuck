<?php

namespace DixonsCz\Chuck\Jira\Tests;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @test
     */
    public function authorizationResponse_ReturnsCorrectResponse()
    {            
        
        $configuration = $this->getMock('DixonsCz\Chuck\Jira\IConfiguration');
        $configuration->expects($this->any())
                        ->method('getApiUrl')
                        ->will($this->returnValue('api_url'));
        
        $configuration->expects($this->any())
                        ->method('getPassword')
                        ->will($this->returnValue('password'));
        
        $configuration->expects($this->any())
                        ->method('getUsername')
                        ->will($this->returnValue('username'));
        
        
        
        $httpRequest = $this->getMock('DixonsCz\Chuck\Jira\IHttpRequest');
        $httpRequest->expects($this->any())
                        ->method('getAuthorizationResponse')
                        ->with('api_url'.'path','username','password')
                        ->will($this->returnValue('authorization_response_body'));
        
        
        $expectedResponse = new \DixonsCz\Chuck\Jira\Response('authorization_response_body');
        
        $client = new \DixonsCz\Chuck\Jira\Client($configuration, $httpRequest);
        $response = $client->requestPath('path');
        $this->assertEquals($expectedResponse, $response);
        
    }
    
}