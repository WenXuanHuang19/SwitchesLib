<?php

/**
 * Validates and stores uploaded typing-sound recordings. validate() is pure
 * (inspects the $_FILES entry only); store() performs the filesystem side
 * effect. Audio is community content, kept apart from official spec data
 * (ADR-0006) and stored in its own table (ADR-0007).
 */
class AudioUpload
{
    public const ALLOWED_EXTENSIONS = ['mp3'];
    public const MAX_BYTES          = 5 * 1024 * 1024;   // 5 MB

    /**
     * Returns an error message if the uploaded file is unacceptable, or null
     * if it is fine. A missing file is acceptable here — callers that require
     * audio (e.g. an Audio Submission) enforce presence themselves.
     */
    public static function validate(array $file): ?string
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            return 'Audio upload failed. Please try again.';
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return 'Audio must be an MP3 file.';
        }

        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            return 'Audio must be 5 MB or smaller.';
        }

        return null;
    }

    /**
     * Move an uploaded file into $destDir and return the path stored in
     * audio_url (relative to the public root, e.g. uploads/audio/abc.mp3).
     */
    public static function store(array $file, string $destDir, string $publicPrefix): string
    {
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(8)) . '.' . $ext;

        if (!is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }
        move_uploaded_file($file['tmp_name'], $destDir . '/' . $filename);

        return $publicPrefix . '/' . $filename;
    }
}
