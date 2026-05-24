<h1>My Submissions</h1>

<?php if (empty($submissions)): ?>
    <p class="empty-state">
        You haven't submitted any switches yet.
        <a href="<?= url('/submit') ?>">Submit one →</a>
    </p>
<?php else: ?>
    <table class="submission-table">
        <thead>
            <tr>
                <th>Switch</th>
                <th>Designer or Studio</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($submissions as $s): ?>
                <tr>
                    <td><?= e($s['name']) ?></td>
                    <td><?= e(or_unknown($s['designer_name'])) ?></td>
                    <td>
                        <span class="status status--<?= e(strtolower($s['status'])) ?>">
                            <?= e($s['status']) ?>
                        </span>
                    </td>
                    <td><?= e(date('M j, Y', strtotime($s['created_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
