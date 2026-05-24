<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Switches Lib</title>
</head>
<body>
<header>
    <nav>
        <a href="<?= url('/') ?>">Switches Lib</a>
        <a href="<?= url('/switches') ?>">Switches</a>
        <a href="<?= url('/blog') ?>">Blog</a>
        <?php if (Auth::check()): ?>
            <a href="<?= url('/submit') ?>">Submit a Switch</a>
            <a href="<?= url('/my-submissions') ?>">My Submissions</a>
            <span class="nav-user">Hi, <?= e(Auth::username()) ?></span>
            <form class="nav-logout" method="post" action="<?= url('/logout') ?>">
                <button type="submit">Logout</button>
            </form>
        <?php else: ?>
            <a href="<?= url('/login') ?>">Login</a>
            <a href="<?= url('/register') ?>">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main>
