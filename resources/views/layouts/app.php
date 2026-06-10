<?php
$title = $title ?? 'Ủy ban MTTQ Việt Nam - Xã Tân Hòa';
$metaDescription = $metaDescription ?? 'Trang thông tin điện tử Ủy ban Mặt trận Tổ quốc Việt Nam xã Tân Hòa: giới thiệu, tổ chức thành viên, tổ vay vốn và văn bản công bố.';
$today = date('d/m/Y');
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$metaRobots = $metaRobots ?? ($currentPath === '/search' ? 'noindex,follow' : 'index,follow');
$postModuleEnabled = post_module_enabled();
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    || (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443');
$scheme = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '';
if (!isset($canonicalUrl)) {
    $canonicalPath = $currentPath;
    if ($currentPath === '/organizations/show' && isset($_GET['slug'])) {
        $canonicalPath .= '?slug=' . rawurlencode((string)$_GET['slug']);
    } elseif (($currentPath === '/loan-groups/show' || $currentPath === '/organizations/chapter') && isset($_GET['id'])) {
        $canonicalPath .= '?id=' . (int)$_GET['id'];
    }
    $canonicalUrl = app_url($canonicalPath);
}
$og = $og ?? [];
$ogTitle = (string)($og['title'] ?? $title);
$ogDescription = (string)($og['description'] ?? $metaDescription);
$ogType = (string)($og['type'] ?? 'website');
$ogImagePath = (string)($og['image'] ?? 'img/logo-mttq.png');
$ogImage = str_starts_with($ogImagePath, 'http://') || str_starts_with($ogImagePath, 'https://')
    ? $ogImagePath
    : app_url('/' . ltrim($ogImagePath, '/'));
$jsonLd = $jsonLd ?? [
    [
        '@context' => 'https://schema.org',
        '@type' => 'GovernmentOrganization',
        'name' => 'Ủy ban Mặt trận Tổ quốc Việt Nam xã Tân Hòa',
        'url' => app_url('/'),
        'logo' => app_url('/img/logo-mttq.png'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Đảng uỷ xã Tân Hòa',
            'addressLocality' => 'Tân Hòa',
            'addressRegion' => 'Cần Thơ',
            'addressCountry' => 'VN',
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'MTTQ xã Tân Hòa',
        'url' => app_url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => app_url('/search?q={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ],
];
$pageExperienceClass = match (true) {
    $currentPath === '/' || $currentPath === '/about' => 'page-home',
    str_starts_with($currentPath, '/organizations') => 'page-organizations',
    str_starts_with($currentPath, '/loan-groups') => 'page-loans',
    str_starts_with($currentPath, '/documents') => 'page-documents',
    str_starts_with($currentPath, '/posts') || str_starts_with($currentPath, '/tin-tuc') => 'page-posts',
    $currentPath === '/search' => 'page-search',
    default => 'page-public',
};
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function date_vi($value): string { return $value ? date('d/m/Y', strtotime($value)) : ''; }
function organization_logo_path(string $slug): string
{
    return match ($slug) {
        'doan-thanh-nien' => 'img/logo-4-hoi-doan-the/logo-doan.png',
        'hoi-lien-hiep-phu-nu' => 'img/logo-4-hoi-doan-the/logo-phunu.png',
        'hoi-cuu-chien-binh' => 'img/logo-4-hoi-doan-the/logo-ccb.png',
        'hoi-nong-dan' => 'img/logo-4-hoi-doan-the/logo-nd.png',
        default => '',
    };
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="robots" content="<?= e($metaRobots) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta property="og:locale" content="vi_VN">
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:title" content="<?= e($ogTitle) ?> | MTTQ xã Tân Hòa">
    <meta property="og:description" content="<?= e($ogDescription) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <link rel="icon" href="<?= e(public_asset('img/logo-mttq.png')) ?>">
    <?php if ($currentPath === '/' || $currentPath === '/about'): ?>
        <link rel="preload" as="image" href="<?= e(public_asset('img/optimized/about-hero.jpg')) ?>" fetchpriority="high">
    <?php endif; ?>
    <title><?= e($title) ?> | MTTQ xã Tân Hòa</title>
    <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <link rel="stylesheet" href="<?= e(public_asset('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(public_asset('assets/css/modern-ui.css')) ?>">
</head>
<body class="public-body <?= e($pageExperienceClass) ?>">
<a class="skip-link" href="#main-content">Bỏ qua đến nội dung chính</a>
<div class="experience-light experience-light-a" aria-hidden="true"></div>
<div class="experience-light experience-light-b" aria-hidden="true"></div>
<div class="site-utility">
    <div class="container utility-inner">
        <span>Cổng thông tin điện tử Ủy ban MTTQ Việt Nam xã Tân Hòa</span>
        <div class="utility-links">
            <span><?= e($today) ?></span>
        </div>
    </div>
</div>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/">
            <span class="logo-circle">
                <img src="/img/logo-mttq.png" alt="Logo Mặt trận Tổ quốc Việt Nam" width="64" height="64" decoding="async" fetchpriority="high">
            </span>
            <span>
                <small>Cổng thông tin điện tử</small>
                <strong>Ủy ban Mặt trận Tổ quốc Việt Nam</strong>
                <em>Xã Tân Hòa - Thành phố Cần Thơ</em>
            </span>
        </a>
        <button class="menu-toggle" type="button" data-menu-toggle aria-controls="public-navigation" aria-expanded="false" aria-label="Mở menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<nav class="site-nav">
    <div class="container nav-items" data-nav id="public-navigation">
        <a class="<?= $currentPath === '/' || $currentPath === '/about' ? 'active' : '' ?>" href="/">Trang chủ</a>
        <a class="<?= str_starts_with($currentPath, '/organizations') ? 'active' : '' ?>" href="/organizations">Tổ chức thành viên</a>
        <a class="<?= str_starts_with($currentPath, '/loan-groups') ? 'active' : '' ?>" href="/loan-groups">Tổ vay vốn</a>
        <a class="<?= str_starts_with($currentPath, '/documents') ? 'active' : '' ?>" href="/documents">Văn bản</a>
        <?php if ($postModuleEnabled): ?>
            <a class="<?= str_starts_with($currentPath, '/posts') || str_starts_with($currentPath, '/tin-tuc') ? 'active' : '' ?>" href="/posts">Bài đăng</a>
        <?php endif; ?>
        <form class="site-search" action="/search" method="get" data-ui-busy>
            <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tìm kiếm..." aria-label="Tìm kiếm">
            <button type="submit">Tìm</button>
        </form>
    </div>
</nav>

<main class="container page public-page" id="main-content">
    <?php require $viewFile; ?>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <section class="footer-brand">
            <span class="logo-circle">
                <img src="/img/logo-mttq.png" alt="Logo Mặt trận Tổ quốc Việt Nam" width="62" height="62" loading="lazy" decoding="async">
            </span>
            <div>
                <h4>Ủy ban MTTQ Việt Nam - Xã Tân Hòa</h4>
                <p>Trang thông tin điện tử phục vụ tuyên truyền, vận động nhân dân, kết nối tổ chức thành viên và khối đại đoàn kết toàn dân tộc tại địa phương.</p>
            </div>
        </section>
        <section class="footer-block">
            <h4>Thông tin liên hệ</h4>
            <ul class="footer-list">
                <li><span>Trụ sở</span><strong>Đảng uỷ xã Tân Hòa, TP. Cần Thơ</strong></li>
                <li><span>Điện thoại</span><strong>(0292) xxx.xxxx</strong></li>
                <li><span>Email</span><strong>mttq.tanhoa@cantho.gov.vn</strong></li>
            </ul>
        </section>
        <section class="footer-block">
            <h4>Liên kết nhanh</h4>
            <nav class="footer-links" aria-label="Liên kết chân trang">
                <a href="https://dichvucong.gov.vn" target="_blank" rel="noopener noreferrer">Cổng Dịch vụ Công Quốc gia</a>
                <a href="https://www.facebook.com/share/18a4Pa2YXp/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer">Facebook MTTQ xã Tân Hòa</a>
                <a href="/">Giới thiệu MTTQVN xã</a>
                <a href="/organizations">Tổ chức thành viên</a>
                <a href="/loan-groups">Tổ vay vốn</a>
                <a href="/documents">Văn bản</a>
                <?php if ($postModuleEnabled): ?>
                    <a href="/posts">Bài đăng</a>
                <?php endif; ?>
            </nav>
        </section>
    </div>
    <div class="container footer-bottom">
        <span>© <?= date('Y') ?> Ủy ban MTTQ Việt Nam xã Tân Hòa</span>
        <a href="/admin"><?= !empty($_SESSION['admin_logged_in']) ? 'Vào trang quản trị' : 'Đăng nhập quản trị' ?></a>
    </div>
</footer>
<button class="back-to-top" type="button" data-back-to-top aria-label="Cuộn lên đầu trang" title="Cuộn lên đầu trang" aria-hidden="true" tabindex="-1">
    <span class="back-to-top__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m6 14 6-6 6 6"></path>
            <path d="M12 9v8"></path>
        </svg>
    </span>
</button>
<script src="<?= e(public_asset('assets/js/app.js')) ?>"></script>
</body>
</html>
