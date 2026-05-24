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

test('findBySlug returns the matching switch with its designer name', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $designerId = insert_designer($pdo, ['name' => 'Gateron']);
    insert_switch($pdo, ['slug' => 'oil-king', 'name' => 'Oil King', 'designer_id' => $designerId]);

    $repo = new SwitchRepository($pdo);
    $switch = $repo->findBySlug('oil-king');

    assertSame('Oil King', $switch['name']);
    assertSame('Gateron', $switch['designer_name']);
});

test('findBySlug returns null when no switch matches', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new SwitchRepository($pdo);

    assertSame(null, $repo->findBySlug('does-not-exist'));
});

test('incrementViews increases the view count by one', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $id = insert_switch($pdo, ['slug' => 'viewed', 'name' => 'Viewed']);

    $repo = new SwitchRepository($pdo);
    $repo->incrementViews($id);

    assertSame(1, (int) $repo->findBySlug('viewed')['views_count']);
});

test('byDesigner returns other switches from the same designer, excluding the current one', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $a = insert_designer($pdo, ['name' => 'Designer A']);
    $b = insert_designer($pdo, ['name' => 'Designer B']);

    $currentId = insert_switch($pdo, ['slug' => 'current', 'name' => 'Current', 'designer_id' => $a]);
    insert_switch($pdo, ['slug' => 'sibling-1', 'name' => 'Sibling 1', 'designer_id' => $a]);
    insert_switch($pdo, ['slug' => 'sibling-2', 'name' => 'Sibling 2', 'designer_id' => $a]);
    insert_switch($pdo, ['slug' => 'other',     'name' => 'Other',     'designer_id' => $b]);

    $repo = new SwitchRepository($pdo);
    $names = array_column($repo->byDesigner($a, $currentId), 'name');
    sort($names);

    assertSame(['Sibling 1', 'Sibling 2'], $names);
});

test('existsByNameAndDesigner is true when a switch with that name and designer exists', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $designerId = insert_designer($pdo, ['name' => 'Gateron']);
    insert_switch($pdo, ['slug' => 'oil-king', 'name' => 'Oil King', 'designer_id' => $designerId]);

    $repo = new SwitchRepository($pdo);

    assertTrue($repo->existsByNameAndDesigner('Oil King', $designerId));
});

test('existsByNameAndDesigner is false for a different name or different designer', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $a = insert_designer($pdo, ['name' => 'Designer A']);
    $b = insert_designer($pdo, ['name' => 'Designer B']);
    insert_switch($pdo, ['slug' => 'oil-king', 'name' => 'Oil King', 'designer_id' => $a]);

    $repo = new SwitchRepository($pdo);

    assertSame(false, $repo->existsByNameAndDesigner('Oil King', $b));
    assertSame(false, $repo->existsByNameAndDesigner('Other Switch', $a));
});

test('byDesigner returns at most six switches', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $a = insert_designer($pdo, ['name' => 'Prolific']);
    $currentId = insert_switch($pdo, ['slug' => 'current', 'name' => 'Current', 'designer_id' => $a]);
    foreach (range(1, 8) as $i) {
        insert_switch($pdo, ['slug' => "sib-$i", 'name' => "Sibling $i", 'designer_id' => $a]);
    }

    $repo = new SwitchRepository($pdo);

    assertSame(6, count($repo->byDesigner($a, $currentId)));
});
