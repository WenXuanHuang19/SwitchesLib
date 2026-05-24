<?php

class AdminTagController
{
    public function index(): void
    {
        Auth::requireAdmin();

        $repo = new TagRepository(Database::pdo());
        $tags = $repo->all();

        // Group by type for the view.
        $typeLabels = [
            'switch_type'     => 'Switch Type',
            'sound_profile'   => 'Sound Profile',
            'feel_profile'    => 'Feel Profile',
            'recommended_use' => 'Recommended Use',
        ];

        $grouped = [];
        foreach ($tags as $tag) {
            $grouped[$tag['type']][] = $tag;
        }

        view('admin/tags/index', [
            'active'     => 'tags',
            'grouped'    => $grouped,
            'typeLabels' => $typeLabels,
        ], 'admin/partials');
    }
}
