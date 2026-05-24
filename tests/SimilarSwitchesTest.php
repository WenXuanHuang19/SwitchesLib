<?php

/** Helper: insert a switch and return the full row (as similarTo expects). */
function make_switch(PDO $pdo, array $overrides): array
{
    $repo = new SwitchRepository($pdo);
    $slug = $overrides['slug'] ?? ('s-' . bin2hex(random_bytes(4)));
    insert_switch($pdo, array_merge(['slug' => $slug], $overrides));
    return $repo->findBySlug($slug);
}

test('similarTo matches same switch type and sound profile, excluding self and mismatches', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $current = make_switch($pdo, ['slug' => 'current', 'name' => 'Current',
        'switch_type' => 'Linear', 'sound_profile' => 'Creamy', 'bottom_out_force' => 60]);
    make_switch($pdo, ['slug' => 'match', 'name' => 'Match',
        'switch_type' => 'Linear', 'sound_profile' => 'Creamy', 'bottom_out_force' => 62]);
    make_switch($pdo, ['slug' => 'wrong-type', 'name' => 'Wrong Type',
        'switch_type' => 'Tactile', 'sound_profile' => 'Creamy', 'bottom_out_force' => 61]);
    make_switch($pdo, ['slug' => 'wrong-sound', 'name' => 'Wrong Sound',
        'switch_type' => 'Linear', 'sound_profile' => 'Thocky', 'bottom_out_force' => 61]);

    $repo = new SwitchRepository($pdo);
    $names = array_column($repo->similarTo($current), 'name');

    assertSame(['Match'], $names);
});

test('similarTo orders by closest bottom-out force difference', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $current = make_switch($pdo, ['slug' => 'current', 'name' => 'Current',
        'switch_type' => 'Linear', 'sound_profile' => 'Creamy', 'bottom_out_force' => 60]);
    make_switch($pdo, ['slug' => 'far',   'name' => 'Far',   // diff 15
        'switch_type' => 'Linear', 'sound_profile' => 'Creamy', 'bottom_out_force' => 75]);
    make_switch($pdo, ['slug' => 'close', 'name' => 'Close', // diff 2
        'switch_type' => 'Linear', 'sound_profile' => 'Creamy', 'bottom_out_force' => 62]);
    make_switch($pdo, ['slug' => 'mid',   'name' => 'Mid',   // diff 8
        'switch_type' => 'Linear', 'sound_profile' => 'Creamy', 'bottom_out_force' => 52]);

    $repo = new SwitchRepository($pdo);
    $names = array_column($repo->similarTo($current), 'name');

    assertSame(['Close', 'Mid', 'Far'], $names);
});

test('similarTo returns at most three switches', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $current = make_switch($pdo, ['slug' => 'current', 'name' => 'Current',
        'switch_type' => 'Linear', 'sound_profile' => 'Creamy', 'bottom_out_force' => 60]);
    foreach (range(1, 5) as $i) {
        make_switch($pdo, ['slug' => "m$i", 'name' => "Match $i",
            'switch_type' => 'Linear', 'sound_profile' => 'Creamy', 'bottom_out_force' => 60 + $i]);
    }

    $repo = new SwitchRepository($pdo);

    assertSame(3, count($repo->similarTo($current)));
});
