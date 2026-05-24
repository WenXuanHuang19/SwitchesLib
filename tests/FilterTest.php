<?php

test('filtered with no filters returns all approved switches', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_switch($pdo, ['name' => 'Alpha', 'status' => 'approved']);
    insert_switch($pdo, ['name' => 'Beta',  'status' => 'approved']);
    insert_switch($pdo, ['name' => 'Draft', 'status' => 'draft']);

    $repo = new SwitchRepository($pdo);
    $names = array_column($repo->filtered(), 'name');

    assertSame(['Alpha', 'Beta'], $names);
});

test('filtered by switch_type returns only matching switches', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_switch($pdo, ['name' => 'Linear A',  'switch_type' => 'Linear']);
    insert_switch($pdo, ['name' => 'Tactile B', 'switch_type' => 'Tactile']);
    insert_switch($pdo, ['name' => 'Linear C',  'switch_type' => 'Linear']);

    $repo  = new SwitchRepository($pdo);
    $names = array_column($repo->filtered(['switch_type' => 'Linear']), 'name');
    sort($names);

    assertSame(['Linear A', 'Linear C'], $names);
});

test('filtered with multiple filters applies AND logic', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_switch($pdo, ['name' => 'Match',       'switch_type' => 'Linear', 'sound_profile' => 'Creamy']);
    insert_switch($pdo, ['name' => 'Wrong Type',  'switch_type' => 'Tactile','sound_profile' => 'Creamy']);
    insert_switch($pdo, ['name' => 'Wrong Sound', 'switch_type' => 'Linear', 'sound_profile' => 'Thocky']);

    $repo  = new SwitchRepository($pdo);
    $names = array_column(
        $repo->filtered(['switch_type' => 'Linear', 'sound_profile' => 'Creamy']),
        'name'
    );

    assertSame(['Match'], $names);
});

test('filtered sort=most_viewed orders by views_count descending', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_switch($pdo, ['name' => 'Low',  'views_count' => 5]);
    insert_switch($pdo, ['name' => 'High', 'views_count' => 100]);
    insert_switch($pdo, ['name' => 'Mid',  'views_count' => 42]);

    $repo  = new SwitchRepository($pdo);
    $names = array_column($repo->filtered([], 'most_viewed'), 'name');

    assertSame(['High', 'Mid', 'Low'], $names);
});

test('filtered sort=lightest orders by bottom_out_force ASC with NULL last', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_switch($pdo, ['name' => 'Heavy',   'bottom_out_force' => 80]);
    insert_switch($pdo, ['name' => 'Unknown', 'bottom_out_force' => null]);
    insert_switch($pdo, ['name' => 'Light',   'bottom_out_force' => 40]);

    $repo  = new SwitchRepository($pdo);
    $names = array_column($repo->filtered([], 'lightest'), 'name');

    assertSame(['Light', 'Heavy', 'Unknown'], $names);
});

test('filtered sort=heaviest orders by bottom_out_force DESC with NULL last', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_switch($pdo, ['name' => 'Light',   'bottom_out_force' => 40]);
    insert_switch($pdo, ['name' => 'Unknown', 'bottom_out_force' => null]);
    insert_switch($pdo, ['name' => 'Heavy',   'bottom_out_force' => 80]);

    $repo  = new SwitchRepository($pdo);
    $names = array_column($repo->filtered([], 'heaviest'), 'name');

    assertSame(['Heavy', 'Light', 'Unknown'], $names);
});
