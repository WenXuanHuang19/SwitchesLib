<?php
/** @var array $submissions Pending audio submissions with switch + submitter info. */
?>
<h1>Audio Review</h1>
<p class="admin-subtitle">Pending typing recordings awaiting review.</p>

<?php if (empty($submissions)): ?>
    <p class="empty-state">No pending recordings.</p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Switch</th>
                <th>Submitted by</th>
                <th>Recording</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($submissions as $s): ?>
                <tr>
                    <td><?= e(or_unknown($s['switch_name'])) ?></td>
                    <td><?= e(or_unknown($s['submitter_username'])) ?></td>
                    <td><audio controls src="<?= url($s['audio_url']) ?>"></audio></td>
                    <td><?= e(date('M j, Y', strtotime($s['created_at']))) ?></td>
                    <td><a href="<?= url('/admin/audio-submissions/' . $s['id']) ?>">Review</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
