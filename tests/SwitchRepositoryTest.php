<?php

test('allApproved returns only approved switches, not drafts', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_switch($pdo, ['name' => 'Published Switch', 'status' => 'approved']);
    insert_switch($pdo, ['name' => 'Draft Switch',     'status' => 'draft']);

    $repo = new SwitchRepository($pdo);
    $names = array_column($repo->allApproved(), 'name');

    assertSame(['Published Switch'], $names);
});

test('allApproved returns newest switches first', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_switch($pdo, ['name' => 'Older', 'created_at' => '2024-01-01 00:00:00']);
    insert_switch($pdo, ['name' => 'Newer', 'created_at' => '2024-06-01 00:00:00']);

    $repo = new SwitchRepository($pdo);
    $names = array_column($repo->allApproved(), 'name');

    assertSame(['Newer', 'Older'], $names);
});
