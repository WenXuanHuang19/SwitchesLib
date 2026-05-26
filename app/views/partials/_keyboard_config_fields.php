<?php
/**
 * Reusable recording-environment fields for audio (PRD v1.0 §20.1).
 * Used by the audio submission form, the new-switch submit form, and the admin
 * switch form. Render inside an existing <form>.
 *
 * @var array $audioConfig Optional col=>value map to repopulate; defaults to 'Unknown'.
 */
$audioConfig = $audioConfig ?? [];
$kbFields = [
    'keyboard_name'   => 'Keyboard name',
    'keyboard_type'   => 'Keyboard type',
    'case_material'   => 'Case material',
    'plate_material'  => 'Plate material',
    'mounting_style'  => 'Mounting style',
    'pcb'             => 'PCB',
    'foam_filling'    => 'Foam / filling',
    'keycap_material' => 'Keycap material',
    'keycap_profile'  => 'Keycap profile',
    'microphone'      => 'Microphone',
];
?>
<fieldset>
    <legend>Recording setup</legend>
    <p class="form-hint">Describe the keyboard this was recorded on. Leave "Unknown" if you're not sure.</p>
    <?php foreach ($kbFields as $name => $label): ?>
        <label><?= e($label) ?>
            <input type="text" name="<?= e($name) ?>"
                   value="<?= e($audioConfig[$name] ?? 'Unknown') ?>">
        </label>
    <?php endforeach; ?>
</fieldset>
