<?php
$title = $title ?? 'Quản trị';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$postModuleEnabled = post_module_enabled();
$quickImportConfig = [
    '/admin/leaders' => ['type' => 'leaders', 'template' => '/assets/templates/mau_lanh_dao.xlsx'],
    '/admin/hamlet-members' => ['type' => 'hamlet_members', 'template' => '/assets/templates/mau_thanh_vien_cap_ap.xlsx'],
    '/admin/loan-groups' => ['type' => 'loan_groups', 'template' => '/assets/templates/mau_to_vay_von.xlsx'],
    '/admin/loan-members' => ['type' => 'loan_members', 'template' => '/assets/templates/mau_thanh_vien_to_vay_von.xlsx'],
];
if (!function_exists('quickImportLabel')) {
    function quickImportLabel(string $type): string
    {
        return match ($type) {
            'leaders' => 'Nhập cán bộ (.xlsx)',
            'hamlet_members' => 'Nhập hồ sơ cấp ấp (.xlsx)',
            'loan_groups' => 'Nhập tổ vay vốn (.xlsx)',
            'loan_members' => 'Nhập TV tổ vay (.xlsx)',
            default => 'Nhập Excel (.xlsx)',
        };
    }
}
if (!function_exists('e')) {
    function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('adminMenuIcon')) {
    function adminMenuIcon(string $name): string
    {
        $paths = [
            'dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect>',
            'leaders' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
            'hamlet' => '<path d="M3 21V9l9-6 9 6v12"></path><path d="M9 21v-6h6v6"></path><path d="M9 9h.01"></path><path d="M15 9h.01"></path>',
            'groups' => '<circle cx="12" cy="5" r="3"></circle><circle cx="5" cy="19" r="3"></circle><circle cx="19" cy="19" r="3"></circle><path d="M12 8v4"></path><path d="m7.5 17 3.5-3"></path><path d="m13 14 3.5 3"></path>',
            'members' => '<rect width="18" height="14" x="3" y="5" rx="2"></rect><path d="M7 9h.01"></path><path d="M7 13h.01"></path><path d="M11 9h6"></path><path d="M11 13h6"></path>',
            'documents' => '<path d="M6 2h9l3 3v17H6z"></path><path d="M14 2v5h4"></path><path d="M9 12h6"></path><path d="M9 16h6"></path>',
            'posts' => '<path d="M4 19.5V5a2 2 0 0 1 2-2h11l3 3v14.5a.5.5 0 0 1-.8.4L16 18l-3.2 2.9a.5.5 0 0 1-.7 0L9 18l-3.2 2.9a.5.5 0 0 1-.8-.4Z"></path><path d="M14 3v5h5"></path><path d="M8 11h8"></path><path d="M8 15h5"></path>',
            'search' => '<circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.3-4.3"></path>',
            'password' => '<rect width="18" height="11" x="3" y="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>',
            'home' => '<path d="M3 11 12 3l9 8"></path><path d="M5 10v11h14V10"></path><path d="M9 21v-6h6v6"></path>',
        ];

        return '<span class="admin-menu-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? $paths['dashboard']) . '</svg></span>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= e(public_asset('img/logo-mttq.png')) ?>">
    <title><?= e($title) ?> | Quản trị MTTQ xã Tân Hòa</title>
    <script>
        (function () {
            try {
                if (localStorage.getItem('mttq_admin_sidebar_collapsed') === '1') {
                    document.documentElement.classList.add('admin-sidebar-collapsed-init');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="<?= e(public_asset('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= e(public_asset('assets/css/modern-ui.css')) ?>">
</head>
<body class="admin-body">
<a class="skip-link" href="#main-content">Bỏ qua đến nội dung chính</a>
<div class="admin-experience-light" aria-hidden="true"></div>
<div class="admin-shell <?= !empty($_SESSION['admin_logged_in']) ? 'admin-shell-auth' : '' ?>" data-admin-shell>
    <?php if (!empty($_SESSION['admin_logged_in'])): ?>
        <aside class="admin-sidebar">
            <div class="admin-sidebar-head">
                <a class="admin-brand" href="/admin">
                    <span class="logo-circle">
                        <img src="/img/logo-mttq.png" alt="Logo Mặt trận Tổ quốc Việt Nam" width="52" height="52" decoding="async">
                    </span>
                    <strong>Quản trị MTTQ</strong>
                    <small>Xã Tân Hòa</small>
                </a>
            </div>
            <nav class="admin-menu">
                <a class="<?= $currentPath === '/admin' ? 'active' : '' ?>" href="/admin" title="Tổng quan"><?= adminMenuIcon('dashboard') ?><span class="admin-menu-label">Tổng quan</span></a>
                <a class="<?= $currentPath === '/admin/leaders' ? 'active' : '' ?>" href="/admin/leaders" title="Cán bộ"><?= adminMenuIcon('leaders') ?><span class="admin-menu-label">Cán bộ</span></a>
                <a class="<?= $currentPath === '/admin/hamlet-members' ? 'active' : '' ?>" href="/admin/hamlet-members" title="Hồ sơ cấp ấp"><?= adminMenuIcon('hamlet') ?><span class="admin-menu-label">Hồ sơ cấp ấp</span></a>
                <a class="<?= $currentPath === '/admin/loan-groups' ? 'active' : '' ?>" href="/admin/loan-groups" title="Tổ vay vốn"><?= adminMenuIcon('groups') ?><span class="admin-menu-label">Tổ vay vốn</span></a>
                <a class="<?= $currentPath === '/admin/loan-members' ? 'active' : '' ?>" href="/admin/loan-members" title="Thành viên vay vốn"><?= adminMenuIcon('members') ?><span class="admin-menu-label">Thành viên vay vốn</span></a>
                <a class="<?= $currentPath === '/admin/documents' ? 'active' : '' ?>" href="/admin/documents" title="Văn bản"><?= adminMenuIcon('documents') ?><span class="admin-menu-label">Văn bản</span></a>
                <?php if ($postModuleEnabled): ?>
                    <a class="<?= $currentPath === '/admin/posts' ? 'active' : '' ?>" href="/admin/posts" title="Bài đăng"><?= adminMenuIcon('posts') ?><span class="admin-menu-label">Bài đăng</span></a>
                <?php endif; ?>
                <a class="<?= $currentPath === '/admin/password' ? 'active' : '' ?>" href="/admin/password" title="Đổi mật khẩu"><?= adminMenuIcon('password') ?><span class="admin-menu-label">Đổi mật khẩu</span></a>
                <div class="admin-menu-bottom">
                    <span class="admin-menu-divider" aria-hidden="true"></span>
                    <a href="/" title="Xem trang chủ"><?= adminMenuIcon('home') ?><span class="admin-menu-label">Xem trang chủ</span></a>
                    <div class="admin-menu-actions">
                        <form class="admin-signout-form" action="/admin/logout" method="post">
                            <?= csrf_field() ?>
                            <button class="admin-signout-button" type="submit" title="Đăng xuất" aria-label="Đăng xuất khỏi hệ thống">
                                <span class="admin-signout-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <path d="M16 17l5-5-5-5"></path>
                                        <path d="M21 12H9"></path>
                                    </svg>
                                </span>
                                <span class="admin-signout-text">Đăng xuất</span>
                            </button>
                        </form>
                        <button class="admin-sidebar-toggle" type="button" data-admin-sidebar-toggle aria-label="Thu gọn menu" aria-expanded="true">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
            </nav>
        </aside>
    <?php endif; ?>

    <div class="admin-main <?= empty($_SESSION['admin_logged_in']) ? 'admin-main-login' : '' ?>">
        <?php if (!empty($_SESSION['admin_logged_in'])): ?>
            <header class="admin-topbar">
                <div>
                    <small>Hệ thống quản trị</small>
                    <h1><?= e($title) ?></h1>
                </div>
                <div class="admin-topbar-right <?= isset($quickImportConfig[$currentPath]) ? 'has-tools' : '' ?>">
                    <?php if (isset($quickImportConfig[$currentPath])): ?>
                        <?php $quickImport = $quickImportConfig[$currentPath]; ?>
                        <div class="admin-topbar-tools">
                            <form class="admin-import-quick" action="/admin/import" method="post" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="type" value="<?= e($quickImport['type']) ?>">
                                <label class="admin-import-quick-label" title="<?= e(quickImportLabel($quickImport['type'])) ?>" aria-label="<?= e(quickImportLabel($quickImport['type'])) ?>">
                                    <input type="file" name="xlsx_file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                                    <i class="bi bi-file-earmark-excel" aria-hidden="true"></i>
                                    <span><?= e(quickImportLabel($quickImport['type'])) ?></span>
                                </label>
                                <button type="submit" title="Nhập Excel" aria-label="Nhập Excel"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i><span class="admin-action-text">Nhập</span></button>
                            </form>
                            <a class="admin-top-action compact" href="/admin/export?type=<?= e($quickImport['type']) ?>" title="Xuất dữ liệu" aria-label="Xuất dữ liệu"><i class="bi bi-cloud-arrow-down" aria-hidden="true"></i><span class="admin-action-text">Xuất</span></a>
                            <a class="admin-top-action compact secondary" href="<?= e($quickImport['template']) ?>" download title="Tải file mẫu" aria-label="Tải file mẫu"><i class="bi bi-filetype-xlsx" aria-hidden="true"></i><span class="admin-action-text">Mẫu</span></a>
                        </div>
                    <?php endif; ?>
                    <form class="admin-search" action="/admin/search" method="get" data-ui-busy>
                        <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tìm kiếm" aria-label="Tìm từ khóa hoặc tên bài đăng">
                        <button type="submit" title="Tìm kiếm" aria-label="Tìm kiếm"><i class="bi bi-search" aria-hidden="true"></i><span class="admin-action-text">Tìm</span></button>
                    </form>
                </div>
            </header>
        <?php endif; ?>

        <main class="admin-content" id="main-content">
            <?php require $viewFile; ?>
        </main>
    </div>
</div>
<script src="<?= e(public_asset('assets/js/app.js')) ?>"></script>
</body>
</html>
