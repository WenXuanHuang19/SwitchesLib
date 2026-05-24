<?php

/**
 * All Switch data access. Receives a PDO so it can run against any database
 * (the app passes Database::pdo(); tests pass a test connection).
 */
class SwitchRepository
{
    public function __construct(private PDO $pdo) {}

    /** Every switch (any status) with its designer name, newest-updated first. */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            "SELECT s.*, d.name AS designer_name
             FROM switches s
             LEFT JOIN designers d ON d.id = s.designer_id
             ORDER BY s.updated_at DESC, s.id DESC"
        );
        return $stmt->fetchAll();
    }

    /** All approved switches for public listing. */
    public function allApproved(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM switches WHERE status = 'approved' ORDER BY created_at DESC"
        );
        return $stmt->fetchAll();
    }

    /** A single switch by id (with its designer's name), or null. */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.*, d.name AS designer_name
             FROM switches s
             LEFT JOIN designers d ON d.id = s.designer_id
             WHERE s.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $switch = $stmt->fetch();

        return $switch === false ? null : $switch;
    }

    /**
     * Insert a switch from normalized column data. A unique slug is generated
     * from the name (ADR-0002); any 'slug'/'id' in $data is ignored. Returns
     * the new row id.
     */
    public function create(array $data): int
    {
        unset($data['id'], $data['slug']);
        $data['slug'] = $this->uniqueSlug($data['name']);

        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($data)));

        $stmt = $this->pdo->prepare("INSERT INTO switches ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    /** Update the given columns of a switch. 'id'/'slug' in $data are ignored. */
    public function update(int $id, array $data): void
    {
        unset($data['id'], $data['slug']);
        if ($data === []) {
            return;
        }

        $assignments = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $data['id']  = $id;

        $stmt = $this->pdo->prepare("UPDATE switches SET {$assignments} WHERE id = :id");
        $stmt->execute($data);
    }

    /** Hard-delete a switch (ADR: hard delete, no soft delete). */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM switches WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** A slug derived from $name, suffixed (-2, -3, …) until it is unique. */
    private function uniqueSlug(string $name): string
    {
        $base = Slug::make($name);
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
        $stmt = $this->pdo->prepare('SELECT 1 FROM switches WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetchColumn() !== false;
    }

    /** A single switch by slug (with its designer's name), or null. */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.*, d.name AS designer_name
             FROM switches s
             LEFT JOIN designers d ON d.id = s.designer_id
             WHERE s.slug = :slug"
        );
        $stmt->execute(['slug' => $slug]);
        $switch = $stmt->fetch();

        return $switch === false ? null : $switch;
    }

    /** Record one more view of a switch. */
    public function incrementViews(int $id): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE switches SET views_count = views_count + 1 WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }

    /** Total number of switches (all statuses), for the admin dashboard. */
    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM switches')->fetchColumn();
    }

    /** The $limit most recently added switches of any status, for the admin dashboard. */
    public function recent(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM switches ORDER BY created_at DESC, id DESC LIMIT ' . (int) $limit
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Whether a switch with the given name already exists under that designer. */
    public function existsByNameAndDesigner(string $name, ?int $designerId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM switches WHERE name = :name AND designer_id <=> :designer_id LIMIT 1"
        );
        $stmt->execute(['name' => $name, 'designer_id' => $designerId]);

        return $stmt->fetchColumn() !== false;
    }

    /** The $limit most recently added approved switches, for the home page. */
    public function latest(int $limit = 9): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM switches
             WHERE status = 'approved'
             ORDER BY created_at DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** All designers ordered alphabetically, for filter dropdowns. */
    public function allDesigners(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM designers ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    /**
     * Approved switches matching optional filter criteria, in the requested
     * sort order.  $filters keys: switch_type, sound_profile, feel_profile,
     * designer_id, factory_lubed, recommended_use.
     * $sort: 'newest' | 'most_viewed' | 'lightest' | 'heaviest'.
     */
    public function filtered(array $filters = [], string $sort = 'newest'): array
    {
        $where  = ["status = 'approved'"];
        $params = [];

        $allowed = ['switch_type', 'sound_profile', 'feel_profile',
                    'designer_id', 'factory_lubed', 'recommended_use'];

        foreach ($allowed as $col) {
            if (isset($filters[$col]) && $filters[$col] !== '') {
                $where[]        = "$col = :$col";
                $params[":$col"] = $filters[$col];
            }
        }

        $orderBy = match ($sort) {
            'most_viewed' => 'views_count DESC',
            'lightest'    => 'bottom_out_force IS NULL ASC, bottom_out_force ASC',
            'heaviest'    => 'bottom_out_force IS NULL ASC, bottom_out_force DESC',
            default       => 'created_at DESC',
        };

        $sql  = 'SELECT * FROM switches WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY ' . $orderBy;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Up to $limit approved switches similar to the given one: same Switch Type
     * and Sound Profile, ordered by closest bottom-out force, excluding itself.
     */
    public function similarTo(array $switch, int $limit = 3): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM switches
             WHERE status = 'approved'
               AND id != :id
               AND switch_type = :switch_type
               AND sound_profile = :sound_profile
             ORDER BY ABS(bottom_out_force - :force) ASC
             LIMIT " . (int) $limit
        );
        $stmt->execute([
            'id'            => $switch['id'],
            'switch_type'   => $switch['switch_type'],
            'sound_profile' => $switch['sound_profile'],
            'force'         => $switch['bottom_out_force'],
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Up to $limit other approved switches from the same designer, excluding
     * the current one. Returns [] when the switch has no designer.
     */
    public function byDesigner(?int $designerId, int $excludeId, int $limit = 6): array
    {
        if ($designerId === null) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            "SELECT * FROM switches
             WHERE status = 'approved'
               AND designer_id = :designer_id
               AND id != :exclude_id
             ORDER BY created_at DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute([
            'designer_id' => $designerId,
            'exclude_id'  => $excludeId,
        ]);

        return $stmt->fetchAll();
    }
}
