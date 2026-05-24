<?php

// Router script for PHP's built-in dev server:
//   php -S localhost:8000 -t public public/router.php
//
// Serves existing static files as-is; routes everything else to index.php.

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false; // let the built-in server serve the static file
}

require __DIR__ . '/index.php';
