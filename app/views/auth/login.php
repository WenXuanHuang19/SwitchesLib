<?php
/**
 * @var string|null $error   Form-level error message.
 * @var array       $old     Old input values for repopulation.
 */
?>
<div class="auth-page">
    <h1>Log in</h1>

    <?php if (!empty($error)): ?>
        <p class="form-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form class="auth-form" method="post" action="<?= url('/login') ?>">
        <label>
            Email
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required autocomplete="email">
        </label>

        <label>
            Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>

        <button type="submit" class="btn">Log in</button>
    </form>

    <p>No account yet? <a href="<?= url('/register') ?>">Register</a>.</p>
</div>
