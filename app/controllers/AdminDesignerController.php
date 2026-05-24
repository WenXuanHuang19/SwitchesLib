<?php

class AdminDesignerController
{
    public function index(): void
    {
        Auth::requireAdmin();
        $repo = new DesignerRepository(Database::pdo());
        view('admin/designers/index', [
            'active'    => 'designers',
            'designers' => $repo->all(),
        ], 'admin/partials');
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->renderForm([], []);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $data   = $this->formData();
        $errors = $this->validate($data);

        if ($errors !== []) {
            $this->renderForm($errors, $data);
            return;
        }

        (new DesignerRepository(Database::pdo()))->create($data);
        flash('Designer created.');
        $this->redirectToList();
    }

    public function edit(string $id): void
    {
        Auth::requireAdmin();
        $repo     = new DesignerRepository(Database::pdo());
        $designer = $repo->findById((int) $id);
        if ($designer === null) not_found();

        $this->renderForm([], $designer, $designer);
    }

    public function update(string $id): void
    {
        Auth::requireAdmin();
        $repo     = new DesignerRepository(Database::pdo());
        $designer = $repo->findById((int) $id);
        if ($designer === null) not_found();

        $data   = $this->formData();
        $errors = $this->validate($data);

        if ($errors !== []) {
            $this->renderForm($errors, $data, $designer);
            return;
        }

        $repo->update((int) $id, $data);
        flash('Designer updated.');
        $this->redirectToList();
    }

    public function destroy(string $id): void
    {
        Auth::requireAdmin();
        $repo = new DesignerRepository(Database::pdo());
        $id   = (int) $id;

        if ($repo->hasSwitches($id)) {
            flash('Cannot delete: switches are still linked to this Designer or Studio.');
        } else {
            $repo->delete($id);
            flash('Designer deleted.');
        }

        $this->redirectToList();
    }

    private function formData(): array
    {
        return [
            'name'    => trim($_POST['name'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = 'Name is required.';
        }
        return $errors;
    }

    private function renderForm(array $errors, array $old, ?array $designer = null): void
    {
        $repo = new DesignerRepository(Database::pdo());
        view('admin/designers/form', [
            'active'    => 'designers',
            'errors'    => $errors,
            'old'       => $old,
            'designer'  => $designer,
        ], 'admin/partials');
    }

    private function redirectToList(): void
    {
        header('Location: ' . url('/admin/designers'));
        exit;
    }
}
