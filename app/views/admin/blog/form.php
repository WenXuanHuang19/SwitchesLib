<?php
/**
 * Admin add/edit blog post form.
 *
 * @var ?array $post   the post being edited, or null when creating
 * @var array  $errors field-level validation errors
 * @var array  $old    previously submitted / existing values
 */
$isEdit = $post !== null;
$action = $isEdit ? url('/admin/blog/' . $post['id']) : url('/admin/blog');
$val    = fn(string $name) => e($old[$name] ?? '');
$err    = function (string $name) use ($errors) {
    if (!empty($errors[$name])) {
        echo '<span class="field-error">' . e($errors[$name]) . '</span>';
    }
};
?>

<h1><?= $isEdit ? 'Edit Post' : 'New Post' ?></h1>

<form class="submit-form" method="post" action="<?= $action ?>" enctype="multipart/form-data">
    <label>Title *
        <input type="text" name="title" value="<?= $val('title') ?>" required>
        <?php $err('title'); ?>
    </label>

    <label>Category
        <input type="text" name="category" value="<?= $val('category') ?>">
    </label>

    <label>Tags (comma separated)
        <input type="text" name="tags" value="<?= $val('tags') ?>">
    </label>

    <label>Excerpt
        <textarea name="excerpt" rows="3"><?= $val('excerpt') ?></textarea>
    </label>

    <label>Status
        <select name="status">
            <?php foreach (['draft', 'published'] as $st): ?>
                <option value="<?= e($st) ?>" <?= ($old['status'] ?? 'draft') === $st ? 'selected' : '' ?>>
                    <?= e(ucfirst($st)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <?php if ($isEdit && !empty($post['cover_image_url'])): ?>
        <p class="current-image">
            Current cover:
            <img src="<?= url($post['cover_image_url']) ?>" alt="" height="60">
        </p>
    <?php endif; ?>
    <label>Cover image (JPG, PNG or WebP)
        <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp">
        <?php $err('cover_image'); ?>
    </label>

    <label>Content (HTML)
        <textarea name="content" rows="16" class="code-editor"><?= $val('content') ?></textarea>
    </label>

    <button type="submit" class="btn"><?= $isEdit ? 'Save changes' : 'Publish' ?></button>
</form>
