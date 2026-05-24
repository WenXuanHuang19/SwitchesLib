<?php /** @var array $posts blog post rows for the admin list */ ?>
<div class="admin-header">
    <h1>Blog Posts</h1>
    <a class="btn" href="<?= url('/admin/blog/add') ?>">New Post</a>
</div>

<?php if (empty($posts)): ?>
    <p class="empty-state">No posts yet. <a href="<?= url('/admin/blog/add') ?>">Write the first one.</a></p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Published</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $p): ?>
                <tr>
                    <td><?= e($p['title']) ?></td>
                    <td><?= e(or_unknown($p['category'])) ?></td>
                    <td><?= e($p['status']) ?></td>
                    <td><?= e(!empty($p['published_at']) ? date('M j, Y', strtotime($p['published_at'])) : '—') ?></td>
                    <td class="admin-table__actions">
                        <a href="<?= url('/admin/blog/' . $p['id'] . '/edit') ?>">Edit</a>
                        <form method="post" action="<?= url('/admin/blog/' . $p['id'] . '/delete') ?>"
                              onsubmit="return confirm('Delete &quot;<?= e($p['title']) ?>&quot; permanently?');">
                            <button type="submit" class="link-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
