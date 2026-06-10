<?php

$appConfig = require __DIR__ . '/../config/app.php';
date_default_timezone_set((string)($appConfig['timezone'] ?? 'Asia/Ho_Chi_Minh'));

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    || (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443');
$host = (string)($_SERVER['HTTP_HOST'] ?? '');
$isLocalHost = in_array(strtolower(explode(':', $host)[0]), ['127.0.0.1', 'localhost', '::1'], true);

if (!$isHttps && !$isLocalHost && !empty($appConfig['force_https'])) {
    header('Location: https://' . $host . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}

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

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'");
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('X-Permitted-Cross-Domain-Policies: none');
if ($isHttps && !$isLocalHost && !empty($appConfig['hsts_enabled'])) {
    header('Strict-Transport-Security: max-age=' . (int)($appConfig['hsts_max_age'] ?? 31536000) . '; includeSubDomains');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' .
        htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') .
        '">';
}

function csrf_token(): string
{
    return (string)($_SESSION['csrf_token'] ?? '');
}

function public_asset(string $path): string
{
    $relativePath = ltrim($path, '/');
    $filePath = __DIR__ . '/' . $relativePath;
    $version = is_file($filePath) ? filemtime($filePath) : null;

    return '/' . $relativePath . ($version ? '?v=' . $version : '');
}

function app_config(string $key, mixed $default = null): mixed
{
    global $appConfig;

    return $appConfig[$key] ?? $default;
}

function app_base_url(): string
{
    $configuredUrl = trim((string)app_config('site_url', ''));
    if ($configuredUrl !== '') {
        return rtrim($configuredUrl, '/');
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $host = (string)($_SERVER['HTTP_HOST'] ?? '');

    return $host !== '' ? ($isHttps ? 'https' : 'http') . '://' . $host : '';
}

function app_url(string $path = '/'): string
{
    $path = '/' . ltrim($path, '/');
    $baseUrl = app_base_url();

    return $baseUrl !== '' ? $baseUrl . $path : $path;
}

function post_public_url(array $post): string
{
    return !empty($post['slug'])
        ? '/tin-tuc/' . rawurlencode((string)$post['slug'])
        : '/posts/show?id=' . (int)$post['id'];
}

function post_module_enabled(): bool
{
    return (bool)app_config('post_module_enabled', false);
}

function render_sitemap(): void
{
    $urls = [
        ['loc' => app_url('/'), 'priority' => '1.0', 'changefreq' => 'weekly'],
        ['loc' => app_url('/organizations'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => app_url('/loan-groups'), 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['loc' => app_url('/documents'), 'priority' => '0.7', 'changefreq' => 'weekly'],
    ];

    foreach (App\Models\Organization::all() as $organization) {
        if (!empty($organization['slug'])) {
            $urls[] = [
                'loc' => app_url('/organizations/show?slug=' . rawurlencode((string)$organization['slug'])),
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ];
        }

        foreach (($organization['chapters'] ?? []) as $chapter) {
            if (!empty($chapter['id'])) {
                $urls[] = [
                    'loc' => app_url('/organizations/chapter?id=' . (int)$chapter['id']),
                    'priority' => '0.6',
                    'changefreq' => 'monthly',
                ];
            }
        }
    }

    foreach (App\Models\LoanGroup::all() as $group) {
        if (!empty($group['id'])) {
            $urls[] = [
                'loc' => app_url('/loan-groups/show?id=' . (int)$group['id']),
                'priority' => '0.5',
                'changefreq' => 'monthly',
            ];
        }
    }

    if (post_module_enabled()) {
        $urls[] = ['loc' => app_url('/posts'), 'priority' => '0.8', 'changefreq' => 'weekly'];
        foreach (App\Models\Post::all(true) as $post) {
            $urls[] = [
                'loc' => app_url(post_public_url($post)),
                'lastmod' => date('Y-m-d', strtotime((string)($post['published_at'] ?? 'now'))),
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ];
        }
    }

    header('Content-Type: application/xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $url) {
        echo "  <url>\n";
        echo '    <loc>' . htmlspecialchars((string)$url['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
        if (!empty($url['lastmod'])) {
            echo '    <lastmod>' . htmlspecialchars((string)$url['lastmod'], ENT_XML1, 'UTF-8') . "</lastmod>\n";
        }
        echo '    <changefreq>' . htmlspecialchars((string)$url['changefreq'], ENT_XML1, 'UTF-8') . "</changefreq>\n";
        echo '    <priority>' . htmlspecialchars((string)$url['priority'], ENT_XML1, 'UTF-8') . "</priority>\n";
        echo "  </url>\n";
    }
    echo "</urlset>\n";
}

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($path)) {
        require $path;
    }
});

$routes = require __DIR__ . '/../routes/web.php';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($method === 'GET' && $path === '/sitemap.xml') {
    render_sitemap();
    exit;
}

if ($method === 'POST') {
    $submittedToken = (string)($_POST['_token'] ?? '');
    $isAdminLogin = $path === '/admin/login';
    if (!$isAdminLogin && !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        http_response_code(419);
        exit('Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
    }
}

$handler = $routes[$method][$path] ?? null;

// Feature gate: keep the post/news code and data intact, but hide/block the module when disabled.
if (!post_module_enabled() && preg_match('#^(/posts(?:/show)?|/tin-tuc(?:/.*)?|/news(?:/.*)?|/articles?(?:/.*)?|/bai-viet(?:/.*)?|/admin/posts(?:/.*)?)$#', $path)) {
    http_response_code(404);
    exit('404 - Không tìm thấy trang');
}

if (!$handler && $method === 'GET' && post_module_enabled() && preg_match('#^/tin-tuc/([a-z0-9-]+)$#', $path, $matches)) {
    (new App\Controllers\PostController())->showBySlug($matches[1]);
    exit;
}

if (!$handler) {
    http_response_code(404);
    exit('404 - Không tìm thấy trang');
}

[$controller, $action] = $handler;
(new $controller())->$action();
