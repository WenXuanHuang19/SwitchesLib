<?php
/**
 * Submission form. $designers, $errors, $old are provided by SubmitController.
 * Small closures keep the repetitive field markup DRY.
 */
$val = fn(string $name) => e($old[$name] ?? '');
$err = function (string $name) use ($errors) {
    if (!empty($errors[$name])) {
        echo '<span class="field-error">' . e($errors[$name]) . '</span>';
    }
};
$text = function (string $name, string $label, string $type = 'text') use ($val, $err) {
    echo '<label>' . e($label)
       . '<input type="' . $type . '" name="' . e($name) . '" value="' . $val($name) . '">';
    $err($name);
    echo '</label>';
};
$enum = function (string $name, string $label) use ($old, $err) {
    $current = $old[$name] ?? 'Unknown';
    echo '<label>' . e($label) . '<select name="' . e($name) . '">';
    foreach (['Yes', 'No', 'Unknown'] as $opt) {
        $sel = $current === $opt ? ' selected' : '';
        echo '<option value="' . e($opt) . '"' . $sel . '>' . e($opt) . '</option>';
    }
    echo '</select>';
    $err($name);
    echo '</label>';
};
$tagSelect = function (string $name, string $label, array $options) use ($old, $err) {
    $current = $old[$name] ?? 'Unknown';
    echo '<label>' . e($label) . '<select name="' . e($name) . '">';
    foreach ($options as $opt) {
        $sel = $current === $opt ? ' selected' : '';
        echo '<option value="' . e($opt) . '"' . $sel . '>' . e($opt) . '</option>';
    }
    echo '</select>';
    $err($name);
    echo '</label>';
};
?>

<h1>Submit a Switch</h1>
<p class="form-hint">Fill in what you know. For specs you're unsure of, leave the field blank or enter <strong>Unknown</strong>.</p>

<form class="submit-form" method="post" action="<?= url('/submit') ?>">

    <fieldset>
        <legend>Identity</legend>

        <label>Switch name *
            <input type="text" name="name" value="<?= $val('name') ?>" required>
            <?php $err('name'); ?>
        </label>

        <label>Designer or Studio *
            <select name="designer_id" required>
                <option value="">— Select —</option>
                <?php foreach ($designers as $d): ?>
                    <option value="<?= e((string) $d['id']) ?>"
                        <?= ($old['designer_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>>
                        <?= e($d['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php $err('designer_id'); ?>
        </label>

        <label>Switch Type *
            <select name="switch_type" required>
                <option value="">— Select —</option>
                <?php foreach (Submission::SWITCH_TYPES as $t): ?>
                    <option value="<?= e($t) ?>" <?= ($old['switch_type'] ?? '') === $t ? 'selected' : '' ?>>
                        <?= e($t) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php $err('switch_type'); ?>
        </label>
    </fieldset>

    <fieldset>
        <legend>Basic information</legend>
        <?php
        $text('series', 'Series');
        $text('variant', 'Variant');
        $text('manufacturer', 'Manufacturer');
        $text('switch_category', 'Switch Category');
        $text('release_date', 'Release date', 'date');
        ?>
    </fieldset>

    <fieldset>
        <legend>Force and travel</legend>
        <?php
        $text('initial_force', 'Initial force');
        $text('actuation_force', 'Actuation force');
        $text('bottom_out_force', 'Bottom-out force');
        $text('tactile_force', 'Tactile force');
        $text('actuation_travel', 'Actuation travel');
        $text('total_travel', 'Total travel');
        $text('spring_length', 'Spring length');
        $text('spring_type', 'Spring type');
        ?>
    </fieldset>

    <fieldset>
        <legend>Materials and structure</legend>
        <?php
        $text('top_housing_material', 'Top housing');
        $text('bottom_housing_material', 'Bottom housing');
        $text('stem_material', 'Stem material');
        $text('stem_type', 'Stem type');
        $text('contact_material', 'Contact material');
        $text('pin_count', 'Pin count');
        $enum('led_diffuser', 'LED diffuser');
        $enum('rgb_support', 'RGB support');
        $enum('factory_lubed', 'Factory lubed');
        $enum('is_silent', 'Silent');
        $text('silent_structure', 'Silent structure');
        ?>
    </fieldset>

    <fieldset>
        <legend>Tags and recommended use</legend>
        <?php
        $tagSelect('sound_profile', 'Sound Profile', ['Unknown','Creamy','Clacky','Thocky','Muted','Poppy','Bright']);
        $tagSelect('feel_profile', 'Feel Profile', ['Unknown','Smooth','Light','Medium','Heavy','Tactile','Snappy','Stable']);
        $tagSelect('recommended_use', 'Recommended Use', ['Unknown','Beginner Friendly','Office','Gaming','Typing','Quiet Setup','Budget Build']);
        ?>
    </fieldset>

    <fieldset>
        <legend>Media and description</legend>
        <?php $text('image_url', 'Image URL', 'url'); ?>
        <label>Description
            <textarea name="description" rows="4"><?= $val('description') ?></textarea>
        </label>
    </fieldset>

    <button type="submit" class="btn">Submit for review</button>
</form>
