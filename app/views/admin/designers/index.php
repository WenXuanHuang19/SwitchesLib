<?php /** @var array $designers rows for the admin list */ ?>
<div class="admin-header">
    <h1>Designers &amp; Studios</h1>
    <a class="btn" href="<?= url('/admin/designers/add') ?>">Add Designer</a>
</div>

<?php if (empty($designers)): ?>
    <p class="empty-state">No designers yet. <a href="<?= url('/admin/designers/add') ?>">Add the first one.</a></p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Website</th>
                <th>Country</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($designers as $d): ?>
                <tr>
                    <td><?= e($d['name']) ?></td>
                    <td><?= e(or_unknown($d['website'])) ?></td>
                    <td><?= e(or_unknown($d['country'])) ?></td>
                    <td class="admin-table__actions">
                        <a href="<?= url('/admin/designers/' . $d['id'] . '/edit') ?>">Edit</a>
                        <form method="post" action="<?= url('/admin/designers/' . $d['id'] . '/delete') ?>"
                              onsubmit="return confirm('Delete &quot;<?= e($d['name']) ?>&quot; permanently?');">
                            <button type="submit" class="link-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
