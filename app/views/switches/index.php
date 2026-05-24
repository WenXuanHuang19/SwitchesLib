<h1>Switches</h1>

<?php if (empty($switches)): ?>
    <p class="empty-state">No switches yet. Check back soon.</p>
<?php else: ?>
    <ul class="switch-grid">
        <?php foreach ($switches as $card): ?>
            <?php require VIEWS_PATH . '/switches/_card.php'; ?>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
