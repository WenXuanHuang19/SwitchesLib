<?php

/**
 * Validates and stores uploaded switch images. validate() is pure (inspects
 * the $_FILES entry only); store() performs the filesystem side effect.
 */
class ImageUpload
{
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Returns an error message if the uploaded file is unacceptable, or null
     * if it is fine. A missing file is acceptable — the image is optional.
     */
    public static function validate(array $file): ?string
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            return 'Image upload failed. Please try again.';
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return 'Image must be a JPG, PNG, or WebP file.';
        }

        return null;
    }

    /**
     * Move an uploaded file into $destDir and return the path stored in
     * image_url (relative to the public root, e.g. uploads/switches/abc.jpg).
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
