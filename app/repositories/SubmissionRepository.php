<?php

/**
 * Data access for user-submitted switches awaiting review. Receives a PDO so it
 * can run against any database (the app passes Database::pdo(); tests pass a
 * test connection).
 */
class SubmissionRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Store a new submission for the given user. $data holds the normalized
     * switch columns (from Submission::validate). Status is always 'Pending';
     * returns the new row id.
     */
    public function create(int $userId, array $data): int
    {
        $row = array_merge($data, [
            'user_id' => $userId,
            'status'  => 'Pending',
        ]);

        $columns      = implode(', ', array_keys($row));
        $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($row)));

        $stmt = $this->pdo->prepare("INSERT INTO submissions ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($row);

        return (int) $this->pdo->lastInsertId();
    }

    /** All submissions for a user, newest first, with the designer's name. */
    public function forUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.*, d.name AS designer_name
             FROM submissions s
             LEFT JOIN designers d ON d.id = s.designer_id
             WHERE s.user_id = :user_id
             ORDER BY s.created_at DESC, s.id DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
