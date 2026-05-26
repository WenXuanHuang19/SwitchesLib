<?php

/**
 * Unit tests for SubmissionAudioRepository — the optional recording bundled
 * with a new-Switch submission (ADR-0007).
 */
class SubmissionAudioRepositoryTest extends TestCase
{
    private SubmissionAudioRepository $repo;
    private SubmissionRepository      $submissions;
    private int                       $userId;
    private int                       $submissionId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo        = new SubmissionAudioRepository($this->pdo);
        $this->submissions = new SubmissionRepository($this->pdo);

        $this->userId = (new UserRepository($this->pdo))
            ->create('submitter', 'sub@test.local', password_hash('x', PASSWORD_DEFAULT));

        $this->submissionId = $this->submissions->create($this->userId, [
            'name'        => 'Bundled Switch',
            'switch_type' => 'Linear',
        ]);
    }

    public function test_add_returns_a_new_row_id(): void
    {
        $id = $this->repo->add($this->submissionId, 'uploads/audio/clip.mp3', $this->userId);
        $this->assertGreaterThan(0, $id);
    }

    public function test_for_submission_returns_attached_recordings(): void
    {
        $this->repo->add($this->submissionId, 'uploads/audio/clip.mp3', $this->userId);

        $rows = $this->repo->forSubmission($this->submissionId);

        $this->assertCount(1, $rows);
        $this->assertSame('uploads/audio/clip.mp3', $rows[0]['audio_url']);
    }

    public function test_for_submission_is_empty_when_no_audio_attached(): void
    {
        $this->assertSame([], $this->repo->forSubmission($this->submissionId));
    }

    public function test_add_stores_keyboard_configuration(): void
    {
        $config = [
            'keyboard_name'   => 'Mode Sonnet',
            'keyboard_type'   => '75%',
            'case_material'   => 'Aluminum',
            'plate_material'  => 'POM',
            'mounting_style'  => 'Top mount',
            'pcb'             => 'Soldered',
            'foam_filling'    => 'No foam',
            'keycap_material' => 'PBT',
            'keycap_profile'  => 'MT3',
            'microphone'      => 'Rode NT1',
        ];

        $this->repo->add($this->submissionId, 'uploads/audio/clip.mp3', $this->userId, $config);

        $rows = $this->repo->forSubmission($this->submissionId);
        foreach ($config as $col => $val) {
            $this->assertSame($val, $rows[0][$col], "config field '$col' should round-trip");
        }
    }

    public function test_add_defaults_omitted_config_to_unknown(): void
    {
        $this->repo->add($this->submissionId, 'uploads/audio/clip.mp3', $this->userId, []);

        $rows = $this->repo->forSubmission($this->submissionId);
        foreach (SwitchAudioRepository::CONFIG_COLUMNS as $col) {
            $this->assertSame('Unknown', $rows[0][$col], "omitted '$col' should default to Unknown");
        }
    }
}
