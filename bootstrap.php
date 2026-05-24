<?php

// Paths
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', APP_PATH . '/views');

// Autoload core classes and controllers by class name.
spl_autoload_register(function (string $class): void {
    foreach (['core', 'controllers', 'repositories'] as $dir) {
        $file = APP_PATH . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require APP_PATH . '/helpers.php';

// Every request runs within a session so we know who is logged in.
session_start();

// Detect the base path so routing and links work whether the app is served
// from the domain root (php -S) or a subdirectory (XAMPP /SwitchesLib/public).
define('BASE_PATH', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));
