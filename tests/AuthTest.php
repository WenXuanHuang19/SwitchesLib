<?php

test('register rejects a duplicate email', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $users = new UserRepository($pdo);
    $users->create('alice', 'alice@example.com', password_hash('secret123', PASSWORD_DEFAULT));

    $result = Auth::register($users, 'bob', 'alice@example.com', 'another123');

    assertTrue(isset($result['errors']['email']), 'expected an email error for duplicate email');
});

test('register rejects a duplicate username', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $users = new UserRepository($pdo);
    $users->create('alice', 'alice@example.com', password_hash('secret123', PASSWORD_DEFAULT));

    $result = Auth::register($users, 'alice', 'different@example.com', 'another123');

    assertTrue(isset($result['errors']['username']), 'expected a username error for duplicate username');
});

test('register creates a user with the default role on success', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $users = new UserRepository($pdo);
    $result = Auth::register($users, 'carol', 'carol@example.com', 'secret123');

    assertTrue(!isset($result['errors']), 'expected no errors on a clean registration');
    assertSame('carol', $result['user']['username']);
    assertSame('user', $result['user']['role']);
});

test('verifyCredentials returns the user when the password is correct', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $users = new UserRepository($pdo);
    Auth::register($users, 'dave', 'dave@example.com', 'secret123');

    $user = Auth::verifyCredentials($users, 'dave@example.com', 'secret123');

    assertSame('dave', $user['username']);
});

test('verifyCredentials returns null when the password is wrong', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $users = new UserRepository($pdo);
    Auth::register($users, 'erin', 'erin@example.com', 'secret123');

    assertSame(null, Auth::verifyCredentials($users, 'erin@example.com', 'wrongpass'));
});
