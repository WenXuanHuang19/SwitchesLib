<?php

class AdminSubmissionController
{
    public function index(): void
    {
        Auth::requireAdmin();

        $repo   = new SubmissionRepository(Database::pdo());
        $status = $_GET['status'] ?? '';
        $list   = in_array($status, ['Pending', 'Approved', 'Rejected'], true)
            ? $repo->filtered($status)
            : $repo->all();

        view('admin/submissions/index', [
            'active'       => 'submissions',
            'submissions'  => $list,
            'currentStatus'=> $status,
        ], 'admin/partials');
    }

    public function show(string $id): void
    {
        Auth::requireAdmin();

        $repo = new SubmissionRepository(Database::pdo());
        $sub  = $repo->findById((int) $id);
        if ($sub === null) not_found();

        $this->renderShow($sub, []);
    }

    public function update(string $id): void
    {
        Auth::requireAdmin();

        $repo = new SubmissionRepository(Database::pdo());
        $sub  = $repo->findById((int) $id);
        if ($sub === null) not_found();

        $result = Submission::validate($_POST);
        $errors = $result['errors'] ?? [];

        if ($errors === []) {
            $repo->update((int) $id, $result['data']);
            flash('Submission updated.');
            // Re-fetch so the form shows the updated values.
            $sub = $repo->findById((int) $id);
        }

        $this->renderShow($sub, $errors);
    }

    public function approve(string $id): void
    {
        Auth::requireAdmin();
        $repo = new SubmissionRepository(Database::pdo());

        // Optionally save any field edits carried in the POST body before approving.
        $result = Submission::validate($_POST);
        if (!isset($result['errors'])) {
            $repo->update((int) $id, $result['data']);
        }

        $repo->approve((int) $id, (int) Auth::id());
        flash('Submission approved. Switch is now public.');
        $this->redirectToList();
    }

    public function reject(string $id): void
    {
        Auth::requireAdmin();
        (new SubmissionRepository(Database::pdo()))->reject((int) $id, (int) Auth::id());
        flash('Submission rejected.');
        $this->redirectToList();
    }

    private function renderShow(array $sub, array $errors): void
    {
        $repo      = new SwitchRepository(Database::pdo());
        $audioRepo = new SubmissionAudioRepository(Database::pdo());
        view('admin/submissions/show', [
            'active'         => 'submissions',
            'submission'     => $sub,
            'designers'      => $repo->allDesigners(),
            'errors'         => $errors,
            'attachedAudio'  => $audioRepo->forSubmission((int) $sub['id']),
        ], 'admin/partials');
    }

    private function redirectToList(): void
    {
        header('Location: ' . url('/admin/submissions'));
        exit;
    }
}
