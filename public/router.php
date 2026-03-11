<?php

// Router script for PHP built-in server (production on Railway)
// Returns false for existing static files (assets, images...) → served directly
// Otherwise routes everything through Symfony's front controller

$filePath = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if (is_file($filePath)) {
    return false;
}

require __DIR__ . '/index.php';
