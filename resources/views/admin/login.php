<div class="breadcrumb"><a href="/">Trang chủ</a><span>/</span><span>Đăng nhập quản trị</span></div>

<section class="section login-panel">
    <div class="login-intro">
        <div class="login-brand">
            <span class="logo-circle">
                <img src="/img/logo-mttq.png" alt="Logo Mặt trận Tổ quốc Việt Nam" width="58" height="58" decoding="async">
            </span>
            <div>
                <small>Hệ thống quản trị</small>
                <h1>MTTQ xã Tân Hòa</h1>
            </div>
        </div>
    </div>
    <div class="login-form-panel">
        <div class="login-heading">
            <h2>Đăng nhập quản trị</h2>
            <p>Nhập mật khẩu để truy cập hệ thống quản trị.</p>
        </div>
        <?php if ($error === 'rate-limit'): ?>
            <div class="notice error">Đăng nhập sai quá nhiều lần. Vui lòng thử lại sau vài phút.</div>
        <?php elseif ($error === 'captcha'): ?>
            <div class="notice error">Mã xác thực không đúng. Vui lòng thử lại.</div>
        <?php elseif ($error): ?>
            <div class="notice error">Mật khẩu không đúng hoặc phiên đăng nhập đã hết hạn.</div>
        <?php endif; ?>
        <form class="admin-form" action="/admin/login" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="captcha_signature" value="<?= e((string)($_SESSION['admin_login_captcha_signature'] ?? '')) ?>">
            <label>Mật khẩu quản trị
                <span class="password-field">
                    <input type="password" name="password" required autofocus placeholder="Nhập mật khẩu" data-password-input>
                    <button type="button" class="password-toggle" data-password-toggle aria-label="Hiện mật khẩu">
                        <span class="icon-eye-open" aria-hidden="true"></span>
                        <span class="icon-eye-closed" aria-hidden="true"></span>
                    </button>
                </span>
            </label>
            <label>Mã xác thực: <?= e($captchaQuestion ?? '') ?>
                <input type="text" name="captcha" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" required placeholder="Nhập 4 số mã xác thực">
            </label>
            <button class="btn-submit">Đăng nhập</button>
        </form>
        <div class="login-footer">
            <a href="/">Quay về trang chủ</a>
        </div>
    </div>
</section>
