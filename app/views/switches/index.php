<?php
/**
 * @var array  $switches   List of switch cards.
 * @var array  $designers  All designers for the filter select.
 * @var array  $filters    Active filter values.
 * @var string $sort       Active sort key.
 * @var string $search     Active search term (may be empty string).
 */
?>
<h1>Switches</h1>

<!-- Filter bar -->
<form class="filter-bar" method="get" action="<?= url('/switches') ?>">

    <div class="filter-bar__row filter-bar__row--search">
        <input
            type="text"
            name="q"
            class="filter-bar__search"
            placeholder="Search by name or designer…"
            value="<?= e($search ?? '') ?>"
        >
    </div>

    <div class="filter-bar__row">

        <select name="switch_type">
            <option value="">All Types</option>
            <?php foreach (['Linear','Tactile','Clicky','Silent Linear','Silent Tactile'] as $opt): ?>
                <option value="<?= e($opt) ?>" <?= ($filters['switch_type'] ?? '') === $opt ? 'selected' : '' ?>>
                    <?= e($opt) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="sound_profile">
            <option value="">All Sounds</option>
            <?php foreach (['Creamy','Clacky','Thocky','Muted','Poppy','Bright','Unknown'] as $opt): ?>
                <option value="<?= e($opt) ?>" <?= ($filters['sound_profile'] ?? '') === $opt ? 'selected' : '' ?>>
                    <?= e($opt) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="feel_profile">
            <option value="">All Feels</option>
            <?php foreach (['Smooth','Light','Medium','Heavy','Tactile','Snappy','Stable','Unknown'] as $opt): ?>
                <option value="<?= e($opt) ?>" <?= ($filters['feel_profile'] ?? '') === $opt ? 'selected' : '' ?>>
                    <?= e($opt) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="designer_id">
            <option value="">All Designers</option>
            <?php foreach ($designers as $d): ?>
                <option value="<?= e((string) $d['id']) ?>"
                    <?= ($filters['designer_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>>
                    <?= e($d['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="factory_lubed">
            <option value="">Any Lube</option>
            <?php foreach (['Yes','No','Unknown'] as $opt): ?>
                <option value="<?= e($opt) ?>" <?= ($filters['factory_lubed'] ?? '') === $opt ? 'selected' : '' ?>>
                    <?= e($opt) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="recommended_use">
            <option value="">All Uses</option>
            <?php foreach (['Beginner Friendly','Office','Gaming','Typing','Quiet Setup','Budget Build','Unknown'] as $opt): ?>
                <option value="<?= e($opt) ?>" <?= ($filters['recommended_use'] ?? '') === $opt ? 'selected' : '' ?>>
                    <?= e($opt) ?>
                </option>
            <?php endforeach; ?>
        </select>

    </div>

    <div class="filter-bar__row filter-bar__row--actions">
        <select name="sort">
            <?php foreach ([
                'newest'      => 'Newest',
                'most_viewed' => 'Most Viewed',
                'lightest'    => 'Lightest to Heaviest',
                'heaviest'    => 'Heaviest to Lightest',
            ] as $val => $label): ?>
                <option value="<?= e($val) ?>" <?= $sort === $val ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">Apply</button>

        <?php if (!empty($filters) || ($search ?? '') !== ''): ?>
            <a href="<?= url('/switches') ?>" class="btn btn--ghost">Clear filters</a>
        <?php endif; ?>
    </div>

</form>

<?php if (empty($switches)): ?>
    <p class="empty-state">
        No switches match your filters.<br>
        <a href="<?= url('/switches') ?>">Clear filters to see all →</a>
    </p>
<?php else: ?>
    <ul class="switch-grid">
        <?php foreach ($switches as $card): ?>
            <?php require VIEWS_PATH . '/switches/_card.php'; ?>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
