<?php
/** @var array  $submissions   rows with designer_name + submitter_username */
/** @var string $currentStatus active filter value */

$statusFilter = fn(string $label, string $val) =>
    '<a href="' . url('/admin/submissions?status=' . $val) . '"'
    . ' class="' . ($currentStatus === $val ? 'active' : '') . '">'
    . e($label) . '</a>';
?>
<h1>Submissions</h1>

<p class="status-filter">
    <a href="<?= url('/admin/submissions') ?>" class="<?= $currentStatus === '' ? 'active' : '' ?>">All</a>
    <?= $statusFilter('Pending', 'Pending') ?>
    <?= $statusFilter('Approved', 'Approved') ?>
    <?= $statusFilter('Rejected', 'Rejected') ?>
</p>

<?php if (empty($submissions)): ?>
    <p class="empty-state">No submissions<?= $currentStatus !== '' ? ' with that status' : '' ?>.</p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Switch</th>
                <th>Designer</th>
                <th>Submitted by</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($submissions as $s): ?>
                <tr>
                    <td><?= e($s['name']) ?></td>
                    <td><?= e(or_unknown($s['designer_name'])) ?></td>
                    <td><?= e($s['submitter_username']) ?></td>
                    <td>
                        <span class="status status--<?= e(strtolower($s['status'])) ?>">
                            <?= e($s['status']) ?>
                        </span>
                    </td>
                    <td><?= e(date('M j, Y', strtotime($s['created_at']))) ?></td>
                    <td>
                        <a href="<?= url('/admin/submissions/' . $s['id']) ?>">Review</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
