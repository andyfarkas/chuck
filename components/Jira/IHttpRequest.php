<?php

namespace DixonsCz\Chuck\Jira;

interface IHttpRequest {

    /**
     * 
     * @param string $url
     * @param string $username     
     * @param string $password
     * @return string
     */
    public function getAuthorizationResponse($url, $username, $password);
}