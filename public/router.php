<?php
// Router for PHP built-in server: serve existing files, otherwise delegate to index.php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$requested = __DIR__ . $uri;

if ($uri !== '/' && file_exists($requested)) {
    return false; // serve the requested resource as-is
}

require __DIR__ . '/index.php';
