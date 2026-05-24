<?php

/** Minimal valid switch payload for admin create/update. */
function switch_payload(PDO $pdo, array $overrides = []): array
{
    return array_merge([
        'name'        => 'Oil King',
        'designer_id' => insert_designer($pdo),
        'switch_type' => 'Linear',
    ], $overrides);
}

test('create inserts a switch with an auto-generated slug and returns its id', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new SwitchRepository($pdo);
    $id   = $repo->create(switch_payload($pdo, ['name' => 'Oil King']));

    $switch = $repo->findById($id);

    assertSame('Oil King', $switch['name']);
    assertSame('oil-king', $switch['slug']);
});

test('create appends a numeric suffix when the slug already exists', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new SwitchRepository($pdo);
    $repo->create(switch_payload($pdo, ['name' => 'Oil King']));
    $repo->create(switch_payload($pdo, ['name' => 'Oil King']));
    $id3 = $repo->create(switch_payload($pdo, ['name' => 'Oil King']));

    assertSame('oil-king-3', $repo->findById($id3)['slug']);
});

test('findById returns null when no switch matches', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new SwitchRepository($pdo);

    assertSame(null, $repo->findById(999));
});

test('update changes the given fields', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new SwitchRepository($pdo);
    $id   = $repo->create(switch_payload($pdo, ['name' => 'Oil King', 'switch_type' => 'Linear']));

    $repo->update($id, ['name' => 'Oil King V2', 'switch_type' => 'Tactile']);
    $switch = $repo->findById($id);

    assertSame('Oil King V2', $switch['name']);
    assertSame('Tactile', $switch['switch_type']);
});

test('delete removes the switch permanently', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new SwitchRepository($pdo);
    $id   = $repo->create(switch_payload($pdo));

    $repo->delete($id);

    assertSame(null, $repo->findById($id));
});

test('all returns every switch regardless of status, with designer name, most-recently-updated first', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $designerId = insert_designer($pdo, ['name' => 'Gateron']);
    insert_switch($pdo, ['name' => 'Older', 'status' => 'approved', 'designer_id' => $designerId, 'updated_at' => '2024-01-01 00:00:00']);
    insert_switch($pdo, ['name' => 'Newer', 'status' => 'draft',    'designer_id' => $designerId, 'updated_at' => '2024-06-01 00:00:00']);

    $repo = new SwitchRepository($pdo);
    $rows = $repo->all();

    assertSame(['Newer', 'Older'], array_column($rows, 'name'));
    assertSame('Gateron', $rows[0]['designer_name']);
});
