<?php

/** @var Router $router */

$router->get('/', 'HomeController@index');
$router->get('/switches', 'SwitchController@index');
$router->get('/switches/{slug}', 'SwitchController@show');
