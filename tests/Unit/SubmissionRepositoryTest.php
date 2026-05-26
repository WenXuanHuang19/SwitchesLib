<?php

/**
 * Unit tests for SubmissionRepository::approve().
 *
 * approve() is the most complex write path in the app: it copies a submission
 * into the switches table and updates the submission status in one operation.
 * All three concerns are verified independently.
 */
class SubmissionRepositoryTest extends TestCase
{
    private SubmissionRepository $submissions;
    private SwitchRepository     $switches;
    private int                  $userId;
    private int                  $adminId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->submissions = new SubmissionRepository($this->pdo);
        $this->switches    = new SwitchRepository($this->pdo);

        // Create a regular user (the submitter).
        $users = new UserRepository($this->pdo);
        $this->userId  = $users->create('submitter', 'sub@test.local', password_hash('x', PASSWORD_DEFAULT));
        $this->adminId = $users->create('reviewer',  'rev@test.local', password_hash('x', PASSWORD_DEFAULT), 'admin');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Minimal valid submission data (mirrors what Submission::validate returns).
     */
    private function submissionData(array $overrides = []): array
    {
        return array_merge([
            'name'             => 'Test Switch',
            'switch_type'      => 'Linear',
            'switch_category'  => 'Mechanical MX',
            'sound_profile'    => 'Unknown',
            'feel_profile'     => 'Unknown',
            'recommended_use'  => 'Unknown',
            'led_diffuser'     => 'Unknown',
            'rgb_support'      => 'Unknown',
            'factory_lubed'    => 'Unknown',
            'is_silent'        => 'Unknown',
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // approve() tests
    // -----------------------------------------------------------------------

    public function test_approve_sets_submission_status_to_approved(): void
    {
        $subId = $this->submissions->create($this->userId, $this->submissionData());
        $this->submissions->approve($subId, $this->adminId);

        $sub = $this->submissions->findById($subId);
        $this->assertSame('Approved', $sub['status']);
    }

    public function test_approve_records_reviewed_by_and_reviewed_at(): void
    {
        $subId = $this->submissions->create($this->userId, $this->submissionData());
        $this->submissions->approve($subId, $this->adminId);

        $sub = $this->submissions->findById($subId);
        $this->assertSame((string) $this->adminId, (string) $sub['reviewed_by']);
        $this->assertNotNull($sub['reviewed_at']);
    }

    public function test_approve_creates_switch_row_with_matching_fields(): void
    {
        $data  = $this->submissionData(['name' => 'Halo True', 'switch_type' => 'Tactile']);
        $subId = $this->submissions->create($this->userId, $data);

        $switchId = $this->submissions->approve($subId, $this->adminId);

        $switch = $this->switches->findById($switchId);
        $this->assertNotNull($switch);
        $this->assertSame('Halo True', $switch['name']);
        $this->assertSame('Tactile',   $switch['switch_type']);
        $this->assertSame('approved',  $switch['status']);
        // The new switch must record who submitted and who approved it.
        $this->assertSame((string) $this->userId,  (string) $switch['submitted_by']);
        $this->assertSame((string) $this->adminId, (string) $switch['approved_by']);
    }

    public function test_approve_copies_bundled_audio_onto_the_new_switch(): void
    {
        $subId = $this->submissions->create($this->userId, $this->submissionData());
        (new SubmissionAudioRepository($this->pdo))
            ->add($subId, 'uploads/audio/bundled.mp3', $this->userId);

        $switchId = $this->submissions->approve($subId, $this->adminId);

        // The bundled recording is now published on the new switch, credited to the submitter.
        $latest = (new SwitchAudioRepository($this->pdo))->latestForSwitch($switchId);
        $this->assertNotNull($latest);
        $this->assertSame('uploads/audio/bundled.mp3', $latest['audio_url']);
        $this->assertSame((string) $this->userId, (string) $latest['uploaded_by']);
    }

    public function test_approve_without_bundled_audio_leaves_switch_audio_empty(): void
    {
        $subId    = $this->submissions->create($this->userId, $this->submissionData());
        $switchId = $this->submissions->approve($subId, $this->adminId);

        $this->assertNull((new SwitchAudioRepository($this->pdo))->latestForSwitch($switchId));
    }
}
