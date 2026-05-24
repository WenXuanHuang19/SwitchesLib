<?php

/** @var Router $router */

$router->get('/', 'HomeController@index');
$router->get('/switches', 'SwitchController@index');
$router->get('/switches/{slug}', 'SwitchController@show');
$router->get('/blog', 'BlogController@index');
$router->get('/blog/{slug}', 'BlogController@show');

$router->get('/submit', 'SubmitController@show');
$router->post('/submit', 'SubmitController@store');
$router->get('/my-submissions', 'SubmitController@mySubmissions');

$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout');

$router->get('/admin', 'AdminController@dashboard');

$router->get('/admin/switches', 'AdminSwitchController@index');
$router->get('/admin/switches/add', 'AdminSwitchController@create');
$router->post('/admin/switches', 'AdminSwitchController@store');
$router->get('/admin/switches/{id}/edit', 'AdminSwitchController@edit');
$router->post('/admin/switches/{id}/delete', 'AdminSwitchController@destroy');
$router->post('/admin/switches/{id}', 'AdminSwitchController@update');
