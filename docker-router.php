<?php

$publicPath = __DIR__ . '/public';
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = $publicPath . $uriPath;

// Serve existing public files directly.
if ($uriPath !== '/' && is_file($filePath)) {
    return false;
}

// Send all other requests to Laravel.
require $publicPath . '/index.php';
