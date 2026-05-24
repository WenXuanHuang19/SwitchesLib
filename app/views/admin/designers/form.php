<?php
/**
 * Admin add/edit designer form.
 *
 * @var ?array  $designer the row being edited, or null when creating
 * @var array   $errors   field-level validation errors
 * @var array   $old      previously submitted / existing values
 */
$isEdit = $designer !== null;
$action = $isEdit ? url('/admin/designers/' . $designer['id']) : url('/admin/designers');
$val    = fn(string $name) => e($old[$name] ?? '');
$err    = function (string $name) use ($errors) {
    if (!empty($errors[$name])) {
        echo '<span class="field-error">' . e($errors[$name]) . '</span>';
    }
};
?>

<h1><?= $isEdit ? 'Edit Designer' : 'Add Designer' ?></h1>

<form class="submit-form" method="post" action="<?= $action ?>">
    <label>Name *
        <input type="text" name="name" value="<?= $val('name') ?>" required>
        <?php $err('name'); ?>
    </label>

    <label>Website
        <input type="url" name="website" value="<?= $val('website') ?>">
    </label>

    <label>Country
        <input type="text" name="country" value="<?= $val('country') ?>">
    </label>

    <button type="submit" class="btn"><?= $isEdit ? 'Save changes' : 'Add Designer' ?></button>
</form>
