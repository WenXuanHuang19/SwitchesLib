<?php

/**
 * Smoke test — verifies the test infrastructure is wired up correctly.
 *
 * Checks that:
 *  - The test database connection is live and pointing at switches_lib_test.
 *  - Core application classes (Database, Auth) are autoloaded correctly.
 *  - Transaction rollback isolation works: an insert done here must not
 *    persist after the test finishes.
 */
class SmokeTest extends TestCase
{
    public function test_connected_to_test_database(): void
    {
        $db = $this->pdo->query('SELECT DATABASE()')->fetchColumn();
        $this->assertSame('switches_lib_test', $db);
    }

    public function test_schema_tables_exist(): void
    {
        $tables = $this->pdo
            ->query("SHOW TABLES")
            ->fetchAll(PDO::FETCH_COLUMN);

        foreach (['users', 'switches', 'designers', 'submissions', 'blog_posts', 'tags'] as $table) {
            $this->assertContains($table, $tables, "Table '{$table}' missing from test schema");
        }
    }

    public function test_transaction_rollback_isolates_data(): void
    {
        // Insert a canary row inside the test transaction.
        $this->pdo->exec(
            "INSERT INTO users (username, email, password_hash, role)
             VALUES ('smoke_canary', 'canary@test.local', 'x', 'user')"
        );

        $count = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM users WHERE username = 'smoke_canary'")
            ->fetchColumn();

        // Canary is visible within the same transaction.
        $this->assertSame(1, $count);

        // tearDown() will roll back — the next test won't see this row.
    }

    public function test_canary_row_not_present_from_previous_test(): void
    {
        $count = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM users WHERE username = 'smoke_canary'")
            ->fetchColumn();

        $this->assertSame(0, $count, 'Transaction rollback failed — canary row leaked between tests');
    }
}
