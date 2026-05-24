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
