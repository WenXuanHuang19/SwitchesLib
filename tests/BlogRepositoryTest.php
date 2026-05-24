<?php

test('allPublished returns only published posts, not drafts', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_blog_post($pdo, ['title' => 'Live Post',  'status' => 'published']);
    insert_blog_post($pdo, ['title' => 'Draft Post', 'status' => 'draft']);

    $repo = new BlogRepository($pdo);
    $titles = array_column($repo->allPublished(), 'title');

    assertSame(['Live Post'], $titles);
});

test('allPublished returns newest posts first', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_blog_post($pdo, ['title' => 'Older', 'published_at' => '2024-01-01 00:00:00']);
    insert_blog_post($pdo, ['title' => 'Newer', 'published_at' => '2024-06-01 00:00:00']);

    $repo = new BlogRepository($pdo);
    $titles = array_column($repo->allPublished(), 'title');

    assertSame(['Newer', 'Older'], $titles);
});

test('findBySlug returns the matching post', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_blog_post($pdo, ['slug' => 'what-affects-sound', 'title' => 'What Affects Sound']);

    $repo = new BlogRepository($pdo);
    $post = $repo->findBySlug('what-affects-sound');

    assertSame('What Affects Sound', $post['title']);
});

test('findBySlug returns null when no post matches', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $repo = new BlogRepository($pdo);

    assertSame(null, $repo->findBySlug('does-not-exist'));
});
