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
