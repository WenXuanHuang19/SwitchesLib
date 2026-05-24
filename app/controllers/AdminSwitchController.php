<?php

class AdminSwitchController
{
    private const UPLOAD_DIR    = ROOT_PATH . '/public/uploads/switches';
    private const UPLOAD_PREFIX = 'uploads/switches';

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

        $repo->create($result['data']);
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

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $data['image_url'] = ImageUpload::store($file, self::UPLOAD_DIR, self::UPLOAD_PREFIX);
        } else {
            // No upload: don't let the validator's null overwrite an existing image.
            unset($data['image_url']);
        }

        return ['data' => $data];
    }

    private function renderForm(array $errors, array $old, ?array $switch): void
    {
        $repo = new SwitchRepository(Database::pdo());
        view('admin/switches/form', [
            'active'    => $switch === null ? 'add-switch' : 'switches',
            'designers' => $repo->allDesigners(),
            'errors'    => $errors,
            'old'       => $old,
            'switch'    => $switch,
        ], 'admin/partials');
    }

    private function redirectToList(): void
    {
        header('Location: ' . url('/admin/switches'));
        exit;
    }
}
