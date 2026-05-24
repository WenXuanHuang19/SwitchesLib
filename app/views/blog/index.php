<h1>Blog</h1>

<?php if (empty($posts)): ?>
    <p class="empty-state">No articles yet. Check back soon.</p>
<?php else: ?>
    <ul class="blog-list">
        <?php foreach ($posts as $post): ?>
            <li class="blog-card">
                <a class="blog-card__link" href="<?= url('/blog/' . $post['slug']) ?>">
                    <?php if (!empty($post['cover_image_url'])): ?>
                        <img class="blog-card__cover" src="<?= url($post['cover_image_url']) ?>" alt="<?= e($post['title']) ?>">
                    <?php else: ?>
                        <span class="blog-card__cover-placeholder">No cover image</span>
                    <?php endif; ?>

                    <h2 class="blog-card__title"><?= e($post['title']) ?></h2>
                </a>

                <p class="blog-card__meta">
                    <?php if (!empty($post['category'])): ?>
                        <span class="blog-card__category"><?= e($post['category']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($post['published_at'])): ?>
                        <span class="blog-card__date"><?= e(date('M j, Y', strtotime($post['published_at']))) ?></span>
                    <?php endif; ?>
                </p>

                <?php if (!empty($post['tags'])): ?>
                    <p class="blog-card__tags">
                        <?php foreach (array_filter(array_map('trim', explode(',', $post['tags']))) as $tag): ?>
                            <span class="tag"><?= e($tag) ?></span>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($post['excerpt'])): ?>
                    <p class="blog-card__excerpt"><?= e($post['excerpt']) ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
