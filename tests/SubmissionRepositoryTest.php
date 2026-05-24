<?php

test('create stores a Pending submission owned by the user', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $userId     = insert_user($pdo);
    $designerId = insert_designer($pdo, ['name' => 'Gateron']);

    $repo = new SubmissionRepository($pdo);
    $id = $repo->create($userId, [
        'name'        => 'Oil King',
        'designer_id' => $designerId,
        'switch_type' => 'Linear',
    ]);

    $rows = $repo->forUser($userId);

    assertSame(1, count($rows));
    assertSame('Oil King', $rows[0]['name']);
    assertSame('Pending', $rows[0]['status']);
    assertSame($userId, (int) $rows[0]['user_id']);
    assertTrue($id > 0);
});

test('forUser returns only that user submissions, newest first, with designer name', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $alice      = insert_user($pdo, ['username' => 'alice', 'email' => 'alice@example.com']);
    $bob        = insert_user($pdo, ['username' => 'bob',   'email' => 'bob@example.com']);
    $designerId = insert_designer($pdo, ['name' => 'Gateron']);

    $repo = new SubmissionRepository($pdo);
    $repo->create($alice, ['name' => 'Older', 'designer_id' => $designerId, 'switch_type' => 'Linear', 'created_at' => '2024-01-01 00:00:00']);
    $repo->create($alice, ['name' => 'Newer', 'designer_id' => $designerId, 'switch_type' => 'Linear', 'created_at' => '2024-06-01 00:00:00']);
    $repo->create($bob,   ['name' => 'Bobs',  'designer_id' => $designerId, 'switch_type' => 'Linear']);

    $rows  = $repo->forUser($alice);
    $names = array_column($rows, 'name');

    assertSame(['Newer', 'Older'], $names);
    assertSame('Gateron', $rows[0]['designer_name']);
});
