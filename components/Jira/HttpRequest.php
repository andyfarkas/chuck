<?php

namespace DixonsCz\Chuck\Jira;

class HttpRequest implements IHttpRequest
{

    /**
     * 
     * @param string $url
     * @param string $username     
     * @param string $password
     * @return string
     */
    public function getAuthorizationResponse($url, $username, $password)
    {
        $request = new \Kdyby\Curl\Request($url);
        $request->headers['Authorization'] = 'Basic ' . base64_encode("{$username}:{$password}");
        $request->setFollowRedirects(TRUE);
        return $request->get()->getResponse();
    }
                                                                                                
}
