<?php

test('all returns all posts regardless of status, newest first', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_blog_post($pdo, ['title' => 'Draft',    'status' => 'draft',    'created_at' => '2024-01-01 00:00:00']);
    insert_blog_post($pdo, ['title' => 'Published','status' => 'published','created_at' => '2024-06-01 00:00:00']);

    $repo   = new BlogRepository($pdo);
    $titles = array_column($repo->all(), 'title');

    assertSame(['Published', 'Draft'], $titles);
});

test('findById returns a post or null', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $id   = insert_blog_post($pdo, ['title' => 'Test Post']);
    $repo = new BlogRepository($pdo);

    assertSame('Test Post', $repo->findById($id)['title']);
    assertSame(null, $repo->findById(999));
});

test('create inserts a post with an auto-generated slug and returns its id', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new BlogRepository($pdo);
    $id   = $repo->create(['title' => 'Hello World', 'status' => 'published']);

    $post = $repo->findById($id);

    assertSame('hello-world', $post['slug']);
    assertSame('published', $post['status']);
    assertTrue($id > 0);
});

test('create appends a numeric suffix when the slug is taken', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new BlogRepository($pdo);
    $repo->create(['title' => 'Same Title']);
    $repo->create(['title' => 'Same Title']);
    $id3  = $repo->create(['title' => 'Same Title']);

    assertSame('same-title-3', $repo->findById($id3)['slug']);
});

test('update changes the given fields', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new BlogRepository($pdo);
    $id   = $repo->create(['title' => 'Old']);

    $repo->update($id, ['title' => 'New', 'status' => 'published']);
    $post = $repo->findById($id);

    assertSame('New', $post['title']);
    assertSame('published', $post['status']);
});

test('delete removes the post permanently', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new BlogRepository($pdo);
    $id   = $repo->create(['title' => 'Delete Me']);

    $repo->delete($id);

    assertSame(null, $repo->findById($id));
});
