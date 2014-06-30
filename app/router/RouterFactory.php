<?php

namespace  DixonsCz\Chuck;

use \Nette\Application\Routers\Route;

class RouterFactory
{

    /**
     * @return \Nette\Application\IRouter
     */
    public static function createRouter()
    {
        $router = new \Nette\Application\Routers\RouteList();
        $router[] = new Route('api/<project>/<action>[/<id>]', array('presenter' => 'Api'));
        $router[] = new Route('<presenter>/<action>[/<id>]', 'Dashboard:default');
        return $router;
    }
}
