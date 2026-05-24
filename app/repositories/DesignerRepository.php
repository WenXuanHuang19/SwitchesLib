<?php

class DesignerRepository
{
    public function __construct(private PDO $pdo) {}

    /** All designers, ordered by name for the admin list. */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM designers ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    /** A single designer, or null when not found. */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM designers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Insert a designer from column data. Returns the new row id. */
    public function create(array $data): int
    {
        unset($data['id']);
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($data)));
        $stmt = $this->pdo->prepare("INSERT INTO designers ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    /** Update the given columns of a designer. 'id' in $data is ignored. */
    public function update(int $id, array $data): void
    {
        unset($data['id']);
        if ($data === []) return;
        $assignments = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $data['id']  = $id;
        $stmt = $this->pdo->prepare("UPDATE designers SET {$assignments} WHERE id = :id");
        $stmt->execute($data);
    }

    /** Hard-delete a designer. */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM designers WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** Whether any switches reference this designer (FK blocker). */
    public function hasSwitches(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM switches WHERE designer_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetchColumn() !== false;
    }
}
