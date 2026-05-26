<?php

/**
 * Unit tests for AudioUpload::validate().
 *
 * validate() is pure — it inspects a $_FILES-shaped array and returns an error
 * string or null. These tests cover the format and size guards from ADR-0007
 * (MP3 only, 5 MB ceiling) without touching the filesystem.
 */
class AudioUploadTest extends TestCase
{
    private function mp3(int $size = 1024): array
    {
        return ['name' => 'typing.mp3', 'error' => UPLOAD_ERR_OK, 'size' => $size];
    }

    public function test_accepts_an_mp3_within_the_size_limit(): void
    {
        $this->assertNull(AudioUpload::validate($this->mp3()));
    }

    public function test_accepts_uppercase_mp3_extension(): void
    {
        $this->assertNull(
            AudioUpload::validate(['name' => 'TYPING.MP3', 'error' => UPLOAD_ERR_OK, 'size' => 1024])
        );
    }

    public function test_treats_no_file_as_acceptable(): void
    {
        // Audio is optional at the uploader level; required-ness is enforced by callers.
        $this->assertNull(AudioUpload::validate(['name' => '', 'error' => UPLOAD_ERR_NO_FILE]));
    }

    public function test_rejects_non_mp3_extensions(): void
    {
        foreach (['sound.wav', 'sound.ogg', 'sound.m4a', 'clip.mp4', 'evil.php'] as $name) {
            $error = AudioUpload::validate(['name' => $name, 'error' => UPLOAD_ERR_OK, 'size' => 1024]);
            $this->assertNotNull($error, "Expected $name to be rejected");
        }
    }

    public function test_rejects_files_over_five_megabytes(): void
    {
        $tooBig = AudioUpload::MAX_BYTES + 1;
        $this->assertNotNull(AudioUpload::validate($this->mp3($tooBig)));
    }

    public function test_accepts_a_file_exactly_at_the_limit(): void
    {
        $this->assertNull(AudioUpload::validate($this->mp3(AudioUpload::MAX_BYTES)));
    }

    public function test_reports_an_error_on_failed_upload(): void
    {
        $this->assertNotNull(
            AudioUpload::validate(['name' => 'typing.mp3', 'error' => UPLOAD_ERR_CANT_WRITE, 'size' => 1024])
        );
    }
}
