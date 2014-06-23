<?php

use Nette\Diagnostics\Debugger;
use Nette\Application\Routers\SimpleRouter;

define('ROOT_DIR', realpath(__DIR__ . '/../'));
define('WWW_DIR', __DIR__);
define('APP_DIR', ROOT_DIR . '/app');
define('LIBS_DIR', ROOT_DIR . '/libs');

// Load libraries
require ROOT_DIR . '/vendor/autoload.php';
//\Nette\Framework::$iAmUsingBadHost = TRUE;
//\Nette\Diagnostics\Debugger::enable(false);
\Nette\Diagnostics\Debugger::enable(Debugger::DEVELOPMENT);

// Configure application
$configurator = new \Nette\Configurator();
$configurator->setDebugMode(true);
$configurator->setTempDirectory(ROOT_DIR . '/temp');
$configurator->createRobotLoader()
    ->addDirectory(APP_DIR)
    ->addDirectory(ROOT_DIR . '/components')
//    ->addDirectory(LIBS_DIR)
    ->register();

// basic environment resolution
$environment = null;

// specific configuration for Dixons machines
if (false !== getenv('EM_ENV')) { // on DEV EM_ENV is set
    $environment = "dixdev";
} elseif (false !== strstr($_SERVER["SERVER_SOFTWARE"], "nginx")) { // jenkins server runs on nginx
    $environment = "jenkins";
    $configurator->setDebugMode(false);
    \Nette\Diagnostics\Debugger::enable(Debugger::PRODUCTION);
}

$configurator->addConfig(ROOT_DIR . '/app/config/config.neon', $environment);
$container = $configurator->createContainer();
$container->application->catchExceptions = false;

$router = $container->application->getRouter();
$router[] = new SimpleRouter('Dashboard:default');

// Run the application!
$container->application->run();
