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

$router->get('/admin/designers', 'AdminDesignerController@index');
$router->get('/admin/designers/add', 'AdminDesignerController@create');
$router->post('/admin/designers', 'AdminDesignerController@store');
$router->get('/admin/designers/{id}/edit', 'AdminDesignerController@edit');
$router->post('/admin/designers/{id}', 'AdminDesignerController@update');
$router->post('/admin/designers/{id}/delete', 'AdminDesignerController@destroy');

$router->get('/admin/blog', 'AdminBlogController@index');
$router->get('/admin/blog/add', 'AdminBlogController@create');
$router->post('/admin/blog', 'AdminBlogController@store');
$router->get('/admin/blog/{id}/edit', 'AdminBlogController@edit');
$router->post('/admin/blog/{id}', 'AdminBlogController@update');
$router->post('/admin/blog/{id}/delete', 'AdminBlogController@destroy');

$router->get('/admin/users', 'AdminUserController@index');
$router->post('/admin/users/{id}/role', 'AdminUserController@updateRole');
