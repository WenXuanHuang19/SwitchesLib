<article class="blog-post">
    <h1 class="blog-post__title"><?= e($post['title']) ?></h1>

    <p class="blog-post__meta">
        <?php if (!empty($post['category'])): ?>
            <span class="blog-post__category"><?= e($post['category']) ?></span>
        <?php endif; ?>
        <?php if (!empty($post['published_at'])): ?>
            <span class="blog-post__date"><?= e(date('M j, Y', strtotime($post['published_at']))) ?></span>
        <?php endif; ?>
    </p>

    <?php if (!empty($post['tags'])): ?>
        <p class="blog-post__tags">
            <?php foreach (array_filter(array_map('trim', explode(',', $post['tags']))) as $tag): ?>
                <span class="tag"><?= e($tag) ?></span>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>

    <!-- Content is admin-authored HTML (PRD: supports images and tables), rendered as-is. -->
    <div class="blog-post__content">
        <?= $post['content'] ?>
    </div>

    <p><a href="<?= url('/blog') ?>">&larr; Back to blog</a></p>
</article>
