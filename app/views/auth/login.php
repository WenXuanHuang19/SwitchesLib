<h1>Log in</h1>

<?php if (!empty($error)): ?>
    <p class="form-error"><?= e($error) ?></p>
<?php endif; ?>

<form class="auth-form" method="post" action="<?= url('/login') ?>">
    <label>
        Email
        <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>
    </label>

    <label>
        Password
        <input type="password" name="password" required>
    </label>

    <button type="submit">Log in</button>
</form>

<p>No account yet? <a href="<?= url('/register') ?>">Register</a>.</p>
