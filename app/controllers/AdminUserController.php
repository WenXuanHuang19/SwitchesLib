<?php

class AdminUserController
{
    public function index(): void
    {
        Auth::requireAdmin();
        $repo = new UserRepository(Database::pdo());
        view('admin/users/index', [
            'active' => 'users',
            'users'  => $repo->all(),
        ], 'admin/partials');
    }

    public function updateRole(string $id): void
    {
        Auth::requireAdmin();

        $role     = $_POST['role'] ?? '';
        $newRole  = $role === 'admin' ? 'admin' : 'user';
        $myId     = Auth::id();
        $targetId = (int) $id;

        if ($targetId === $myId) {
            flash('You cannot change your own role.');
        } elseif (!in_array($role, ['user', 'admin'], true)) {
            flash('Invalid role.');
        } else {
            (new UserRepository(Database::pdo()))->updateRole($targetId, $newRole);
            flash('User role updated.');
        }

        $this->redirectToList();
    }

    private function redirectToList(): void
    {
        header('Location: ' . url('/admin/users'));
        exit;
    }
}
