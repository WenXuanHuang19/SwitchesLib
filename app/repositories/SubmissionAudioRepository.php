<?php

/**
 * Access to submission_audio — an optional recording bundled with a new-Switch
 * submission (ADR-0007). On approval the recordings are copied into switch_audio
 * for the newly created Switch.
 */
class SubmissionAudioRepository
{
    public function __construct(private PDO $pdo) {}

    /** Attach a recording to a pending submission. Returns the new row id. */
    public function add(int $submissionId, string $audioUrl, ?int $uploadedBy): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO submission_audio (submission_id, audio_url, uploaded_by)
             VALUES (:submission_id, :audio_url, :uploaded_by)"
        );
        $stmt->execute([
            'submission_id' => $submissionId,
            'audio_url'     => $audioUrl,
            'uploaded_by'   => $uploadedBy,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** All recordings attached to a submission, oldest first. */
    public function forSubmission(int $submissionId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM submission_audio
             WHERE submission_id = :submission_id
             ORDER BY id ASC"
        );
        $stmt->execute(['submission_id' => $submissionId]);

        return $stmt->fetchAll();
    }
}
