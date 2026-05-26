<?php

/**
 * Access to switch_audio — community-contributed typing recordings attached to
 * a Switch (ADR-0006, ADR-0007). A Switch may hold many recordings; Phase 2
 * displays only the most recent.
 */
class SwitchAudioRepository
{
    /**
     * Recording-environment columns (PRD v1.0 §20.1). switch_audio is where every
     * recording ultimately lands, so this is the canonical list the submission
     * tables mirror and copy from.
     */
    public const CONFIG_COLUMNS = [
        'keyboard_name', 'keyboard_type', 'case_material', 'plate_material',
        'mounting_style', 'pcb', 'foam_filling', 'keycap_material',
        'keycap_profile', 'microphone',
    ];

    public function __construct(private PDO $pdo) {}

    /**
     * Extract the recording-environment fields from a request payload (e.g.
     * $_POST), trimming each value and falling back to 'Unknown' when blank or
     * absent. Shared by every audio-upload entry point so the field handling
     * stays identical.
     */
    public static function configFromInput(array $input): array
    {
        $config = [];
        foreach (self::CONFIG_COLUMNS as $col) {
            $val          = trim($input[$col] ?? '');
            $config[$col] = $val === '' ? 'Unknown' : $val;
        }
        return $config;
    }

    /**
     * Attach a recording to a switch. $config holds the recording-environment
     * fields (missing keys default to 'Unknown'). Returns the new row id.
     */
    public function add(int $switchId, string $audioUrl, ?int $uploadedBy, array $config = []): int
    {
        $columns = ['switch_id', 'audio_url', 'uploaded_by'];
        $params  = ['switch_id' => $switchId, 'audio_url' => $audioUrl, 'uploaded_by' => $uploadedBy];

        foreach (self::CONFIG_COLUMNS as $col) {
            $columns[]    = $col;
            $params[$col] = $config[$col] ?? 'Unknown';
        }

        $placeholders = implode(', ', array_map(fn($c) => ":$c", $columns));
        $sql = 'INSERT INTO switch_audio (' . implode(', ', $columns) . ") VALUES ($placeholders)";

        $this->pdo->prepare($sql)->execute($params);

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
