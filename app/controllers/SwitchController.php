<?php

class SwitchController
{
    // Real switch list is built in Slice 2.
    public function index(): void
    {
        view('switches/index');
    }

    // Real switch detail is built in Slice 5. For now, confirm the slug is captured.
    public function show(string $slug): void
    {
        view('switches/show', ['slug' => $slug]);
    }
}
