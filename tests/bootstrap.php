<?php
/**
 * PHPUnit bootstrap for Switches Lib.
 *
 * Responsibilities:
 *  1. Define the same path constants used by the application.
 *  2. Register the same spl_autoload used in app/bootstrap.php so every core
 *     class, repository, and controller is available in tests.
 *  3. Point CONFIG_PATH at tests/config/ so Database::pdo() connects to the
 *     isolated switches_lib_test database instead of the real one.
 *  4. Reset the test database to a clean schema before the test suite runs.
 */

define('ROOT_PATH',  dirname(__DIR__));
define('APP_PATH',   ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/tests/config');   // ← test DB, not production
define('VIEWS_PATH', APP_PATH . '/views');

// Composer autoloader (PHPUnit itself).
require ROOT_PATH . '/vendor/autoload.php';

// Application class autoloader — mirrors app/bootstrap.php.
// Also searches tests/ for the shared TestCase base class.
spl_autoload_register(function (string $class): void {
    $searchDirs = [
        APP_PATH . '/core',
        APP_PATH . '/controllers',
        APP_PATH . '/repositories',
        ROOT_PATH . '/tests',
    ];
    foreach ($searchDirs as $dir) {
        $file = $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

require_once APP_PATH . '/helpers.php';

// ---------------------------------------------------------------------------
// Reset the test database to a clean schema before the suite runs.
// ---------------------------------------------------------------------------
$cfg = require CONFIG_PATH . '/config.php';
$db  = $cfg['db'];

// Connect without a default database so we can CREATE DATABASE IF NOT EXISTS.
$rootDsn = sprintf('mysql:host=%s;port=%s;charset=%s', $db['host'], $db['port'], $db['charset']);
$rootPdo  = new PDO($rootDsn, $db['user'], $db['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$rootPdo->exec(
    "CREATE DATABASE IF NOT EXISTS `{$db['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
);

// Connect to the test database.
$testDsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $db['host'], $db['port'], $db['name'], $db['charset']
);
$testPdo = new PDO($testDsn, $db['user'], $db['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// Load schema and adapt it for the test database name, then execute
// statement-by-statement so we don't need PDO multi-statement mode.
$schema = file_get_contents(ROOT_PATH . '/database/schema.sql');

// Replace the production DB name in CREATE DATABASE / USE statements.
$schema = str_replace(
    ['switches_lib ', 'USE switches_lib;'],
    [$db['name'] . ' ', 'USE ' . $db['name'] . ';'],
    $schema
);

// Strip SQL comments (-- …) and split on semicolons.
$lines = explode("\n", $schema);
$lines = array_filter($lines, fn($l) => !str_starts_with(ltrim($l), '--'));
$clean = implode("\n", $lines);

foreach (array_filter(array_map('trim', explode(';', $clean))) as $stmt) {
    $testPdo->exec($stmt);
}

unset($rootPdo, $testPdo, $cfg, $db, $schema, $clean, $lines);
