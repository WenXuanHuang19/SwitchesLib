<?php

/**
 * Test database helper.
 *
 * Connects to a separate `switches_lib_test` database (using the same
 * credentials as the app) and loads the real schema into it once per run, so
 * integration tests never touch development data. Provides fixture helpers.
 */

function test_pdo(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $config = require dirname(__DIR__) . '/config/config.php';
    $db = $config['db'];
    $testDbName = $db['name'] . '_test';

    // Connect without a default database so we can create the test database.
    $dsn = "mysql:host={$db['host']};port={$db['port']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Load the real schema, redirected at the test database.
    $schema = file_get_contents(dirname(__DIR__) . '/database/schema.sql');
    $schema = preg_replace('/CREATE DATABASE.*?;/is', '', $schema);
    $schema = preg_replace('/USE\s+\w+\s*;/i', '', $schema);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$testDbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE {$testDbName}");
    $pdo->exec($schema);

    return $pdo;
}

/** Empty all data tables so each test starts from a known state. */
function reset_db(PDO $pdo): void
{
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach (['submissions', 'switches', 'blog_posts', 'tags', 'designers', 'users'] as $table) {
        $pdo->exec("TRUNCATE TABLE {$table}");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}

/**
 * Insert a switch row with sensible defaults; pass overrides to set specific
 * columns. Returns the new row id.
 */
function insert_switch(PDO $pdo, array $overrides = []): int
{
    $row = array_merge([
        'slug'            => 'switch-' . bin2hex(random_bytes(4)),
        'name'            => 'Test Switch',
        'switch_type'     => 'Linear',
        'sound_profile'   => 'Unknown',
        'feel_profile'    => 'Unknown',
        'recommended_use' => 'Unknown',
        'status'          => 'approved',
    ], $overrides);

    $columns = implode(', ', array_keys($row));
    $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($row)));

    $stmt = $pdo->prepare("INSERT INTO switches ({$columns}) VALUES ({$placeholders})");
    $stmt->execute($row);

    return (int) $pdo->lastInsertId();
}

/** Insert a user with sensible defaults; returns the new row id. */
function insert_user(PDO $pdo, array $overrides = []): int
{
    $row = array_merge([
        'username'      => 'user-' . bin2hex(random_bytes(4)),
        'email'         => bin2hex(random_bytes(4)) . '@example.com',
        'password_hash' => 'x',
        'role'          => 'user',
    ], $overrides);

    $columns = implode(', ', array_keys($row));
    $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($row)));

    $stmt = $pdo->prepare("INSERT INTO users ({$columns}) VALUES ({$placeholders})");
    $stmt->execute($row);

    return (int) $pdo->lastInsertId();
}

/** Insert a designer with a default name; returns the new row id. */
function insert_designer(PDO $pdo, array $overrides = []): int
{
    $row = array_merge(['name' => 'Designer ' . bin2hex(random_bytes(4))], $overrides);

    $columns = implode(', ', array_keys($row));
    $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($row)));

    $stmt = $pdo->prepare("INSERT INTO designers ({$columns}) VALUES ({$placeholders})");
    $stmt->execute($row);

    return (int) $pdo->lastInsertId();
}

/**
 * Insert a submission row with sensible defaults; pass overrides to set
 * specific columns (e.g. status). Returns the new row id.
 */
function insert_submission(PDO $pdo, array $overrides = []): int
{
    $row = array_merge([
        'user_id'     => insert_user($pdo),
        'name'        => 'Test Submission',
        'designer_id' => insert_designer($pdo),
        'switch_type' => 'Linear',
        'status'      => 'Pending',
    ], $overrides);

    $columns = implode(', ', array_keys($row));
    $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($row)));

    $stmt = $pdo->prepare("INSERT INTO submissions ({$columns}) VALUES ({$placeholders})");
    $stmt->execute($row);

    return (int) $pdo->lastInsertId();
}

/**
 * Insert a blog post with sensible defaults; pass overrides to set specific
 * columns. Returns the new row id.
 */
function insert_blog_post(PDO $pdo, array $overrides = []): int
{
    $row = array_merge([
        'slug'   => 'post-' . bin2hex(random_bytes(4)),
        'title'  => 'Test Post',
        'status' => 'published',
    ], $overrides);

    $columns = implode(', ', array_keys($row));
    $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($row)));

    $stmt = $pdo->prepare("INSERT INTO blog_posts ({$columns}) VALUES ({$placeholders})");
    $stmt->execute($row);

    return (int) $pdo->lastInsertId();
}
