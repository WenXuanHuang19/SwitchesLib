<?php

/**
 * Access to switch_audio — community-contributed typing recordings attached to
 * a Switch (ADR-0006, ADR-0007). A Switch may hold many recordings; Phase 2
 * displays only the most recent.
 */
class SwitchAudioRepository
{
    public function __construct(private PDO $pdo) {}

    /** Attach a recording to a switch. Returns the new row id. */
    public function add(int $switchId, string $audioUrl, ?int $uploadedBy): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO switch_audio (switch_id, audio_url, uploaded_by)
             VALUES (:switch_id, :audio_url, :uploaded_by)"
        );
        $stmt->execute([
            'switch_id'   => $switchId,
            'audio_url'   => $audioUrl,
            'uploaded_by' => $uploadedBy,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * The most recently added recording for a switch, with its uploader's
     * username for attribution, or null when the switch has no recordings.
     */
    public function latestForSwitch(int $switchId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, u.username AS uploader_name
             FROM switch_audio a
             LEFT JOIN users u ON u.id = a.uploaded_by
             WHERE a.switch_id = :switch_id
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT 1"
        );
        $stmt->execute(['switch_id' => $switchId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
