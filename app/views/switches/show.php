<article class="switch-detail">

    <!-- 1. Hero -->
    <header class="switch-detail__hero">
        <?php if (!empty($switch['image_url'])): ?>
            <img class="switch-detail__image" src="<?= url($switch['image_url']) ?>" alt="<?= e($switch['name']) ?>">
        <?php else: ?>
            <span class="switch-detail__placeholder">No image</span>
        <?php endif; ?>
        <h1 class="switch-detail__name"><?= e($switch['name']) ?></h1>
    </header>

    <!-- 2. Basic information -->
    <section class="switch-detail__section">
        <h2>Basic information</h2>
        <dl>
            <dt>Designer or Studio</dt><dd><?= e(or_unknown($switch['designer_name'])) ?></dd>
            <dt>Series</dt>           <dd><?= e(or_unknown($switch['series'])) ?></dd>
            <dt>Variant</dt>          <dd><?= e(or_unknown($switch['variant'])) ?></dd>
            <dt>Manufacturer</dt>     <dd><?= e(or_unknown($switch['manufacturer'])) ?></dd>
            <dt>Switch Category</dt>  <dd><?= e(or_unknown($switch['switch_category'])) ?></dd>
            <dt>Switch Type</dt>      <dd><?= e(or_unknown($switch['switch_type'])) ?></dd>
            <dt>Release date</dt>     <dd><?= e(or_unknown($switch['release_date'])) ?></dd>
        </dl>
    </section>

    <!-- 3. Force and travel -->
    <section class="switch-detail__section">
        <h2>Force and travel</h2>
        <dl>
            <dt>Initial force</dt>    <dd><?= e(or_unknown($switch['initial_force'])) ?></dd>
            <dt>Actuation force</dt>  <dd><?= e(or_unknown($switch['actuation_force'])) ?></dd>
            <dt>Bottom-out force</dt> <dd><?= e(or_unknown($switch['bottom_out_force'])) ?></dd>
            <dt>Tactile force</dt>    <dd><?= e(or_unknown($switch['tactile_force'])) ?></dd>
            <dt>Actuation travel</dt> <dd><?= e(or_unknown($switch['actuation_travel'])) ?></dd>
            <dt>Total travel</dt>     <dd><?= e(or_unknown($switch['total_travel'])) ?></dd>
            <dt>Spring length</dt>    <dd><?= e(or_unknown($switch['spring_length'])) ?></dd>
            <dt>Spring type</dt>      <dd><?= e(or_unknown($switch['spring_type'])) ?></dd>
        </dl>
    </section>

    <!-- 4. Materials and structure -->
    <section class="switch-detail__section">
        <h2>Materials and structure</h2>
        <dl>
            <dt>Top housing</dt>      <dd><?= e(or_unknown($switch['top_housing_material'])) ?></dd>
            <dt>Bottom housing</dt>   <dd><?= e(or_unknown($switch['bottom_housing_material'])) ?></dd>
            <dt>Stem material</dt>    <dd><?= e(or_unknown($switch['stem_material'])) ?></dd>
            <dt>Stem type</dt>        <dd><?= e(or_unknown($switch['stem_type'])) ?></dd>
            <dt>Contact material</dt> <dd><?= e(or_unknown($switch['contact_material'])) ?></dd>
            <dt>Pin count</dt>        <dd><?= e(or_unknown($switch['pin_count'])) ?></dd>
            <dt>LED diffuser</dt>     <dd><?= e(or_unknown($switch['led_diffuser'])) ?></dd>
            <dt>RGB support</dt>      <dd><?= e(or_unknown($switch['rgb_support'])) ?></dd>
            <dt>Factory lubed</dt>    <dd><?= e(or_unknown($switch['factory_lubed'])) ?></dd>
            <dt>Silent</dt>           <dd><?= e(or_unknown($switch['is_silent'])) ?></dd>
            <dt>Silent structure</dt> <dd><?= e(or_unknown($switch['silent_structure'])) ?></dd>
        </dl>
    </section>

    <!-- 5. Tags and recommended use -->
    <section class="switch-detail__section">
        <h2>Tags</h2>
        <p class="switch-detail__tags">
            <span class="tag"><?= e(or_unknown($switch['switch_type'])) ?></span>
            <span class="tag"><?= e(or_unknown($switch['sound_profile'])) ?></span>
            <span class="tag"><?= e(or_unknown($switch['feel_profile'])) ?></span>
            <span class="tag"><?= e(or_unknown($switch['recommended_use'])) ?></span>
        </p>
    </section>

    <?php if (!empty($switch['description'])): ?>
        <section class="switch-detail__section">
            <h2>About</h2>
            <p><?= e($switch['description']) ?></p>
        </section>
    <?php endif; ?>

    <!-- 6. Similar switches (hidden when none) -->
    <?php if (!empty($similar)): ?>
        <section class="switch-detail__section">
            <h2>Similar switches</h2>
            <ul class="switch-grid">
                <?php foreach ($similar as $card): ?>
                    <?php require VIEWS_PATH . '/switches/_card.php'; ?>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- 7. More from this designer or studio (hidden when none) -->
    <?php if (!empty($fromDesigner)): ?>
        <section class="switch-detail__section">
            <h2>More from <?= e(or_unknown($switch['designer_name'] ?? null)) ?></h2>
            <ul class="switch-grid">
                <?php foreach ($fromDesigner as $card): ?>
                    <?php require VIEWS_PATH . '/switches/_card.php'; ?>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- 8. Metadata -->
    <footer class="switch-detail__meta">
        <span>Views: <?= e((string) $switch['views_count']) ?></span>
        <span>Added: <?= e(or_unknown($switch['created_at'])) ?></span>
    </footer>
</article>
