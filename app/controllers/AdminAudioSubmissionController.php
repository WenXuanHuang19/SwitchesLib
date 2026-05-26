<?php

/**
 * Admin review queue for Audio Submissions — a flow separate from the switch
 * submission queue (ADR-0007). Approving copies the recording into switch_audio
 * so it appears on the target switch's detail page.
 */
class AdminAudioSubmissionController
{
    public function index(): void
    {
        Auth::requireAdmin();

        $repo = new AudioSubmissionRepository(Database::pdo());
        view('admin/audio-submissions/index', [
            'active'      => 'audio-submissions',
            'submissions' => $repo->pending(),
        ], 'admin/partials');
    }

    public function show(string $id): void
    {
        Auth::requireAdmin();

        $repo = new AudioSubmissionRepository(Database::pdo());
        $sub  = $repo->findById((int) $id);
        if ($sub === null) not_found();

        view('admin/audio-submissions/show', [
            'active'     => 'audio-submissions',
            'submission' => $sub,
        ], 'admin/partials');
    }

    public function approve(string $id): void
    {
        Auth::requireAdmin();
        (new AudioSubmissionRepository(Database::pdo()))->approve((int) $id, (int) Auth::id());
        flash('Recording approved and published.');
        $this->redirectToList();
    }

    public function reject(string $id): void
    {
        Auth::requireAdmin();
        (new AudioSubmissionRepository(Database::pdo()))->reject((int) $id, (int) Auth::id());
        flash('Recording rejected.');
        $this->redirectToList();
    }

    private function redirectToList(): void
    {
        header('Location: ' . url('/admin/audio-submissions'));
        exit;
    }
}
