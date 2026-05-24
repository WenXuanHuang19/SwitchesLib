<?php

/**
 * All Switch data access. Receives a PDO so it can run against any database
 * (the app passes Database::pdo(); tests pass a test connection).
 */
class SwitchRepository
{
    public function __construct(private PDO $pdo) {}

    /** All approved switches for public listing. */
    public function allApproved(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM switches WHERE status = 'approved' ORDER BY created_at DESC"
        );
        return $stmt->fetchAll();
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
