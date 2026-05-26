<?php

/**
 * Unit tests for SwitchAudioRepository.
 *
 * Covers attaching recordings to a Switch and fetching the most recent one
 * with uploader attribution (the Phase 2 display rule from ADR-0007).
 */
class SwitchAudioRepositoryTest extends TestCase
{
    private SwitchAudioRepository $audio;
    private SwitchRepository      $switches;
    private int                   $switchId;
    private int                   $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->audio    = new SwitchAudioRepository($this->pdo);
        $this->switches = new SwitchRepository($this->pdo);

        $this->switchId = $this->switches->create([
            'name'        => 'Audio Host Switch',
            'switch_type' => 'Linear',
        ]);

        $users = new UserRepository($this->pdo);
        $this->userId = $users->create('recorder', 'rec@test.local', password_hash('x', PASSWORD_DEFAULT));
    }

    public function test_add_inserts_a_row_and_returns_its_id(): void
    {
        $id = $this->audio->add($this->switchId, 'uploads/audio/clip.mp3', $this->userId);
        $this->assertGreaterThan(0, $id);
    }

    public function test_latest_returns_null_when_switch_has_no_recording(): void
    {
        $this->assertNull($this->audio->latestForSwitch($this->switchId));
    }

    public function test_latest_returns_the_recording_with_uploader_name(): void
    {
        $this->audio->add($this->switchId, 'uploads/audio/clip.mp3', $this->userId);

        $latest = $this->audio->latestForSwitch($this->switchId);

        $this->assertNotNull($latest);
        $this->assertSame('uploads/audio/clip.mp3', $latest['audio_url']);
        $this->assertSame('recorder', $latest['uploader_name']);
    }

    public function test_latest_returns_the_most_recently_added_recording(): void
    {
        $this->audio->add($this->switchId, 'uploads/audio/old.mp3', $this->userId);
        $this->audio->add($this->switchId, 'uploads/audio/new.mp3', $this->userId);

        // Tie on created_at (same second) is broken by id DESC → newest row wins.
        $latest = $this->audio->latestForSwitch($this->switchId);
        $this->assertSame('uploads/audio/new.mp3', $latest['audio_url']);
    }

    public function test_latest_tolerates_a_null_uploader(): void
    {
        // uploaded_by may be NULL (uploader account later deleted, ON DELETE SET NULL).
        $this->audio->add($this->switchId, 'uploads/audio/orphan.mp3', null);

        $latest = $this->audio->latestForSwitch($this->switchId);
        $this->assertSame('uploads/audio/orphan.mp3', $latest['audio_url']);
        $this->assertNull($latest['uploader_name']);
    }
}
