<?php

class SubmitController
{
    /** Show the submission form (logged-in users only). */
    public function show(): void
    {
        Auth::requireLogin();

        $switchRepo = new SwitchRepository(Database::pdo());
        view('submit/form', [
            'designers' => $switchRepo->allDesigners(),
            'errors'    => [],
            'old'       => [],
        ]);
    }

    /** Validate and store a submission, then redirect to My Submissions. */
    public function store(): void
    {
        Auth::requireLogin();

        $switchRepo = new SwitchRepository(Database::pdo());
        $result     = Submission::validate($_POST);

        if (isset($result['errors'])) {
            view('submit/form', [
                'designers' => $switchRepo->allDesigners(),
                'errors'    => $result['errors'],
                'old'       => $_POST,
            ]);
            return;
        }

        $data = $result['data'];

        // Block duplicates: same name under the same designer already exists.
        if ($switchRepo->existsByNameAndDesigner($data['name'], $data['designer_id'])) {
            view('submit/form', [
                'designers' => $switchRepo->allDesigners(),
                'errors'    => ['name' => 'A switch with this name already exists for that Designer or Studio.'],
                'old'       => $_POST,
            ]);
            return;
        }

        (new SubmissionRepository(Database::pdo()))->create(Auth::id(), $data);

        header('Location: ' . url('/my-submissions'));
        exit;
    }

    /** List the current user's submissions. */
    public function mySubmissions(): void
    {
        Auth::requireLogin();

        $repo = new SubmissionRepository(Database::pdo());
        view('submit/my', ['submissions' => $repo->forUser(Auth::id())]);
    }
}
