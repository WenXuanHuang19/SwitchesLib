<?php
/** @var string $active — key of the current admin page, for nav highlighting. */
$active = $active ?? '';
$navItems = [
    'dashboard'   => ['Dashboard',   '/admin'],
    'switches'    => ['Switches',    '/admin/switches'],
    'add-switch'  => ['Add Switch',  '/admin/switches/create'],
    'submissions' => ['Submissions', '/admin/submissions'],
    'designers'   => ['Designers',   '/admin/designers'],
    'tags'        => ['Tags',        '/admin/tags'],
    'blog'        => ['Blog Posts',  '/admin/blog'],
    'users'       => ['Users',       '/admin/users'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin &mdash; Switches Lib</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=IBM+Plex+Mono:wght@400;500&family=Instrument+Sans:wght@400;500;600&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('/css/app.css') ?>">
    <link rel="stylesheet" href="<?= url('/css/admin.css') ?>">
</head>
<body class="admin">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="admin-sidebar__brand" href="<?= url('/admin') ?>">Switches Lib Admin</a>
        <nav class="admin-nav">
            <?php foreach ($navItems as $key => [$label, $path]): ?>
                <a href="<?= url($path) ?>"
                   class="admin-nav__link<?= $active === $key ? ' admin-nav__link--active' : '' ?>">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
            <form class="admin-nav__logout" method="post" action="<?= url('/logout') ?>">
                <button type="submit">Logout</button>
            </form>
        </nav>
    </aside>
    <main class="admin-main">
        <?php if ($flash = flash_pull()): ?>
            <p class="flash"><?= e($flash) ?></p>
        <?php endif; ?>
