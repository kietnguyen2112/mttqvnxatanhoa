<section class="section password-panel admin-password-page">
    <div class="admin-content-card">
        <div class="admin-content-card-head">
            <div class="admin-content-card-icon" aria-hidden="true">●</div>
            <div class="admin-content-card-main">
                <h1>Mật khẩu quản trị</h1>
                <p>Cập nhật mật khẩu đăng nhập khu vực admin.</p>
            </div>
        </div>
    <?php if ($status === 'changed'): ?>
        <div class="notice">Đã đổi mật khẩu quản trị.</div>
    <?php endif; ?>
        <div class="admin-password-form-card">
            <div class="admin-card-form-head">
            </div>
            <?php if ($error): ?>
                <div class="notice error">
                    <?php if ($error === 'short'): ?>
                        Mật khẩu mới phải có ít nhất 6 ký tự.
                    <?php elseif ($error === 'confirm'): ?>
                        Xác nhận mật khẩu mới không khớp.
                    <?php else: ?>
                        Mật khẩu hiện tại không đúng.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <form class="admin-form" action="/admin/password" method="post">
        <?= csrf_field() ?>
        <label>Mật khẩu hiện tại
            <span class="password-field">
                <input type="password" name="current_password" required>
            </span>
        </label>
        <label>Mật khẩu mới
            <span class="password-field">
                <input type="password" name="new_password" required minlength="6">
            </span>
        </label>
        <label>Nhập lại mật khẩu mới
            <span class="password-field">
                <input type="password" name="confirm_password" required minlength="6">
            </span>
        </label>
        <button class="btn-submit">Cập nhật mật khẩu</button>
            </form>
        </div>
    </div>
</section>
