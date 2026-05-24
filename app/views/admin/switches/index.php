<?php /** @var array $switches switch rows with designer_name, for the admin list */ ?>
<div class="admin-header">
    <h1>Switches</h1>
    <a class="btn" href="<?= url('/admin/switches/add') ?>">Add Switch</a>
</div>

<?php if (empty($switches)): ?>
    <p class="empty-state">No switches yet. <a href="<?= url('/admin/switches/add') ?>">Add the first one.</a></p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Designer or Studio</th>
                <th>Type</th>
                <th>Sound</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($switches as $s): ?>
                <tr>
                    <td><?= e($s['name']) ?></td>
                    <td><?= e(or_unknown($s['designer_name'])) ?></td>
                    <td><?= e(or_unknown($s['switch_type'])) ?></td>
                    <td><?= e(or_unknown($s['sound_profile'])) ?></td>
                    <td><?= e($s['status']) ?></td>
                    <td><?= e(date('M j, Y', strtotime($s['updated_at']))) ?></td>
                    <td class="admin-table__actions">
                        <a href="<?= url('/admin/switches/' . $s['id'] . '/edit') ?>">Edit</a>
                        <form method="post" action="<?= url('/admin/switches/' . $s['id'] . '/delete') ?>"
                              onsubmit="return confirm('Delete &quot;<?= e($s['name']) ?>&quot; permanently?');">
                            <button type="submit" class="link-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
