<?php /** @var array $card — one switch row */ ?>
<li class="switch-card">
    <a class="switch-card__link" href="<?= url('/switches/' . $card['slug']) ?>">
        <?php if (!empty($card['image_url'])): ?>
            <img class="switch-card__image" src="<?= url($card['image_url']) ?>" alt="<?= e($card['name']) ?>">
        <?php else: ?>
            <span class="switch-card__placeholder">No image</span>
        <?php endif; ?>

        <span class="switch-card__name"><?= e($card['name']) ?></span>

        <span class="switch-card__tags">
            <?php foreach (switch_card_tags($card) as $tag): ?>
                <span class="tag"><?= e($tag) ?></span>
            <?php endforeach; ?>
        </span>
    </a>
</li>
