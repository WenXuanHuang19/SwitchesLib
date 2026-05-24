<?php

test('SwitchRepository::count counts all switches including drafts', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_switch($pdo, ['status' => 'approved']);
    insert_switch($pdo, ['status' => 'approved']);
    insert_switch($pdo, ['status' => 'draft']);

    $repo = new SwitchRepository($pdo);

    assertSame(3, $repo->count());
});

test('SwitchRepository::recent returns the newest switches regardless of status, newest first', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_switch($pdo, ['name' => 'Old',   'status' => 'approved', 'created_at' => '2024-01-01 00:00:00']);
    insert_switch($pdo, ['name' => 'Draft', 'status' => 'draft',    'created_at' => '2024-03-01 00:00:00']);
    insert_switch($pdo, ['name' => 'New',   'status' => 'approved', 'created_at' => '2024-06-01 00:00:00']);

    $repo  = new SwitchRepository($pdo);
    $names = array_column($repo->recent(2), 'name');

    assertSame(['New', 'Draft'], $names);
});

test('SubmissionRepository::countPending counts only Pending submissions', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_submission($pdo, ['status' => 'Pending']);
    insert_submission($pdo, ['status' => 'Approved']);
    insert_submission($pdo, ['status' => 'Rejected']);

    $repo = new SubmissionRepository($pdo);

    assertSame(1, $repo->countPending());
});

test('BlogRepository::count counts all posts including drafts', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_blog_post($pdo, ['status' => 'published']);
    insert_blog_post($pdo, ['status' => 'draft']);

    $repo = new BlogRepository($pdo);

    assertSame(2, $repo->count());
});

test('UserRepository::count counts all users', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_user($pdo);
    insert_user($pdo);

    $repo = new UserRepository($pdo);

    assertSame(2, $repo->count());
});
