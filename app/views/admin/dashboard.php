<h1>Dashboard</h1>

<section class="stat-cards">
    <div class="stat-card">
        <span class="stat-card__value"><?= e((string) $switchCount) ?></span>
        <span class="stat-card__label">Switches</span>
    </div>
    <div class="stat-card">
        <span class="stat-card__value"><?= e((string) $pendingCount) ?></span>
        <span class="stat-card__label">Pending Submissions</span>
    </div>
    <div class="stat-card">
        <span class="stat-card__value"><?= e((string) $blogCount) ?></span>
        <span class="stat-card__label">Blog Posts</span>
    </div>
    <div class="stat-card">
        <span class="stat-card__value"><?= e((string) $userCount) ?></span>
        <span class="stat-card__label">Users</span>
    </div>
</section>

<section class="admin-section">
    <h2>Recently added switches</h2>
    <?php if (empty($recentSwitches)): ?>
        <p class="empty-state">No switches yet.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr><th>Switch</th><th>Status</th><th>Added</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recentSwitches as $s): ?>
                    <tr>
                        <td><a href="<?= url('/switches/' . $s['slug']) ?>"><?= e($s['name']) ?></a></td>
                        <td><?= e($s['status']) ?></td>
                        <td><?= e(date('M j, Y', strtotime($s['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
