<?php

class HomeController
{
    public function index(): void
    {
        // Prove the DB singleton connects. The real home page is built in Slice 7.
        try {
            Database::pdo();
            $dbStatus = 'connected';
        } catch (PDOException $e) {
            $dbStatus = 'not connected: ' . $e->getMessage();
        }

        view('home', ['dbStatus' => $dbStatus]);
    }
}
