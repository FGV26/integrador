<?php
if (!function_exists('app_base_url')) {
function app_base_url() {
    $configuredBaseUrl = getenv('APP_BASE_URL');

    if ($configuredBaseUrl) {
        return rtrim($configuredBaseUrl, '/') . '/';
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/';
}
}

if (!function_exists('google_maps_api_key')) {
function google_maps_api_key() {
    $key = getenv('GOOGLE_MAPS_API_KEY');
    return $key && $key !== 'TU_API_KEY' ? $key : '';
}
}
?>
