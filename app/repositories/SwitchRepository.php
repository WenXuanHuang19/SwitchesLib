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
