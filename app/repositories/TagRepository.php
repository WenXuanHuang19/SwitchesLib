<?php

class TagRepository
{
    public function __construct(private PDO $pdo) {}

    /** All tags ordered by type then name, for the read-only reference page. */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM tags ORDER BY type, name ASC'
        );
        return $stmt->fetchAll();
    }
}
