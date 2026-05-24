<?php

class AuthController
{
    public function showRegister(): void
    {
        view('auth/register', ['errors' => [], 'old' => []]);
    }

    public function register(): void
    {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];
        if ($username === '') $errors['username'] = 'Username is required.';
        if ($email === '')    $errors['email']    = 'Email is required.';
        if ($password === '') $errors['password'] = 'Password is required.';

        if ($errors === []) {
            $result = Auth::register(new UserRepository(Database::pdo()), $username, $email, $password);

            if (isset($result['errors'])) {
                $errors = $result['errors'];
            } else {
                Auth::login($result['user']);
                header('Location: ' . url('/'));
                exit;
            }
        }

        view('auth/register', ['errors' => $errors, 'old' => ['username' => $username, 'email' => $email]]);
    }

    public function showLogin(): void
    {
        view('auth/login', ['error' => null, 'old' => []]);
    }

    public function login(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = Auth::verifyCredentials(new UserRepository(Database::pdo()), $email, $password);

        if ($user === null) {
            // Deliberately vague — don't reveal whether the email or password was wrong.
            view('auth/login', ['error' => 'Invalid email or password.', 'old' => ['email' => $email]]);
            return;
        }

        Auth::login($user);
        header('Location: ' . url('/'));
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: ' . url('/'));
        exit;
    }
}
