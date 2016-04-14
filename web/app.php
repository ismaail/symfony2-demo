<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

defined('APP_ENV')
    || define('APP_ENV', (getenv('APP_ENV') ? getenv('APP_ENV') : 'prod'));

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

if ('dev' === APP_ENV) {
    Debug::enable();
}

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

$kernel = new AppKernel(APP_ENV, ('dev' === APP_ENV));

if ('prod' === APP_ENV) {
    $kernel->loadClassCache();
    //$kernel = new AppCache($kernel);
}

/* When using the HttpCache, you need to call the method in your front controller
   instead of relying on the configuration parameter. */
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
