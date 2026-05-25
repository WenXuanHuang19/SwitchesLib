<?php

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case for Switches Lib unit tests.
 *
 * Wraps each test in a database transaction that is rolled back in tearDown(),
 * so every test starts from a clean schema state without touching other tests.
 *
 * Usage:
 *   class MyTest extends TestCase { ... }
 */
abstract class TestCase extends BaseTestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Database::pdo();
        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        parent::tearDown();
    }
}
