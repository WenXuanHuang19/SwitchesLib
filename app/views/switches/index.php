<h1>Switches</h1>

<?php if (empty($switches)): ?>
    <p class="empty-state">No switches yet. Check back soon.</p>
<?php else: ?>
    <ul class="switch-grid">
        <?php foreach ($switches as $switch): ?>
            <li class="switch-card">
                <a class="switch-card__link" href="<?= url('/switches/' . $switch['slug']) ?>">
                    <?php if (!empty($switch['image_url'])): ?>
                        <img class="switch-card__image" src="<?= url($switch['image_url']) ?>" alt="<?= e($switch['name']) ?>">
                    <?php else: ?>
                        <span class="switch-card__placeholder">No image</span>
                    <?php endif; ?>

                    <span class="switch-card__name"><?= e($switch['name']) ?></span>

                    <span class="switch-card__tags">
                        <?php foreach (switch_card_tags($switch) as $tag): ?>
                            <span class="tag"><?= e($tag) ?></span>
                        <?php endforeach; ?>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
