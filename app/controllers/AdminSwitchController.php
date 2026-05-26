<?php

class AdminSwitchController
{
    private const UPLOAD_DIR    = ROOT_PATH . '/public/uploads/switches';
    private const UPLOAD_PREFIX = 'uploads/switches';
    private const AUDIO_DIR     = ROOT_PATH . '/public/uploads/audio';
    private const AUDIO_PREFIX  = 'uploads/audio';

    public function index(): void
    {
        Auth::requireAdmin();
        $repo = new SwitchRepository(Database::pdo());
        view('admin/switches/index', [
            'active'   => 'switches',
            'switches' => $repo->all(),
        ], 'admin/partials');
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->renderForm([], [], null);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $repo   = new SwitchRepository(Database::pdo());
        $result = $this->validateInput();

        if (isset($result['errors'])) {
            $this->renderForm($result['errors'], $_POST, null);
            return;
        }

        $switchId = $repo->create($result['data']);
        $this->storeAudioIfPresent($switchId);
        flash('Switch created.');
        $this->redirectToList();
    }

    public function edit(string $id): void
    {
        Auth::requireAdmin();
        $repo   = new SwitchRepository(Database::pdo());
        $switch = $repo->findById((int) $id);

        if ($switch === null) {
            not_found();
        }

        $this->renderForm([], $switch, $switch);
    }

    public function update(string $id): void
    {
        Auth::requireAdmin();
        $repo   = new SwitchRepository(Database::pdo());
        $switch = $repo->findById((int) $id);

        if ($switch === null) {
            not_found();
        }

        $result = $this->validateInput();
        if (isset($result['errors'])) {
            $this->renderForm($result['errors'], $_POST + $switch, $switch);
            return;
        }

        $data = $result['data'];
        // No new image this time → keep the existing image_url untouched.
        if (!array_key_exists('image_url', $data)) {
            unset($data['image_url']);
        }

        $repo->update((int) $id, $data);
        $this->storeAudioIfPresent((int) $id);
        flash('Switch updated.');
        $this->redirectToList();
    }

    public function destroy(string $id): void
    {
        Auth::requireAdmin();
        (new SwitchRepository(Database::pdo()))->delete((int) $id);
        flash('Switch deleted.');
        $this->redirectToList();
    }

    /**
     * Normalize the posted fields (reusing the submission validator, since the
     * field set mirrors the switches table), pick a valid status, and validate
     * + store any uploaded image. Returns ['errors'=>..] or ['data'=>..].
     */
    private function validateInput(): array
    {
        $result = Submission::validate($_POST);
        if (isset($result['errors'])) {
            return $result;
        }

        $data           = $result['data'];
        $data['status'] = in_array($_POST['status'] ?? '', ['approved', 'draft'], true)
            ? $_POST['status']
            : 'approved';

        $file      = $_FILES['image'] ?? [];
        $imageError = ImageUpload::validate($file);
        if ($imageError !== null) {
            return ['errors' => ['image' => $imageError]];
        }

        $audioError = AudioUpload::validate($_FILES['audio'] ?? []);
        if ($audioError !== null) {
            return ['errors' => ['audio' => $audioError]];
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $data['image_url'] = ImageUpload::store($file, self::UPLOAD_DIR, self::UPLOAD_PREFIX);
        } else {
            // No upload: don't let the validator's null overwrite an existing image.
            unset($data['image_url']);
        }

        return ['data' => $data];
    }

    /**
     * If an audio file was uploaded, store it and attach a switch_audio row to
     * the switch. Audio lives in its own table (ADR-0007), not on the switches
     * row, so it is handled after the switch id is known.
     */
    private function storeAudioIfPresent(int $switchId): void
    {
        $file = $_FILES['audio'] ?? [];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return;
        }

        $url = AudioUpload::store($file, self::AUDIO_DIR, self::AUDIO_PREFIX);
        (new SwitchAudioRepository(Database::pdo()))->add($switchId, $url, Auth::id());
    }

    private function renderForm(array $errors, array $old, ?array $switch): void
    {
        $repo      = new SwitchRepository(Database::pdo());
        $recording = $switch === null
            ? null
            : (new SwitchAudioRepository(Database::pdo()))->latestForSwitch((int) $switch['id']);

        view('admin/switches/form', [
            'active'    => $switch === null ? 'add-switch' : 'switches',
            'designers' => $repo->allDesigners(),
            'errors'    => $errors,
            'old'       => $old,
            'switch'    => $switch,
            'recording' => $recording,
        ], 'admin/partials');
    }

    private function redirectToList(): void
    {
        header('Location: ' . url('/admin/switches'));
        exit;
    }
}
