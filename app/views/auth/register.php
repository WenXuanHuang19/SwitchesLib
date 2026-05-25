<?php
/**
 * @var array $errors  Field-level validation errors.
 * @var array $old     Old input values for repopulation.
 */
?>
<div class="auth-page">
    <h1>Register</h1>

    <form class="auth-form" method="post" action="<?= url('/register') ?>">
        <label>
            Username
            <input type="text" name="username" value="<?= e($old['username'] ?? '') ?>" required autocomplete="username">
            <?php if (!empty($errors['username'])): ?>
                <span class="field-error" role="alert"><?= e($errors['username']) ?></span>
            <?php endif; ?>
        </label>

        <label>
            Email
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required autocomplete="email">
            <?php if (!empty($errors['email'])): ?>
                <span class="field-error" role="alert"><?= e($errors['email']) ?></span>
            <?php endif; ?>
        </label>

        <label>
            Password
            <input type="password" name="password" required autocomplete="new-password">
            <?php if (!empty($errors['password'])): ?>
                <span class="field-error" role="alert"><?= e($errors['password']) ?></span>
            <?php endif; ?>
        </label>

        <button type="submit" class="btn">Create account</button>
    </form>

    <p>Already have an account? <a href="<?= url('/login') ?>">Log in</a>.</p>
</div>
