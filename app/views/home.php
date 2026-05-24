<!-- Hero -->
<section class="hero">
    <h1 class="hero__title">Switches Lib</h1>
    <p class="hero__slogan">Stop searching blindly. Find your switch.</p>
    <p class="hero__description">
        A community-driven database of mechanical keyboard switches.
        Compare specs, discover new switches, and find your perfect match.
    </p>
    <a class="btn hero__cta" href="<?= url('/switches') ?>">Browse all switches</a>
</section>

<!-- Latest Switches -->
<?php if (!empty($latestSwitches)): ?>
    <section class="home-section">
        <h2 class="home-section__title">Latest Switches</h2>
        <ul class="switch-grid">
            <?php foreach ($latestSwitches as $card): ?>
                <?php require VIEWS_PATH . '/switches/_card.php'; ?>
            <?php endforeach; ?>
        </ul>
        <p class="home-section__more">
            <a href="<?= url('/switches') ?>">See all switches →</a>
        </p>
    </section>
<?php endif; ?>

<!-- Blog Preview -->
<?php if (!empty($latestPosts)): ?>
    <section class="home-section">
        <h2 class="home-section__title">From the Blog</h2>
        <ul class="blog-preview-list">
            <?php foreach ($latestPosts as $post): ?>
                <li class="blog-preview-card">
                    <a class="blog-preview-card__link" href="<?= url('/blog/' . $post['slug']) ?>">
                        <h3 class="blog-preview-card__title"><?= e($post['title']) ?></h3>
                    </a>
                    <?php if (!empty($post['excerpt'])): ?>
                        <p class="blog-preview-card__excerpt"><?= e($post['excerpt']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($post['published_at'])): ?>
                        <span class="blog-preview-card__date">
                            <?= e(date('M j, Y', strtotime($post['published_at']))) ?>
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="home-section__more">
            <a href="<?= url('/blog') ?>">Read all posts →</a>
        </p>
    </section>
<?php endif; ?>
