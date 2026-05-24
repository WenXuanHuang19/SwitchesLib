<?php

class SwitchController
{
    public function index(): void
    {
        $repo = new SwitchRepository(Database::pdo());
        view('switches/index', ['switches' => $repo->allApproved()]);
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
