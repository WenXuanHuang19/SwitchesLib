<?php

/**
 * Data access for Audio Submissions — User-proposed recordings for an existing
 * Switch, awaiting Admin review (ADR-0007). This slice covers the user side
 * (create + list own); the admin review actions arrive in a later slice.
 */
class AudioSubmissionRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Store a new Audio Submission for the given user against an existing
     * switch. Status is always 'Pending'; returns the new row id.
     */
    public function create(int $userId, int $switchId, string $audioUrl): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO audio_submissions (user_id, switch_id, audio_url, status)
             VALUES (:user_id, :switch_id, :audio_url, 'Pending')"
        );
        $stmt->execute([
            'user_id'   => $userId,
            'switch_id' => $switchId,
            'audio_url' => $audioUrl,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * All Audio Submissions for a user, newest first, with the target switch's
     * name and slug for display.
     */
    public function forUser(int $userId): array
    {
        return $this->baseQuery('WHERE a.user_id = :user_id', ['user_id' => $userId]);
    }

    /** Pending Audio Submissions for the admin review queue, newest first. */
    public function pending(): array
    {
        return $this->baseQuery("WHERE a.status = 'Pending'");
    }

    /** A single Audio Submission with switch + submitter details, or null. */
    public function findById(int $id): ?array
    {
        $rows = $this->baseQuery('WHERE a.id = :id', ['id' => $id]);
        return $rows === [] ? null : $rows[0];
    }

    private function baseQuery(string $where, array $params = []): array
    {
        $sql = "SELECT a.*, s.name AS switch_name, s.slug AS switch_slug,
                       u.username AS submitter_username
                FROM audio_submissions a
                LEFT JOIN switches s ON s.id = a.switch_id
                LEFT JOIN users u    ON u.id = a.user_id
                {$where}
                ORDER BY a.created_at DESC, a.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Approve an Audio Submission: copy the recording into switch_audio
     * (crediting the original submitter), then mark the submission Approved.
     * Returns the new switch_audio row id.
     */
    public function approve(int $id, int $adminId): int
    {
        $sub = $this->findById($id);
        if ($sub === null) {
            throw new RuntimeException('Audio submission not found.');
        }

        $audioId = (new SwitchAudioRepository($this->pdo))->add(
            (int) $sub['switch_id'],
            $sub['audio_url'],
            (int) $sub['user_id']
        );

        $this->pdo->prepare(
            "UPDATE audio_submissions
             SET status='Approved', reviewed_by=:rb, reviewed_at=NOW()
             WHERE id=:id"
        )->execute(['rb' => $adminId, 'id' => $id]);

        return $audioId;
    }

    /** Reject an Audio Submission without copying it into switch_audio. */
    public function reject(int $id, int $adminId): void
    {
        $this->pdo->prepare(
            "UPDATE audio_submissions
             SET status='Rejected', reviewed_by=:rb, reviewed_at=NOW()
             WHERE id=:id"
        )->execute(['rb' => $adminId, 'id' => $id]);
    }
}
