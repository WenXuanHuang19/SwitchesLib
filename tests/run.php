<?php

// Test entry point:  php tests/run.php

require __DIR__ . '/framework.php';
require __DIR__ . '/db.php';

// Autoload application classes (core + repositories) for tests.
spl_autoload_register(function (string $class): void {
    foreach (['core', 'repositories'] as $dir) {
        $file = dirname(__DIR__) . '/app/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

// Load every *Test.php file under tests/.
foreach (glob(__DIR__ . '/*Test.php') as $file) {
    require $file;
}

exit(run_tests());
