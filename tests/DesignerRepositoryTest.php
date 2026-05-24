<?php

test('DesignerRepository::all returns all designers ordered by name', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_designer($pdo, ['name' => 'Zeal PC']);
    insert_designer($pdo, ['name' => 'Gateron']);
    insert_designer($pdo, ['name' => 'Boba']);

    $repo  = new DesignerRepository($pdo);
    $names = array_column($repo->all(), 'name');

    assertSame(['Boba', 'Gateron', 'Zeal PC'], $names);
});

test('findById returns the designer or null', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $id  = insert_designer($pdo, ['name' => 'Gateron']);
    $repo = new DesignerRepository($pdo);

    assertSame('Gateron', $repo->findById($id)['name']);
    assertSame(null, $repo->findById(999));
});

test('create stores a designer and returns its id', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new DesignerRepository($pdo);
    $id   = $repo->create(['name' => 'JWK', 'website' => 'https://jwk.top', 'country' => 'CN']);

    $row = $repo->findById($id);

    assertSame('JWK', $row['name']);
    assertSame('https://jwk.top', $row['website']);
    assertSame('CN', $row['country']);
    assertTrue($id > 0);
});

test('update changes the given fields', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new DesignerRepository($pdo);
    $id   = $repo->create(['name' => 'Old Name']);

    $repo->update($id, ['name' => 'New Name', 'country' => 'US']);
    $row = $repo->findById($id);

    assertSame('New Name', $row['name']);
    assertSame('US', $row['country']);
});

test('delete removes the designer permanently', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new DesignerRepository($pdo);
    $id   = $repo->create(['name' => 'Remove Me']);

    $repo->delete($id);

    assertSame(null, $repo->findById($id));
});

test('hasSwitches returns true when linked switches exist, false otherwise', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $did = insert_designer($pdo, ['name' => 'Linked']);
    $emptyDid = insert_designer($pdo, ['name' => 'Empty']);
    insert_switch($pdo, ['designer_id' => $did]);

    $repo = new DesignerRepository($pdo);

    assertTrue($repo->hasSwitches($did));
    assertSame(false, $repo->hasSwitches($emptyDid));
});
