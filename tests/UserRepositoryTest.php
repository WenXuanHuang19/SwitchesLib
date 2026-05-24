<?php

test('create stores a hashed password that findByEmail can retrieve', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new UserRepository($pdo);
    $repo->create('alice', 'alice@example.com', password_hash('secret123', PASSWORD_DEFAULT));

    $user = $repo->findByEmail('alice@example.com');

    assertSame('alice', $user['username']);
    assertTrue($user['password_hash'] !== 'secret123', 'password must not be stored in plaintext');
    assertTrue(password_verify('secret123', $user['password_hash']), 'stored hash must verify against the password');
});
