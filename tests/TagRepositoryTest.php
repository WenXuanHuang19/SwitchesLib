<?php

function insert_tag(PDO $pdo, string $type, string $name, ?string $description = null): void
{
    $pdo->prepare("INSERT INTO tags (type, name, description) VALUES (:t, :n, :d)")
        ->execute(['t' => $type, 'n' => $name, 'd' => $description]);
}

test('all returns all tags grouped and ordered within each type', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_tag($pdo, 'switch_type', 'Clicky', 'Click');
    insert_tag($pdo, 'switch_type', 'Tactile', 'Bump');

    $repo = new TagRepository($pdo);
    $rows = $repo->all();

    // All inserted tags are returned.
    assertSame(2, count($rows));
    // Within switch_type, alphabetical order.
    assertSame('Clicky', $rows[0]['name']);
    assertSame('Tactile', $rows[1]['name']);
});
