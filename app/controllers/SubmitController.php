<?php

class SubmitController
{
    private const AUDIO_DIR    = ROOT_PATH . '/public/uploads/audio';
    private const AUDIO_PREFIX = 'uploads/audio';

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

        // An attached recording is optional, but if present it must be valid.
        $audioFile  = $_FILES['audio'] ?? [];
        $audioError = AudioUpload::validate($audioFile);
        if ($audioError !== null) {
            view('submit/form', [
                'designers' => $switchRepo->allDesigners(),
                'errors'    => ['audio' => $audioError],
                'old'       => $_POST,
            ]);
            return;
        }

        $submissionId = (new SubmissionRepository(Database::pdo()))->create((int) Auth::id(), $data);

        if (($audioFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $url = AudioUpload::store($audioFile, self::AUDIO_DIR, self::AUDIO_PREFIX);
            (new SubmissionAudioRepository(Database::pdo()))
                ->add($submissionId, $url, (int) Auth::id());
        }

        header('Location: ' . url('/my-submissions'));
        exit;
    }

    /** List the current user's submissions. */
    public function mySubmissions(): void
    {
        Auth::requireLogin();

        $repo      = new SubmissionRepository(Database::pdo());
        $audioRepo = new AudioSubmissionRepository(Database::pdo());
        view('submit/my', [
            'submissions'      => $repo->forUser(Auth::id()),
            'audioSubmissions' => $audioRepo->forUser((int) Auth::id()),
        ]);
    }
}
