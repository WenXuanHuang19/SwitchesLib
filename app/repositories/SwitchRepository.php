<?php

/**
 * All Switch data access. Receives a PDO so it can run against any database
 * (the app passes Database::pdo(); tests pass a test connection).
 */
class SwitchRepository
{
    public function __construct(private PDO $pdo) {}

    /** All approved switches for public listing. */
    public function allApproved(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM switches WHERE status = 'approved' ORDER BY created_at DESC"
        );
        return $stmt->fetchAll();
    }
}
