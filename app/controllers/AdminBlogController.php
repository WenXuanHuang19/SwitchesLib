<?php

class AdminBlogController
{
    private const UPLOAD_DIR    = ROOT_PATH . '/public/uploads/blog';
    private const UPLOAD_PREFIX = 'uploads/blog';

    public function index(): void
    {
        Auth::requireAdmin();
        $repo = new BlogRepository(Database::pdo());
        view('admin/blog/index', [
            'active' => 'blog',
            'posts'  => $repo->all(),
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
        $result = $this->validateInput();

        if (isset($result['errors'])) {
            $this->renderForm($result['errors'], $_POST, null);
            return;
        }

        (new BlogRepository(Database::pdo()))->create($result['data']);
        flash('Blog post created.');
        $this->redirectToList();
    }

    public function edit(string $id): void
    {
        Auth::requireAdmin();
        $repo = new BlogRepository(Database::pdo());
        $post = $repo->findById((int) $id);
        if ($post === null) not_found();

        $this->renderForm([], $post, $post);
    }

    public function update(string $id): void
    {
        Auth::requireAdmin();
        $repo = new BlogRepository(Database::pdo());
        $post = $repo->findById((int) $id);
        if ($post === null) not_found();

        $result = $this->validateInput();
        if (isset($result['errors'])) {
            $this->renderForm($result['errors'], $_POST + $post, $post);
            return;
        }

        if (!array_key_exists('cover_image_url', $result['data'])) {
            unset($result['data']['cover_image_url']);
        }

        $repo->update((int) $id, $result['data']);
        flash('Blog post updated.');
        $this->redirectToList();
    }

    public function destroy(string $id): void
    {
        Auth::requireAdmin();
        (new BlogRepository(Database::pdo()))->delete((int) $id);
        flash('Blog post deleted.');
        $this->redirectToList();
    }

    private function validateInput(): array
    {
        $errors = [];
        $title  = trim($_POST['title'] ?? '');
        if ($title === '') {
            $errors['title'] = 'Title is required.';
        }

        $status = in_array($_POST['status'] ?? '', ['draft', 'published'], true)
            ? $_POST['status']
            : 'draft';

        $data = [
            'title'      => $title,
            'category'   => trim($_POST['category'] ?? ''),
            'tags'       => trim($_POST['tags'] ?? ''),
            'excerpt'    => trim($_POST['excerpt'] ?? ''),
            'content'    => $_POST['content'] ?? '',
            'status'     => $status,
        ];

        // Cover image upload (optional).
        $file       = $_FILES['cover_image'] ?? [];
        $imageError = ImageUpload::validate($file);
        if ($imageError !== null) {
            $errors['cover_image'] = $imageError;
        } elseif (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $data['cover_image_url'] = ImageUpload::store($file, self::UPLOAD_DIR, self::UPLOAD_PREFIX);
        }

        // Auto-set published_at when first published.
        if ($status === 'published') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        return $errors !== [] ? ['errors' => $errors] : ['data' => $data];
    }

    private function renderForm(array $errors, array $old, ?array $post): void
    {
        view('admin/blog/form', [
            'active' => $post === null ? 'blog' : 'blog',
            'errors' => $errors,
            'old'    => $old,
            'post'   => $post,
        ], 'admin/partials');
    }

    private function redirectToList(): void
    {
        header('Location: ' . url('/admin/blog'));
        exit;
    }
}
