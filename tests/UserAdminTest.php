<?php

test('all returns all users ordered by newest first', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_user($pdo, ['username' => 'old', 'created_at' => '2024-01-01 00:00:00']);
    insert_user($pdo, ['username' => 'new', 'created_at' => '2024-06-01 00:00:00']);

    $repo  = new UserRepository($pdo);
    $names = array_column($repo->all(), 'username');

    assertSame(['new', 'old'], $names);
});

test('updateRole changes the role of a user', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $id   = insert_user($pdo, ['role' => 'user']);
    $repo = new UserRepository($pdo);

    $repo->updateRole($id, 'admin');

    assertSame('admin', $repo->findById($id)['role']);
});
