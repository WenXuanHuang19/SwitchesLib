<?php

class HomeController
{
    public function index(): void
    {
        $switchRepo = new SwitchRepository(Database::pdo());
        $blogRepo   = new BlogRepository(Database::pdo());

        view('home', [
            'latestSwitches' => $switchRepo->latest(9),
            'latestPosts'    => array_slice($blogRepo->allPublished(), 0, 3),
        ]);
    }
}
