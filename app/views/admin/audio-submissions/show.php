<?php
/** @var array $submission Audio submission with switch + submitter details. */
$slug = $submission['switch_slug'] ?? null;
?>
<h1>Review Recording</h1>

<dl class="admin-review">
    <dt>Target switch</dt>
    <dd>
        <?php if ($slug): ?>
            <a href="<?= url('/switches/' . $slug) ?>" target="_blank">
                <?= e($submission['switch_name']) ?>
            </a>
        <?php else: ?>
            <?= e(or_unknown($submission['switch_name'])) ?>
        <?php endif; ?>
    </dd>

    <dt>Submitted by</dt>
    <dd><?= e(or_unknown($submission['submitter_username'])) ?></dd>

    <dt>Status</dt>
    <dd>
        <span class="status status--<?= e(strtolower($submission['status'])) ?>">
            <?= e($submission['status']) ?>
        </span>
    </dd>

    <dt>Recording</dt>
    <dd><audio controls src="<?= url($submission['audio_url']) ?>"></audio></dd>
</dl>

<?php if ($submission['status'] === 'Pending'): ?>
    <div class="admin-review__actions">
        <form method="post" action="<?= url('/admin/audio-submissions/' . $submission['id'] . '/approve') ?>">
            <button type="submit" class="btn">Approve &amp; publish</button>
        </form>
        <form method="post" action="<?= url('/admin/audio-submissions/' . $submission['id'] . '/reject') ?>">
            <button type="submit" class="btn btn--ghost">Reject</button>
        </form>
    </div>
<?php else: ?>
    <p class="admin-note">This recording has already been reviewed.</p>
<?php endif; ?>

<p><a href="<?= url('/admin/audio-submissions') ?>">← Back to queue</a></p>
