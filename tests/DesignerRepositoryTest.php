<?php

test('allDesigners returns all designers ordered by name', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_designer($pdo, ['name' => 'Zeal PC']);
    insert_designer($pdo, ['name' => 'Gateron']);
    insert_designer($pdo, ['name' => 'Boba']);

    $repo  = new SwitchRepository($pdo);
    $names = array_column($repo->allDesigners(), 'name');

    assertSame(['Boba', 'Gateron', 'Zeal PC'], $names);
});
