<?php

/**
 * Render a view file from app/views, wrapped in the shared header/footer.
 * Variables passed in $data become local variables inside the view.
 */
function view(string $name, array $data = []): void
{
    extract($data);
    $viewFile = VIEWS_PATH . '/' . $name . '.php';

    require VIEWS_PATH . '/partials/header.php';
    require $viewFile;
    require VIEWS_PATH . '/partials/footer.php';
}

/** Escape a string for safe output in HTML. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Build a URL relative to the app's base path, so links work whether the app
 * is served from the domain root or a subdirectory (e.g. /SwitchesLib/public).
 */
function url(string $path = ''): string
{
    return BASE_PATH . '/' . ltrim($path, '/');
}

/**
 * The tags shown on a switch card, in priority order:
 * Switch Type, then Sound Profile, then Recommended Use.
 */
function switch_card_tags(array $switch): array
{
    $candidates = [
        $switch['switch_type'],
        $switch['sound_profile'],
        $switch['recommended_use'],
    ];

    return array_values(array_filter(
        $candidates,
        fn($tag) => $tag !== 'Unknown'
    ));
}
