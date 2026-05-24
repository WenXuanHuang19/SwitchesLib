<?php

/**
 * All user data access. Receives a PDO so it can run against any database
 * (the app passes Database::pdo(); tests pass a test connection).
 */
class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?array
    {
        return $this->one("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }

    public function findByUsername(string $username): ?array
    {
        return $this->one("SELECT * FROM users WHERE username = :username", ['username' => $username]);
    }

    public function findById(int $id): ?array
    {
        return $this->one("SELECT * FROM users WHERE id = :id", ['id' => $id]);
    }

    /** Insert a user (password must already be hashed). Returns the new id. */
    public function create(string $username, string $email, string $passwordHash, string $role = 'user'): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (username, email, password_hash, role)
             VALUES (:username, :email, :password_hash, :role)"
        );
        $stmt->execute([
            'username'      => $username,
            'email'         => $email,
            'password_hash' => $passwordHash,
            'role'          => $role,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** All users, newest first, for the admin user list. */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    /** Change a user's role. No validation — the caller must guard. */
    public function updateRole(int $id, string $role): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute(['role' => $role, 'id' => $id]);
    }

    /** Total number of users, for the admin dashboard. */
    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    private function one(string $sql, array $params): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
