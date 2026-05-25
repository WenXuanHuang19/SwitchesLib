<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Switches Lib</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=IBM+Plex+Mono:wght@400;500&family=Instrument+Sans:wght@400;500;600&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('/css/app.css') ?>">
</head>
<body>

<header class="site-header">
    <nav class="site-nav container">
        <a class="site-nav__brand" href="<?= url('/') ?>">Switches Lib</a>

        <!-- Desktop links -->
        <div class="site-nav__links">
            <a href="<?= url('/switches') ?>">Switches</a>
            <a href="<?= url('/blog') ?>">Blog</a>
        </div>

        <!-- Desktop right side -->
        <div class="site-nav__right">
            <?php if (Auth::check()): ?>
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= url('/admin') ?>" class="btn btn--admin">Admin</a>
                <?php else: ?>
                    <a href="<?= url('/submit') ?>" class="btn">Submit a Switch</a>
                <?php endif; ?>
                <a href="<?= url('/my-submissions') ?>">My Submissions</a>
                <span class="site-nav__user">Hi, <?= e(Auth::username()) ?></span>
                <form class="site-nav__logout" method="post" action="<?= url('/logout') ?>">
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <a href="<?= url('/login') ?>">Login</a>
                <a href="<?= url('/register') ?>" class="btn">Register</a>
            <?php endif; ?>
        </div>

        <!-- Mobile hamburger -->
        <button
            class="site-nav__hamburger"
            id="nav-hamburger"
            aria-label="Open navigation"
            aria-expanded="false"
            aria-controls="nav-mobile-menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </nav>

    <!-- Mobile menu overlay -->
    <div class="site-nav__mobile-menu" id="nav-mobile-menu" role="navigation" aria-label="Mobile navigation">
        <a href="<?= url('/switches') ?>">Switches</a>
        <a href="<?= url('/blog') ?>">Blog</a>
        <?php if (Auth::check()): ?>
            <?php if (Auth::isAdmin()): ?>
                <a href="<?= url('/admin') ?>">Admin Dashboard</a>
            <?php else: ?>
                <a href="<?= url('/submit') ?>">Submit a Switch</a>
            <?php endif; ?>
            <a href="<?= url('/my-submissions') ?>">My Submissions</a>
            <span class="site-nav__user">Hi, <?= e(Auth::username()) ?></span>
            <form class="site-nav__logout" method="post" action="<?= url('/logout') ?>">
                <button type="submit">Logout</button>
            </form>
        <?php else: ?>
            <a href="<?= url('/login') ?>">Login</a>
            <a href="<?= url('/register') ?>">Register</a>
        <?php endif; ?>
    </div>
</header>

<main class="container">
