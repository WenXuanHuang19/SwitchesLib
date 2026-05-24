<?php /** @var array $users rows for the admin user list */ ?>
<h1>Users</h1>

<?php if (empty($users)): ?>
    <p class="empty-state">No users yet.</p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td>
                        <span class="role-badge role-badge--<?= e($u['role']) ?>"><?= e($u['role']) ?></span>
                    </td>
                    <td><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
                    <td class="admin-table__actions">
                        <form method="post" action="<?= url('/admin/users/' . $u['id'] . '/role') ?>"
                              onsubmit="return confirm('Change &quot;<?= e($u['username']) ?>&quot; role to <?= $u['role'] === 'admin' ? 'user' : 'admin' ?>?');">
                            <?php if ($u['role'] === 'admin'): ?>
                                <input type="hidden" name="role" value="user">
                                <button type="submit" class="link-danger">Demote to user</button>
                            <?php else: ?>
                                <input type="hidden" name="role" value="admin">
                                <button type="submit" class="btn-sm">Promote to admin</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
