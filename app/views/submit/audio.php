<?php
/**
 * Audio Submission form — submit a typing recording for an existing switch.
 *
 * @var array  $switch The target switch (name + slug).
 * @var ?string $error A validation message to show, or null.
 */
?>
<div class="audio-submit-page">
    <h1>Submit a Recording</h1>
    <p class="audio-submit-page__lead">
        Add a typing-sound recording for <strong><?= e($switch['name']) ?></strong>.
        Recordings are community contributions and are published after review.
    </p>

    <?php if ($error !== null): ?>
        <p class="field-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form class="submit-form" method="post"
          action="<?= url('/switches/' . $switch['slug'] . '/submit-audio') ?>"
          enctype="multipart/form-data">

        <fieldset>
            <legend>Recording</legend>
            <label>MP3 file (max 5 MB) *
                <input type="file" name="audio" accept=".mp3,audio/mpeg" required>
            </label>
        </fieldset>

        <?php require VIEWS_PATH . '/partials/_keyboard_config_fields.php'; ?>

        <button type="submit" class="btn">Submit recording</button>
        <a href="<?= url('/switches/' . $switch['slug']) ?>" class="btn btn--ghost">Cancel</a>
    </form>
</div>
