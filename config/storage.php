<?php
function storage_bucket() {
    return getenv('MINIO_BUCKET') ?: 'integrador-images';
}

function storage_public_endpoint() {
    return rtrim(getenv('MINIO_PUBLIC_ENDPOINT') ?: 'http://localhost:9000', '/');
}

function storage_internal_endpoint() {
    return rtrim(getenv('MINIO_ENDPOINT') ?: 'http://minio:9000', '/');
}


function storage_image_url($image, $baseUrl) {
    if (!$image || $image === 'default.png') {
        return rtrim($baseUrl, '/') . '/assets/img/default.png';
    }

    if (preg_match('/^https?:\/\//', $image)) {
        return $image;
    }

    if (str_contains($image, '/')) {
        return storage_public_endpoint() . '/' . storage_bucket() . '/' . ltrim($image, '/');
    }

    return rtrim($baseUrl, '/') . '/assets/img/' . $image;
}


function storage_upload_user_image($file, $folder = 'users') {
    if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return null;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $allowedExtensions, true)) {
        return null;
    }

    $objectKey = trim($folder, '/') . '/' . date('Y/m') . '/' . bin2hex(random_bytes(16)) . '.' . $extension;
    storage_put_object($objectKey, $file['tmp_name'], $imageInfo['mime']);

    return $objectKey;
}

function storage_put_object($objectKey, $filePath, $contentType) {
    $accessKey = getenv('MINIO_ACCESS_KEY') ?: 'integrador_minio';
    $secretKey = getenv('MINIO_SECRET_KEY') ?: 'integrador_minio_pass';
    $bucket = storage_bucket();
    $endpoint = storage_internal_endpoint();
    $region = 'us-east-1';
    $service = 's3';

    $payload = file_get_contents($filePath);
    $payloadHash = hash('sha256', $payload);
    $amzDate = gmdate('Ymd\THis\Z');
    $dateStamp = gmdate('Ymd');
    $canonicalUri = '/' . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($objectKey));
    $host = parse_url($endpoint, PHP_URL_HOST) . ':' . parse_url($endpoint, PHP_URL_PORT);

    $canonicalHeaders = "host:$host\nx-amz-content-sha256:$payloadHash\nx-amz-date:$amzDate\n";
    $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';
    $canonicalRequest = "PUT\n$canonicalUri\n\n$canonicalHeaders\n$signedHeaders\n$payloadHash";
    $credentialScope = "$dateStamp/$region/$service/aws4_request";
    $stringToSign = "AWS4-HMAC-SHA256\n$amzDate\n$credentialScope\n" . hash('sha256', $canonicalRequest);

    $dateKey = hash_hmac('sha256', $dateStamp, 'AWS4' . $secretKey, true);
    $dateRegionKey = hash_hmac('sha256', $region, $dateKey, true);
    $dateRegionServiceKey = hash_hmac('sha256', $service, $dateRegionKey, true);
    $signingKey = hash_hmac('sha256', 'aws4_request', $dateRegionServiceKey, true);
    $signature = hash_hmac('sha256', $stringToSign, $signingKey);

    $authorization = 'AWS4-HMAC-SHA256 Credential=' . $accessKey . '/' . $credentialScope . ', SignedHeaders=' . $signedHeaders . ', Signature=' . $signature;
    $url = $endpoint . $canonicalUri;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $authorization,
            'Content-Type: ' . $contentType,
            'Host: ' . $host,
            'x-amz-content-sha256: ' . $payloadHash,
            'x-amz-date: ' . $amzDate,
        ],
    ]);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $statusCode < 200 || $statusCode >= 300) {
        throw new RuntimeException('No se pudo subir la imagen a MinIO. HTTP ' . $statusCode . ' ' . $error . ' ' . $response);
    }
}
?>
