<?php

return [
    'timezone' => 'Asia/Ho_Chi_Minh',

    // Khi đưa lên host thật, đổi thành domain chính thức để canonical/sitemap trỏ đúng một nơi.
    // Ví dụ: 'https://mttqtanhoa.cantho.gov.vn'
    'site_url' => '',

    // Khi up host, giữ true để tự chuyển mọi truy cập HTTP sang HTTPS.
    'force_https' => true,

    // Chỉ gửi HSTS khi request đã chạy qua HTTPS để trình duyệt ưu tiên HTTPS.
    'hsts_enabled' => true,
    'hsts_max_age' => 31536000,

    // Đổi false để ẩn module bài đăng/tin tức, đổi true để bật lại.
    'post_module_enabled' => false,
];
