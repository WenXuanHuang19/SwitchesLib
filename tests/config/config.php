<?php
/**
 * Test database configuration.
 * Points to switches_lib_test to keep test data isolated from development data.
 * Bootstrap creates/resets this DB automatically before each test run.
 */
return [
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => '3306',
        'name'     => 'switches_lib_test',
        'user'     => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
];
