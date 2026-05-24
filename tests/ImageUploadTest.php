<?php

test('validate accepts JPG, PNG and WebP files', function () {
    foreach (['photo.jpg', 'photo.jpeg', 'photo.PNG', 'photo.webp'] as $name) {
        $file = ['name' => $name, 'error' => UPLOAD_ERR_OK];
        assertSame(null, ImageUpload::validate($file));
    }
});

test('validate treats no file as acceptable (image is optional)', function () {
    assertSame(null, ImageUpload::validate(['name' => '', 'error' => UPLOAD_ERR_NO_FILE]));
});

test('validate rejects non-image extensions with an error message', function () {
    foreach (['evil.gif', 'doc.pdf', 'script.php'] as $name) {
        $file  = ['name' => $name, 'error' => UPLOAD_ERR_OK];
        $error = ImageUpload::validate($file);
        assertTrue($error !== null);
    }
});
