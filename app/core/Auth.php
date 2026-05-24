<?php

/**
 * Authentication: registration, credential checking, session, and route guards.
 *
 * register() and verifyCredentials() are pure with respect to the HTTP session
 * (they only touch the database via UserRepository), which keeps the security-
 * critical logic testable. The session methods below carry the side effects.
 */
class Auth
{
    /**
     * Validate and create a new account. Returns ['errors' => [...]] on failure
     * or ['user' => [...]] on success. New accounts default to the 'user' role.
     */
    public static function register(UserRepository $users, string $username, string $email, string $password): array
    {
        $errors = [];

        if ($users->findByUsername($username) !== null) {
            $errors['username'] = 'That username is already taken.';
        }

        if ($users->findByEmail($email) !== null) {
            $errors['email'] = 'That email is already registered.';
        }

        if ($errors !== []) {
            return ['errors' => $errors];
        }

        $id = $users->create($username, $email, password_hash($password, PASSWORD_DEFAULT));

        return ['user' => $users->findById($id)];
    }

    /** Return the user if the email/password pair is valid, otherwise null. */
    public static function verifyCredentials(UserRepository $users, string $email, string $password): ?array
    {
        $user = $users->findByEmail($email);

        if ($user !== null && password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return null;
    }

    // --- Session side (relies on a started session; not unit-tested) ---

    public static function login(array $user): void
    {
        $_SESSION['user_id']  = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function username(): ?string
    {
        return $_SESSION['username'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['role'] ?? null) === 'admin';
    }

    /** Redirect guests to the login page. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . url('/login'));
            exit;
        }
    }

    /** Block non-admins with a 403. */
    public static function requireAdmin(): void
    {
        self::requireLogin();

        if (!self::isAdmin()) {
            http_response_code(403);
            view('errors/403');
            exit;
        }
    }
}
