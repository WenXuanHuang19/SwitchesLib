<?php

/**
 * Access to submission_audio — an optional recording bundled with a new-Switch
 * submission (ADR-0007). On approval the recordings are copied into switch_audio
 * for the newly created Switch.
 */
class SubmissionAudioRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Attach a recording to a pending submission. $config holds the
     * recording-environment fields (missing keys default to 'Unknown').
     * Returns the new row id.
     */
    public function add(int $submissionId, string $audioUrl, ?int $uploadedBy, array $config = []): int
    {
        $columns = ['submission_id', 'audio_url', 'uploaded_by'];
        $params  = [
            'submission_id' => $submissionId,
            'audio_url'     => $audioUrl,
            'uploaded_by'   => $uploadedBy,
        ];
        foreach (SwitchAudioRepository::CONFIG_COLUMNS as $col) {
            $columns[]    = $col;
            $params[$col] = $config[$col] ?? 'Unknown';
        }

        $placeholders = implode(', ', array_map(fn($c) => ":$c", $columns));
        $sql = 'INSERT INTO submission_audio (' . implode(', ', $columns) . ") VALUES ($placeholders)";
        $this->pdo->prepare($sql)->execute($params);

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
