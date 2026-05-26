<?php

/**
 * Data access for user-submitted switches awaiting review. Receives a PDO so it
 * can run against any database (the app passes Database::pdo(); tests pass a
 * test connection).
 */
class SubmissionRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Store a new submission for the given user. $data holds the normalized
     * switch columns (from Submission::validate). Status is always 'Pending';
     * returns the new row id.
     */
    public function create(int $userId, array $data): int
    {
        $row = array_merge($data, [
            'user_id' => $userId,
            'status'  => 'Pending',
        ]);

        $columns      = implode(', ', array_keys($row));
        $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($row)));

        $stmt = $this->pdo->prepare("INSERT INTO submissions ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($row);

        return (int) $this->pdo->lastInsertId();
    }

    /** All submissions with designer + submitter name, newest first. */
    public function all(): array
    {
        return $this->baseQuery('');
    }

    /** Submissions filtered by a single status. */
    public function filtered(string $status): array
    {
        return $this->baseQuery('WHERE s.status = :status', ['status' => $status]);
    }

    private function baseQuery(string $where, array $params = []): array
    {
        $sql = "SELECT s.*, d.name AS designer_name, u.username AS submitter_username
                FROM submissions s
                LEFT JOIN designers d ON d.id = s.designer_id
                LEFT JOIN users u ON u.id = s.user_id
                {$where}
                ORDER BY s.created_at DESC, s.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** A single submission by id, with designer and submitter name. */
    public function findById(int $id): ?array
    {
        $rows = $this->baseQuery('WHERE s.id = :id', ['id' => $id]);
        return $rows === [] ? null : $rows[0];
    }

    /** Update editable fields on a pending submission before approving. */
    public function update(int $id, array $data): void
    {
        unset($data['id'], $data['user_id'], $data['status'],
              $data['reviewed_by'], $data['reviewed_at']);
        if ($data === []) return;

        $assignments = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $data['id']  = $id;
        $stmt = $this->pdo->prepare("UPDATE submissions SET {$assignments} WHERE id = :id");
        $stmt->execute($data);
    }

    /**
     * Approve a submission: copy switch fields into switches, mark submission
     * as Approved. Returns the new switch row id.
     */
    public function approve(int $submissionId, int $adminId): int
    {
        $sub = $this->findById($submissionId);
        if ($sub === null) throw new RuntimeException("Submission not found.");

        $exclude = ['id', 'user_id', 'designer_name', 'submitter_username',
                    'status', 'reviewed_by', 'reviewed_at', 'created_at', 'updated_at'];

        $data = [];
        foreach ($sub as $col => $val) {
            if (!in_array($col, $exclude, true)) {
                $data[$col] = $val;
            }
        }
        $data['submitted_by'] = (int) $sub['user_id'];
        $data['approved_by']  = $adminId;
        $data['status']       = 'approved';

        $switchId = (new SwitchRepository($this->pdo))->create($data);

        // Carry any bundled recordings onto the new switch, crediting the submitter.
        $bundled     = (new SubmissionAudioRepository($this->pdo))->forSubmission($submissionId);
        $switchAudio = new SwitchAudioRepository($this->pdo);
        foreach ($bundled as $rec) {
            $config = [];
            foreach (SwitchAudioRepository::CONFIG_COLUMNS as $col) {
                $config[$col] = $rec[$col] ?? 'Unknown';
            }
            $switchAudio->add($switchId, $rec['audio_url'], (int) $sub['user_id'], $config);
        }

        $this->pdo->prepare(
            "UPDATE submissions SET status='Approved', reviewed_by=:rb, reviewed_at=NOW() WHERE id=:id"
        )->execute(['rb' => $adminId, 'id' => $submissionId]);

        return $switchId;
    }

    /** Reject a submission without creating a switch record. */
    public function reject(int $submissionId, int $adminId): void
    {
        $this->pdo->prepare(
            "UPDATE submissions SET status='Rejected', reviewed_by=:rb, reviewed_at=NOW() WHERE id=:id"
        )->execute(['rb' => $adminId, 'id' => $submissionId]);
    }

    /** Number of submissions still awaiting review, for the admin dashboard. */
    public function countPending(): int
    {
        return (int) $this->pdo
            ->query("SELECT COUNT(*) FROM submissions WHERE status = 'Pending'")
            ->fetchColumn();
    }

    /** All submissions for a user, newest first, with the designer's name. */
    public function forUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.*, d.name AS designer_name
             FROM submissions s
             LEFT JOIN designers d ON d.id = s.designer_id
             WHERE s.user_id = :user_id
             ORDER BY s.created_at DESC, s.id DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
