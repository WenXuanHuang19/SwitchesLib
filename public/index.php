<?php

// Front controller — every request that isn't a real file lands here.

require dirname(__DIR__) . '/bootstrap.php';

$router = new Router();
require APP_PATH . '/routes.php';

// Resolve the route path by stripping the base path from the request URI.
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routePath = substr($requestPath, strlen(BASE_PATH));
if ($routePath === '' || $routePath === false) {
    $routePath = '/';
}

$router->dispatch($_SERVER['REQUEST_METHOD'], $routePath);
