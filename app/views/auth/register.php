<h1>Register</h1>

<form class="auth-form" method="post" action="<?= url('/register') ?>">
    <label>
        Username
        <input type="text" name="username" value="<?= e($old['username'] ?? '') ?>" required>
        <?php if (!empty($errors['username'])): ?>
            <span class="field-error"><?= e($errors['username']) ?></span>
        <?php endif; ?>
    </label>

    <label>
        Email
        <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>
        <?php if (!empty($errors['email'])): ?>
            <span class="field-error"><?= e($errors['email']) ?></span>
        <?php endif; ?>
    </label>

    <label>
        Password
        <input type="password" name="password" required>
        <?php if (!empty($errors['password'])): ?>
            <span class="field-error"><?= e($errors['password']) ?></span>
        <?php endif; ?>
    </label>

    <button type="submit">Create account</button>
</form>

<p>Already have an account? <a href="<?= url('/login') ?>">Log in</a>.</p>
