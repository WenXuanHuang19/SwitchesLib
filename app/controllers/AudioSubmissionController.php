<?php

/**
 * User-side Audio Submission flow: submit a typing recording for an existing
 * Switch (ADR-0007). Admin review lives in AdminAudioSubmissionController
 * (later slice).
 */
class AudioSubmissionController
{
    private const AUDIO_DIR    = ROOT_PATH . '/public/uploads/audio';
    private const AUDIO_PREFIX = 'uploads/audio';

    /** Show the recording form for a given switch (logged-in users only). */
    public function show(string $slug): void
    {
        Auth::requireLogin();
        $switch = $this->requireSwitch($slug);

        view('submit/audio', ['switch' => $switch, 'error' => null, 'audioConfig' => []]);
    }

    /** Validate the uploaded recording and store it as a Pending submission. */
    public function store(string $slug): void
    {
        Auth::requireLogin();
        $switch = $this->requireSwitch($slug);

        $config = SwitchAudioRepository::configFromInput($_POST);
        $file   = $_FILES['audio'] ?? [];

        // Audio is the entire submission here, so it is required.
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            view('submit/audio', ['switch' => $switch, 'error' => 'Please choose an MP3 file to upload.', 'audioConfig' => $config]);
            return;
        }

        $audioError = AudioUpload::validate($file);
        if ($audioError !== null) {
            view('submit/audio', ['switch' => $switch, 'error' => $audioError, 'audioConfig' => $config]);
            return;
        }

        $url = AudioUpload::store($file, self::AUDIO_DIR, self::AUDIO_PREFIX);
        (new AudioSubmissionRepository(Database::pdo()))
            ->create((int) Auth::id(), (int) $switch['id'], $url, $config);

        flash('Recording submitted — pending review.');
        header('Location: ' . url('/my-submissions'));
        exit;
    }

    /** Fetch an approved switch by slug or render 404. */
    private function requireSwitch(string $slug): array
    {
        $switch = (new SwitchRepository(Database::pdo()))->findBySlug($slug);
        if ($switch === null || $switch['status'] !== 'approved') {
            not_found();
        }
        return $switch;
    }
}
