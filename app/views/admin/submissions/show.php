<?php
/**
 * Admin review page — shows all fields, allows inline editing, and provides
 * Approve / Reject actions.
 *
 * @var array  $submission    row with all switch fields + designer_name + submitter_username
 * @var array  $designers     designer rows for the dropdown
 * @var array  $errors        field-level validation errors from the last save attempt
 * @var array  $attachedAudio recordings bundled with this submission (may be empty)
 */
$isPending = $submission['status'] === 'Pending';
$old = $errors !== [] ? ($_POST + $submission) : $submission;

$val = fn(string $name) => e($old[$name] ?? '');
$err = function (string $name) use ($errors) {
    if (!empty($errors[$name])) echo '<span class="field-error">' . e($errors[$name]) . '</span>';
};
$text = function (string $name, string $label, string $type = 'text') use ($val, $err) {
    echo '<label>' . e($label)
       . '<input type="' . $type . '" name="' . e($name) . '" value="' . $val($name) . '">';
    $err($name); echo '</label>';
};
$enum = function (string $name, string $label) use ($old, $err) {
    $current = $old[$name] ?? 'Unknown';
    echo '<label>' . e($label) . '<select name="' . e($name) . '">';
    foreach (['Yes', 'No', 'Unknown'] as $opt) {
        echo '<option value="' . e($opt) . '"' . ($current === $opt ? ' selected' : '') . '>' . e($opt) . '</option>';
    }
    echo '</select>'; $err($name); echo '</label>';
};
$tagSelect = function (string $name, string $label, array $options) use ($old, $err) {
    $current = $old[$name] ?? 'Unknown';
    echo '<label>' . e($label) . '<select name="' . e($name) . '">';
    foreach ($options as $opt) {
        echo '<option value="' . e($opt) . '"' . ($current === $opt ? ' selected' : '') . '>' . e($opt) . '</option>';
    }
    echo '</select>'; $err($name); echo '</label>';
};
?>

<div class="admin-header">
    <h1><?= e($submission['name']) ?></h1>
    <p class="submission-meta">
        Submitted by <strong><?= e($submission['submitter_username']) ?></strong>
        on <?= e(date('M j, Y', strtotime($submission['created_at']))) ?>
        &mdash; Status: <span class="status status--<?= e(strtolower($submission['status'])) ?>"><?= e($submission['status']) ?></span>
    </p>
</div>

<form class="submit-form" method="post" action="<?= url('/admin/submissions/' . $submission['id'] . '/approve') ?>">

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
                        <?= (string) ($old['designer_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>>
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

    <fieldset><legend>Basic information</legend>
        <?php $text('series','Series'); $text('variant','Variant'); $text('manufacturer','Manufacturer'); $text('switch_category','Switch Category'); $text('release_date','Release date','date'); ?>
    </fieldset>

    <fieldset><legend>Force and travel</legend>
        <?php $text('initial_force','Initial force'); $text('actuation_force','Actuation force'); $text('bottom_out_force','Bottom-out force'); $text('tactile_force','Tactile force'); $text('actuation_travel','Actuation travel'); $text('total_travel','Total travel'); $text('spring_length','Spring length'); $text('spring_type','Spring type'); ?>
    </fieldset>

    <fieldset><legend>Materials and structure</legend>
        <?php $text('top_housing_material','Top housing'); $text('bottom_housing_material','Bottom housing'); $text('stem_material','Stem material'); $text('stem_type','Stem type'); $text('contact_material','Contact material'); $text('pin_count','Pin count'); $enum('led_diffuser','LED diffuser'); $enum('rgb_support','RGB support'); $enum('factory_lubed','Factory lubed'); $enum('is_silent','Silent'); $text('silent_structure','Silent structure'); ?>
    </fieldset>

    <fieldset><legend>Tags and recommended use</legend>
        <?php
        $tagSelect('sound_profile','Sound Profile',['Unknown','Creamy','Clacky','Thocky','Muted','Poppy','Bright']);
        $tagSelect('feel_profile','Feel Profile',['Unknown','Smooth','Light','Medium','Heavy','Tactile','Snappy','Stable']);
        $tagSelect('recommended_use','Recommended Use',['Unknown','Beginner Friendly','Office','Gaming','Typing','Quiet Setup','Budget Build']);
        ?>
    </fieldset>

    <fieldset><legend>Media and description</legend>
        <?php $text('image_url','Image URL','url'); ?>
        <?php if (!empty($attachedAudio)): ?>
            <?php $kbLabels = [
                'keyboard_name' => 'Keyboard name', 'keyboard_type' => 'Keyboard type',
                'case_material' => 'Case material', 'plate_material' => 'Plate material',
                'mounting_style' => 'Mounting style', 'pcb' => 'PCB',
                'foam_filling' => 'Foam / filling', 'keycap_material' => 'Keycap material',
                'keycap_profile' => 'Keycap profile', 'microphone' => 'Microphone',
            ]; ?>
            <div class="attached-audio">
                <span class="attached-audio__label">Attached recording<?= count($attachedAudio) > 1 ? 's' : '' ?> (published on approval):</span>
                <?php foreach ($attachedAudio as $rec): ?>
                    <audio controls src="<?= url($rec['audio_url']) ?>"></audio>
                    <dl class="admin-review">
                        <?php foreach ($kbLabels as $col => $label): ?>
                            <dt><?= e($label) ?></dt>
                            <dd><?= e(or_unknown($rec[$col] ?? null)) ?></dd>
                        <?php endforeach; ?>
                    </dl>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <label>Description <textarea name="description" rows="4"><?= $val('description') ?></textarea></label>
    </fieldset>

    <?php if ($isPending): ?>
        <div class="form-actions">
            <button type="submit" name="action" value="save" formaction="<?= url('/admin/submissions/' . $submission['id'] . '/update') ?>" class="btn btn--ghost">Save changes</button>
            <button type="submit" class="btn btn--approve">Approve &amp; publish</button>
            <button type="submit" formaction="<?= url('/admin/submissions/' . $submission['id'] . '/reject') ?>" class="btn btn--danger" onclick="return confirm('Reject this submission?');">Reject</button>
        </div>
    <?php endif; ?>
</form>
