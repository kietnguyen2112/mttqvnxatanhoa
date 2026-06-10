<?php

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    || (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443');

ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_secure', $isHttps ? '1' : '0');
ini_set('session.cookie_samesite', 'Lax');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

$_SESSION['test'] = ($_SESSION['test'] ?? 0) + 1;

header('Content-Type: text/html; charset=UTF-8');

echo '<h1>Session test</h1>';
echo '<p>Session ID: ' . htmlspecialchars(session_id(), ENT_QUOTES, 'UTF-8') . '</p>';
echo '<p>Value: ' . htmlspecialchars((string)$_SESSION['test'], ENT_QUOTES, 'UTF-8') . '</p>';
echo '<h2>Headers</h2>';
echo '<pre>';
print_r(headers_list());
echo '</pre>';
