<div class="admin-kpis">
    <a class="admin-kpi" href="/admin/leaders">
        <span>Cán bộ</span>
        <strong><?= count($leaders) ?></strong>
        <small>MTTQ xã và các hội</small>
    </a>
    <a class="admin-kpi" href="/admin/hamlet-members">
        <span>Hồ sơ cấp ấp</span>
        <strong><?= count($hamletMembers) ?></strong>
        <small>Hội viên và Ban Công tác Mặt trận</small>
    </a>
    <a class="admin-kpi" href="/admin/loan-groups">
        <span>Tổ vay vốn</span>
        <strong><?= count($loanGroups) ?></strong>
        <small>Theo hội quản lý và ấp</small>
    </a>
    <a class="admin-kpi" href="/admin/loan-members">
        <span>Thành viên vay vốn</span>
        <strong><?= count($loanMembers) ?></strong>
        <small>Danh sách tổ viên</small>
    </a>
    <a class="admin-kpi" href="/admin/documents">
        <span>Văn bản</span>
        <strong><?= (int)$documentCount ?></strong>
        <small>Tệp công bố để tải xuống</small>
    </a>
    <?php if (post_module_enabled()): ?>
        <a class="admin-kpi" href="/admin/posts">
            <span>Bài đăng</span>
            <strong><?= (int)$postCount ?></strong>
            <small>Tin tức và hoạt động công khai</small>
        </a>
    <?php endif; ?>
</div>

<section class="section admin-welcome">
    <div class="section-head"><h1>Tác vụ nhanh</h1></div>
    <div class="admin-shortcuts">
        <a href="/admin/leaders">Quản lý cán bộ</a>
        <a href="/admin/hamlet-members">Quản lý hồ sơ cấp ấp</a>
        <a href="/admin/loan-groups">Quản lý tổ vay vốn</a>
        <a href="/admin/loan-members">Quản lý thành viên tổ vay vốn</a>
        <a href="/admin/documents">Tải và quản lý văn bản</a>
        <?php if (post_module_enabled()): ?>
            <a href="/admin/posts">Đăng bài viết</a>
        <?php endif; ?>
        <a href="/admin/password">Đổi mật khẩu</a>
        <a href="/">Xem trang hiển thị</a>
    </div>
</section>
