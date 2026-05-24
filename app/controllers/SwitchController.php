<?php

class SwitchController
{
    public function index(): void
    {
        $repo = new SwitchRepository(Database::pdo());
        view('switches/index', ['switches' => $repo->allApproved()]);
    }

    // Real switch detail is built in Slice 5. For now, confirm the slug is captured.
    public function show(string $slug): void
    {
        view('switches/show', ['slug' => $slug]);
    }
}
