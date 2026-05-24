<?php

/**
 * All blog post data access. Receives a PDO so it can run against any database
 * (the app passes Database::pdo(); tests pass a test connection).
 */
class BlogRepository
{
    public function __construct(private PDO $pdo) {}

    /** All published posts for the public blog list. */
    public function allPublished(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC"
        );
        return $stmt->fetchAll();
    }

    /** Total number of posts (all statuses), for the admin dashboard. */
    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn();
    }

    /** All posts (any status), newest first, for the admin list. */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM blog_posts ORDER BY created_at DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    /** A single post by id, or null. */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM blog_posts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        return $post === false ? null : $post;
    }

    /**
     * Insert a post from column data. A unique slug is generated from the
     * title (any 'slug'/'id' in $data is ignored). Returns the new row id.
     */
    public function create(array $data): int
    {
        unset($data['id'], $data['slug']);
        $data['slug'] = $this->uniqueSlug($data['title']);

        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($data)));

        $stmt = $this->pdo->prepare("INSERT INTO blog_posts ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    /** Update the given columns of a post. 'id'/'slug' in $data are ignored. */
    public function update(int $id, array $data): void
    {
        unset($data['id'], $data['slug']);
        if ($data === []) return;
        $assignments = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $data['id']  = $id;
        $stmt = $this->pdo->prepare("UPDATE blog_posts SET {$assignments} WHERE id = :id");
        $stmt->execute($data);
    }

    /** Hard-delete a post. */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM blog_posts WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Slug::make($title);
        $slug = $base;
        $n    = 1;
        while ($this->slugExists($slug)) {
            $n++;
            $slug = $base . '-' . $n;
        }
        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM blog_posts WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetchColumn() !== false;
    }

    /** A single post by slug, or null if none matches. */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM blog_posts WHERE slug = :slug"
        );
        $stmt->execute(['slug' => $slug]);
        $post = $stmt->fetch();

        return $post === false ? null : $post;
    }
}
