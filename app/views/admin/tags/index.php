<?php
/**
 * Read-only tag reference page.
 *
 * @var array $grouped    tags grouped by type key
 * @var array $typeLabels human-readable labels per type key
 */
/** @var array $active (injected by view() / admin layout) */
$order = ['switch_type', 'sound_profile', 'feel_profile', 'recommended_use'];
?>
<h1>Tag Reference</h1>
<p class="admin-hint">These are the fixed tag values available in the system. Tags are not editable in the MVP.</p>

<?php foreach ($order as $type): ?>
    <?php if (empty($grouped[$type])) continue; ?>
    <section class="admin-section">
        <h2><?= e($typeLabels[$type]) ?></h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Value</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grouped[$type] as $tag): ?>
                    <tr>
                        <td><code><?= e($tag['name']) ?></code></td>
                        <td><?= e($tag['description'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endforeach; ?>
