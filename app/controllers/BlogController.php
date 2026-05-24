<?php

class BlogController
{
    public function index(): void
    {
        $repo = new BlogRepository(Database::pdo());
        view('blog/index', ['posts' => $repo->allPublished()]);
    }

    public function show(string $slug): void
    {
        $repo = new BlogRepository(Database::pdo());
        $post = $repo->findBySlug($slug);

        // Only published posts are visible to the public.
        if ($post === null || $post['status'] !== 'published') {
            not_found();
        }

        view('blog/show', ['post' => $post]);
    }
}
