<?php

/**
 * Unit tests for AudioSubmissionRepository (user-side: create + list own).
 *
 * An Audio Submission targets an existing Switch and starts life Pending
 * (ADR-0007). Admin review actions are covered by a later slice.
 */
class AudioSubmissionRepositoryTest extends TestCase
{
    private AudioSubmissionRepository $repo;
    private int                       $userId;
    private int                       $adminId;
    private int                       $switchId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new AudioSubmissionRepository($this->pdo);

        $users = new UserRepository($this->pdo);
        $this->userId  = $users->create('recorder', 'rec@test.local', password_hash('x', PASSWORD_DEFAULT));
        $this->adminId = $users->create('reviewer', 'rev@test.local', password_hash('x', PASSWORD_DEFAULT), 'admin');

        $this->switchId = (new SwitchRepository($this->pdo))->create([
            'name'        => 'Target Switch',
            'switch_type' => 'Linear',
        ]);
    }

    public function test_create_returns_a_new_row_id(): void
    {
        $id = $this->repo->create($this->userId, $this->switchId, 'uploads/audio/clip.mp3');
        $this->assertGreaterThan(0, $id);
    }

    public function test_create_defaults_status_to_pending(): void
    {
        $id  = $this->repo->create($this->userId, $this->switchId, 'uploads/audio/clip.mp3');
        $row = $this->pdo->query("SELECT status FROM audio_submissions WHERE id = $id")->fetch();
        $this->assertSame('Pending', $row['status']);
    }

    public function test_for_user_lists_their_submissions_with_switch_name_and_slug(): void
    {
        $this->repo->create($this->userId, $this->switchId, 'uploads/audio/clip.mp3');

        $rows = $this->repo->forUser($this->userId);

        $this->assertCount(1, $rows);
        $this->assertSame('Target Switch', $rows[0]['switch_name']);
        $this->assertNotEmpty($rows[0]['switch_slug']);
        $this->assertSame('uploads/audio/clip.mp3', $rows[0]['audio_url']);
    }

    public function test_for_user_only_returns_that_users_submissions(): void
    {
        $other = (new UserRepository($this->pdo))
            ->create('other', 'other@test.local', password_hash('x', PASSWORD_DEFAULT));

        $this->repo->create($this->userId, $this->switchId, 'uploads/audio/mine.mp3');
        $this->repo->create($other,        $this->switchId, 'uploads/audio/theirs.mp3');

        $rows = $this->repo->forUser($this->userId);

        $this->assertCount(1, $rows);
        $this->assertSame('uploads/audio/mine.mp3', $rows[0]['audio_url']);
    }

    public function test_for_user_returns_newest_first(): void
    {
        $this->repo->create($this->userId, $this->switchId, 'uploads/audio/first.mp3');
        $this->repo->create($this->userId, $this->switchId, 'uploads/audio/second.mp3');

        $rows = $this->repo->forUser($this->userId);

        // Tie on created_at (same second) broken by id DESC → newest first.
        $this->assertSame('uploads/audio/second.mp3', $rows[0]['audio_url']);
    }

    // -----------------------------------------------------------------------
    // Admin review: pending / findById / approve / reject
    // -----------------------------------------------------------------------

    public function test_pending_lists_only_pending_submissions(): void
    {
        $keep     = $this->repo->create($this->userId, $this->switchId, 'uploads/audio/keep.mp3');
        $resolved = $this->repo->create($this->userId, $this->switchId, 'uploads/audio/done.mp3');
        $this->repo->approve($resolved, $this->adminId);

        $pending = $this->repo->pending();

        $this->assertCount(1, $pending);
        $this->assertSame($keep, (int) $pending[0]['id']);
    }

    public function test_find_by_id_includes_switch_and_submitter(): void
    {
        $id  = $this->repo->create($this->userId, $this->switchId, 'uploads/audio/clip.mp3');
        $sub = $this->repo->findById($id);

        $this->assertSame('Target Switch', $sub['switch_name']);
        $this->assertNotEmpty($sub['switch_slug']);
        $this->assertSame('recorder', $sub['submitter_username']);
    }

    public function test_approve_copies_recording_into_switch_audio_crediting_submitter(): void
    {
        $id = $this->repo->create($this->userId, $this->switchId, 'uploads/audio/clip.mp3');

        $this->repo->approve($id, $this->adminId);

        // The recording now lives on the switch, credited to the original submitter.
        $latest = (new SwitchAudioRepository($this->pdo))->latestForSwitch($this->switchId);
        $this->assertNotNull($latest);
        $this->assertSame('uploads/audio/clip.mp3', $latest['audio_url']);
        $this->assertSame((string) $this->userId, (string) $latest['uploaded_by']);
    }

    public function test_approve_marks_submission_approved_with_reviewer(): void
    {
        $id = $this->repo->create($this->userId, $this->switchId, 'uploads/audio/clip.mp3');
        $this->repo->approve($id, $this->adminId);

        $sub = $this->repo->findById($id);
        $this->assertSame('Approved', $sub['status']);
        $this->assertSame((string) $this->adminId, (string) $sub['reviewed_by']);
        $this->assertNotNull($sub['reviewed_at']);
    }

    public function test_reject_marks_rejected_without_writing_switch_audio(): void
    {
        $id = $this->repo->create($this->userId, $this->switchId, 'uploads/audio/clip.mp3');

        $this->repo->reject($id, $this->adminId);

        $sub = $this->repo->findById($id);
        $this->assertSame('Rejected', $sub['status']);
        $this->assertSame((string) $this->adminId, (string) $sub['reviewed_by']);

        // No recording should have been published.
        $this->assertNull((new SwitchAudioRepository($this->pdo))->latestForSwitch($this->switchId));
    }

    // -----------------------------------------------------------------------
    // Keyboard configuration metadata (PRD v1.0 §20.1)
    // -----------------------------------------------------------------------

    /** A full set of recording-environment values, for reuse across tests. */
    private function fullConfig(): array
    {
        return [
            'keyboard_name'   => 'Keychron Q1',
            'keyboard_type'   => '75%',
            'case_material'   => 'Aluminum',
            'plate_material'  => 'Brass',
            'mounting_style'  => 'Gasket',
            'pcb'             => 'Hotswap',
            'foam_filling'    => 'Case foam + PE foam',
            'keycap_material' => 'PBT',
            'keycap_profile'  => 'Cherry',
            'microphone'      => 'Shure SM7B',
        ];
    }

    public function test_create_stores_keyboard_configuration(): void
    {
        $config = $this->fullConfig();
        $id     = $this->repo->create($this->userId, $this->switchId, 'uploads/audio/clip.mp3', $config);

        $sub = $this->repo->findById($id);
        foreach ($config as $col => $val) {
            $this->assertSame($val, $sub[$col], "config field '$col' should round-trip");
        }
    }

    public function test_create_defaults_omitted_config_to_unknown(): void
    {
        // Submitter leaves the recording environment blank → every field is 'Unknown',
        // matching the project's Unknown convention (not blank).
        $id  = $this->repo->create($this->userId, $this->switchId, 'uploads/audio/clip.mp3', []);
        $sub = $this->repo->findById($id);

        foreach (SwitchAudioRepository::CONFIG_COLUMNS as $col) {
            $this->assertSame('Unknown', $sub[$col], "omitted '$col' should default to Unknown");
        }
    }

    public function test_approve_copies_keyboard_configuration_into_switch_audio(): void
    {
        $config = $this->fullConfig();
        $id     = $this->repo->create($this->userId, $this->switchId, 'uploads/audio/clip.mp3', $config);

        $this->repo->approve($id, $this->adminId);

        // The published recording must carry the same recording-environment data.
        $latest = (new SwitchAudioRepository($this->pdo))->latestForSwitch($this->switchId);
        foreach ($config as $col => $val) {
            $this->assertSame($val, $latest[$col], "approve should carry '$col' into switch_audio");
        }
    }
}
