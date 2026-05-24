<?php

class AdminController
{
    /** Admin dashboard: live counts plus the most recently added switches. */
    public function dashboard(): void
    {
        Auth::requireAdmin();

        $pdo = Database::pdo();
        $switches    = new SwitchRepository($pdo);
        $submissions = new SubmissionRepository($pdo);
        $blog        = new BlogRepository($pdo);
        $users       = new UserRepository($pdo);

        view('admin/dashboard', [
            'active'        => 'dashboard',
            'switchCount'   => $switches->count(),
            'pendingCount'  => $submissions->countPending(),
            'blogCount'     => $blog->count(),
            'userCount'     => $users->count(),
            'recentSwitches' => $switches->recent(5),
        ], 'admin/partials');
    }
}
