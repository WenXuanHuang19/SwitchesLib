<?php

/**
 * Unit tests for Auth::register() and Auth::verifyCredentials().
 *
 * Both methods are pure with respect to HTTP sessions — they only touch the
 * database. Tests drive them through a real UserRepository backed by the
 * test DB and roll back after each test.
 */
class AuthTest extends TestCase
{
    private UserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();
        $this->users = new UserRepository($this->pdo);
    }

    // -----------------------------------------------------------------------
    // Auth::register()
    // -----------------------------------------------------------------------

    public function test_register_returns_user_with_hashed_password(): void
    {
        $result = Auth::register($this->users, 'alice', 'alice@example.com', 'secret123');

        $this->assertArrayHasKey('user', $result);

        $user = $result['user'];
        $this->assertSame('alice', $user['username']);
        $this->assertSame('alice@example.com', $user['email']);
        // Password must be bcrypt-hashed, not plain text.
        $this->assertNotSame('secret123', $user['password_hash']);
        $this->assertTrue(password_verify('secret123', $user['password_hash']));
    }

    public function test_register_new_account_defaults_to_user_role(): void
    {
        $result = Auth::register($this->users, 'bob', 'bob@example.com', 'pass');

        $this->assertSame('user', $result['user']['role']);
    }

    public function test_register_duplicate_username_returns_username_error(): void
    {
        Auth::register($this->users, 'charlie', 'charlie@example.com', 'pass');

        $result = Auth::register($this->users, 'charlie', 'other@example.com', 'pass');

        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('username', $result['errors']);
        $this->assertArrayNotHasKey('email', $result['errors']);
    }

    public function test_register_duplicate_email_returns_email_error(): void
    {
        Auth::register($this->users, 'dan', 'shared@example.com', 'pass');

        $result = Auth::register($this->users, 'different', 'shared@example.com', 'pass');

        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('email', $result['errors']);
        $this->assertArrayNotHasKey('username', $result['errors']);
    }

    public function test_register_both_conflicts_returns_both_errors(): void
    {
        Auth::register($this->users, 'eve', 'eve@example.com', 'pass');

        $result = Auth::register($this->users, 'eve', 'eve@example.com', 'pass');

        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('username', $result['errors']);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    // -----------------------------------------------------------------------
    // Auth::verifyCredentials()
    // -----------------------------------------------------------------------

    public function test_verify_credentials_returns_user_for_correct_password(): void
    {
        Auth::register($this->users, 'frank', 'frank@example.com', 'correct_pass');

        $user = Auth::verifyCredentials($this->users, 'frank@example.com', 'correct_pass');

        $this->assertNotNull($user);
        $this->assertSame('frank', $user['username']);
    }

    public function test_verify_credentials_returns_null_for_wrong_password(): void
    {
        Auth::register($this->users, 'grace', 'grace@example.com', 'rightpass');

        $result = Auth::verifyCredentials($this->users, 'grace@example.com', 'wrongpass');

        $this->assertNull($result);
    }

    public function test_verify_credentials_returns_null_for_nonexistent_email(): void
    {
        $result = Auth::verifyCredentials($this->users, 'nobody@example.com', 'anypass');

        $this->assertNull($result);
    }
}
