<?php

test('latest returns at most the given number of approved switches', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    foreach (range(1, 5) as $i) {
        insert_switch($pdo, ['name' => "Switch $i"]);
    }

    $repo = new SwitchRepository($pdo);

    assertSame(3, count($repo->latest(3)));
});

test('latest returns only approved switches', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_switch($pdo, ['name' => 'Approved', 'status' => 'approved']);
    insert_switch($pdo, ['name' => 'Draft',    'status' => 'draft']);

    $repo  = new SwitchRepository($pdo);
    $names = array_column($repo->latest(9), 'name');

    assertSame(['Approved'], $names);
});
