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

    private function one(string $sql, array $params): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
