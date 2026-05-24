<?php

class SwitchController
{
    public function index(): void
    {
        $repo = new SwitchRepository(Database::pdo());

        // Collect filter params from GET, stripping empties.
        $filterKeys = ['switch_type', 'sound_profile', 'feel_profile',
                       'designer_id', 'factory_lubed', 'recommended_use'];
        $filters = [];
        foreach ($filterKeys as $key) {
            $val = trim($_GET[$key] ?? '');
            if ($val !== '') {
                $filters[$key] = $val;
            }
        }

        $sort = trim($_GET['sort'] ?? 'newest');
        if (!in_array($sort, ['newest', 'most_viewed', 'lightest', 'heaviest'], true)) {
            $sort = 'newest';
        }

        view('switches/index', [
            'switches'  => $repo->filtered($filters, $sort),
            'designers' => $repo->allDesigners(),
            'filters'   => $filters,
            'sort'      => $sort,
        ]);
    }

    public function show(string $slug): void
    {
        $repo = new SwitchRepository(Database::pdo());
        $switch = $repo->findBySlug($slug);

        // Only approved switches are public.
        if ($switch === null || $switch['status'] !== 'approved') {
            not_found();
        }

        $repo->incrementViews((int) $switch['id']);
        $switch['views_count'] = (int) $switch['views_count'] + 1; // reflect this visit

        $designerId = $switch['designer_id'] !== null ? (int) $switch['designer_id'] : null;

        view('switches/show', [
            'switch'       => $switch,
            'similar'      => $repo->similarTo($switch),
            'fromDesigner' => $repo->byDesigner($designerId, (int) $switch['id']),
        ]);
    }
}
