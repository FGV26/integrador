<?php
require_once dirname(__DIR__, 2) . '/config/Storage.php';

header('Content-Type: application/json');

try {
    $objectKey = storage_upload_user_image($_FILES['imagen'] ?? null, 'users');

    if ($objectKey) {
        echo json_encode([
            'success' => true,
            'message' => 'Image uploaded successfully.',
            'filename' => $objectKey,
            'url' => storage_image_url($objectKey, '/'),
        ]);
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'File is not a valid image.']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
