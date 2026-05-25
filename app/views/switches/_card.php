<?php
/**
 * @var array $card — one switch row
 * Maps switch_type to a CSS modifier class for coloured pills.
 */
$typeClass = match ($card['switch_type'] ?? '') {
    'Linear'         => 'tag--linear',
    'Tactile'        => 'tag--tactile',
    'Clicky'         => 'tag--clicky',
    'Silent Linear'  => 'tag--silent-linear',
    'Silent Tactile' => 'tag--silent-tactile',
    default          => 'tag--unknown',
};
?>
<li class="switch-card">
    <a class="switch-card__link" href="<?= url('/switches/' . $card['slug']) ?>">

        <div class="switch-card__image-wrap">
            <?php if (!empty($card['image_url'])): ?>
                <img class="switch-card__image"
                     src="<?= url($card['image_url']) ?>"
                     alt="<?= e($card['name']) ?>"
                     loading="lazy">
            <?php else: ?>
                <span class="switch-card__placeholder">
                    <span class="visually-hidden">No image</span>
                </span>
            <?php endif; ?>
        </div>

        <div class="switch-card__body">
            <span class="switch-card__name"><?= e($card['name']) ?></span>

            <?php if (!empty($card['designer_name'])): ?>
                <span class="switch-card__designer"><?= e($card['designer_name']) ?></span>
            <?php endif; ?>

            <div class="switch-card__tags">
                <?php foreach (switch_card_tags($card) as $tag): ?>
                    <span class="tag <?= $tag === ($card['switch_type'] ?? '') ? $typeClass : '' ?>">
                        <?= e($tag) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

    </a>
</li>
