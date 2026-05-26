<?php
/**
 * @var array  $switch       The switch record (injected by SwitchController).
 * @var array  $similar      Similar switch cards (may be empty).
 * @var array  $fromDesigner More switches from the same designer (may be empty).
 * @var ?array $recording    Most recent typing recording, or null if none.
 */

/** Map switch_type → tag modifier class */
$typeClass = match ($switch['switch_type'] ?? '') {
    'Linear'         => 'tag--linear',
    'Tactile'        => 'tag--tactile',
    'Clicky'         => 'tag--clicky',
    'Silent Linear'  => 'tag--silent-linear',
    'Silent Tactile' => 'tag--silent-tactile',
    default          => 'tag--unknown',
};
?>
<article class="switch-detail">

    <!-- 1. Hero: image + name + key tags -->
    <header class="switch-detail__hero">

        <div>
            <?php if (!empty($switch['image_url'])): ?>
                <img class="switch-detail__image"
                     src="<?= url($switch['image_url']) ?>"
                     alt="<?= e($switch['name']) ?>">
            <?php else: ?>
                <span class="switch-detail__placeholder">No image available</span>
            <?php endif; ?>
        </div>

        <div class="switch-detail__info-hero">
            <h1 class="switch-detail__name"><?= e($switch['name']) ?></h1>

            <?php if (!empty($switch['designer_name'])): ?>
                <p class="switch-detail__designer-line">
                    by <?= e($switch['designer_name']) ?>
                </p>
            <?php endif; ?>

            <div class="switch-detail__tags">
                <?php if (!empty($switch['switch_type'])): ?>
                    <span class="tag <?= $typeClass ?>"><?= e($switch['switch_type']) ?></span>
                <?php endif; ?>
                <?php if (!empty($switch['sound_profile']) && $switch['sound_profile'] !== 'Unknown'): ?>
                    <span class="tag"><?= e($switch['sound_profile']) ?></span>
                <?php endif; ?>
                <?php if (!empty($switch['feel_profile']) && $switch['feel_profile'] !== 'Unknown'): ?>
                    <span class="tag"><?= e($switch['feel_profile']) ?></span>
                <?php endif; ?>
                <?php if (!empty($switch['recommended_use']) && $switch['recommended_use'] !== 'Unknown'): ?>
                    <span class="tag"><?= e($switch['recommended_use']) ?></span>
                <?php endif; ?>
            </div>
        </div>

    </header>

    <!-- 1b. Typing recording (community content, kept apart from official specs — ADR-0006) -->
    <section class="switch-detail__audio">
        <h2>Typing Sound</h2>
        <?php if (!empty($recording['audio_url'])): ?>
            <audio class="switch-detail__player" controls
                   src="<?= url($recording['audio_url']) ?>"></audio>
            <p class="switch-detail__audio-credit">
                Recorded by <?= e($recording['uploader_name'] ?? 'a community member') ?>
                <span class="switch-detail__audio-note">— community recording, not an official spec</span>
            </p>
        <?php else: ?>
            <p class="switch-detail__audio-empty">
                No recording yet — be the first to submit one.
            </p>
        <?php endif; ?>

        <?php if (Auth::check()): ?>
            <a class="switch-detail__audio-submit"
               href="<?= url('/switches/' . $switch['slug'] . '/submit-audio') ?>">
                Submit a recording
            </a>
        <?php endif; ?>
    </section>

    <!-- 2. Basic information -->
    <section class="switch-detail__section">
        <h2>Basic Information</h2>
        <dl>
            <dt>Designer or Studio</dt><dd><?= e(or_unknown($switch['designer_name'])) ?></dd>
            <dt>Series</dt>           <dd><?= e(or_unknown($switch['series'])) ?></dd>
            <dt>Variant</dt>          <dd><?= e(or_unknown($switch['variant'])) ?></dd>
            <dt>Manufacturer</dt>     <dd><?= e(or_unknown($switch['manufacturer'])) ?></dd>
            <dt>Switch Category</dt>  <dd><?= e(or_unknown($switch['switch_category'])) ?></dd>
            <dt>Switch Type</dt>      <dd><?= e(or_unknown($switch['switch_type'])) ?></dd>
            <dt>Release Date</dt>     <dd><?= e(or_unknown($switch['release_date'])) ?></dd>
        </dl>
    </section>

    <!-- 3. Force and travel -->
    <section class="switch-detail__section">
        <h2>Force &amp; Travel</h2>
        <dl>
            <dt>Initial Force</dt>    <dd><?= e(or_unknown($switch['initial_force'])) ?></dd>
            <dt>Actuation Force</dt>  <dd><?= e(or_unknown($switch['actuation_force'])) ?></dd>
            <dt>Bottom-out Force</dt> <dd><?= e(or_unknown($switch['bottom_out_force'])) ?></dd>
            <dt>Tactile Force</dt>    <dd><?= e(or_unknown($switch['tactile_force'])) ?></dd>
            <dt>Actuation Travel</dt> <dd><?= e(or_unknown($switch['actuation_travel'])) ?></dd>
            <dt>Total Travel</dt>     <dd><?= e(or_unknown($switch['total_travel'])) ?></dd>
            <dt>Spring Length</dt>    <dd><?= e(or_unknown($switch['spring_length'])) ?></dd>
            <dt>Spring Type</dt>      <dd><?= e(or_unknown($switch['spring_type'])) ?></dd>
        </dl>
    </section>

    <!-- 4. Materials and structure -->
    <section class="switch-detail__section">
        <h2>Materials &amp; Structure</h2>
        <dl>
            <dt>Top Housing</dt>      <dd><?= e(or_unknown($switch['top_housing_material'])) ?></dd>
            <dt>Bottom Housing</dt>   <dd><?= e(or_unknown($switch['bottom_housing_material'])) ?></dd>
            <dt>Stem Material</dt>    <dd><?= e(or_unknown($switch['stem_material'])) ?></dd>
            <dt>Stem Type</dt>        <dd><?= e(or_unknown($switch['stem_type'])) ?></dd>
            <dt>Contact Material</dt> <dd><?= e(or_unknown($switch['contact_material'])) ?></dd>
            <dt>Pin Count</dt>        <dd><?= e(or_unknown($switch['pin_count'])) ?></dd>
            <dt>LED Diffuser</dt>     <dd><?= e(or_unknown($switch['led_diffuser'])) ?></dd>
            <dt>RGB Support</dt>      <dd><?= e(or_unknown($switch['rgb_support'])) ?></dd>
            <dt>Factory Lubed</dt>    <dd><?= e(or_unknown($switch['factory_lubed'])) ?></dd>
            <dt>Silent</dt>           <dd><?= e(or_unknown($switch['is_silent'])) ?></dd>
            <dt>Silent Structure</dt> <dd><?= e(or_unknown($switch['silent_structure'])) ?></dd>
        </dl>
    </section>

    <?php if (!empty($switch['description'])): ?>
        <section class="switch-detail__section">
            <h2>About</h2>
            <p><?= e($switch['description']) ?></p>
        </section>
    <?php endif; ?>

    <!-- 5. Similar switches -->
    <?php if (!empty($similar)): ?>
        <section class="switch-detail__section">
            <h2>Similar Switches</h2>
            <ul class="switch-grid">
                <?php foreach ($similar as $card): ?>
                    <?php require VIEWS_PATH . '/switches/_card.php'; ?>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- 6. More from this designer or studio -->
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

    <!-- 7. Metadata -->
    <footer class="switch-detail__meta">
        <span><?= e((string) $switch['views_count']) ?> views</span>
        <span>Added <?= e(or_unknown($switch['created_at'])) ?></span>
    </footer>

</article>
