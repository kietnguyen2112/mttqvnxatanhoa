<?php
$isEditing = !empty($editPost);
$formPost = $postForm ?? ($editPost ?? []);
$filters = $filters ?? ['q' => '', 'status' => ''];
$postErrors = $postErrors ?? [];
$editorOpen = $isEditing || !empty($postErrors);
if (!function_exists('admin_post_date')) {
    function admin_post_date(?string $date): string
    {
        return $date ? date('d/m/Y H:i', strtotime($date)) : '';
    }
}
if (!function_exists('admin_post_editor_html')) {
    function admin_post_editor_html(string $content): string
    {
        $content = trim($content);
        if ($content === '') {
            return '';
        }

        return $content === strip_tags($content)
            ? '<p>' . nl2br(e($content)) . '</p>'
            : $content;
    }
}
if (!function_exists('admin_post_url')) {
    function admin_post_url(array $post): string
    {
        return !empty($post['slug'])
            ? '/tin-tuc/' . rawurlencode((string)$post['slug'])
            : '/posts/show?id=' . (int)$post['id'];
    }
}
if (!function_exists('admin_post_status_label')) {
    function admin_post_status_label(string $status): string
    {
        return match ($status) {
            'draft' => 'Nháp',
            'hidden' => 'Ẩn',
            default => 'Đã đăng',
        };
    }
}
if (!function_exists('admin_post_is_public')) {
    function admin_post_is_public(array $post): bool
    {
        return ($post['status'] ?? '') === 'published'
            && !empty($post['published_at'])
            && strtotime((string)$post['published_at']) <= time();
    }
}
if (!function_exists('admin_post_visibility_note')) {
    function admin_post_visibility_note(array $post): string
    {
        if (!post_module_enabled()) {
            return 'Module bài đăng đang tắt trong config/app.php.';
        }
        if (($post['status'] ?? '') === 'draft') {
            return 'Chưa hiển thị: bài đang lưu nháp.';
        }
        if (($post['status'] ?? '') === 'hidden') {
            return 'Chưa hiển thị: bài đang bị ẩn.';
        }
        if (empty($post['published_at']) || strtotime((string)$post['published_at']) > time()) {
            return 'Chưa hiển thị: thời gian đăng đang ở tương lai.';
        }

        return 'Đang hiển thị ngoài website.';
    }
}
?>
<div class="admin-page-grid admin-crud-page admin-posts-page admin-posts-modal-page">
    <section class="section admin-editor-panel">
        <div class="admin-content-card">
            <div class="admin-content-card-head">
                <div class="admin-content-card-icon" aria-hidden="true">✎</div>
                <div class="admin-content-card-main">
                    <h1>Bài đăng</h1>
                    <p>Soạn tin tức, chọn ảnh đại diện, trạng thái hiển thị và thông tin SEO.</p>
                </div>
                <span class="admin-content-status <?= empty($posts) ? 'is-empty' : 'is-ready' ?>"><?= empty($posts) ? 'Chưa có nội dung' : 'Đã có nội dung' ?></span>
                <button class="admin-content-action <?= $isEditing ? 'is-editing' : '' ?>" type="button" data-admin-collapse-toggle="post-editor-panel" aria-controls="post-editor-panel" aria-expanded="<?= $editorOpen ? 'true' : 'false' ?>">
                    <?= $isEditing ? 'Sửa nội dung' : 'Thêm nội dung' ?>
                </button>
            </div>
            <button class="admin-editor-modal-backdrop" type="button" data-admin-collapse-close="post-editor-panel" aria-label="Đóng cửa sổ nhập bài đăng"></button>
            <div id="post-editor-panel" class="admin-card-form <?= $editorOpen ? 'is-open' : '' ?>" data-admin-collapse-panel role="dialog" aria-modal="true" aria-labelledby="post-editor-title">
                <div class="admin-card-form-head">
                    <div>
                        <h2 id="post-editor-title"><?= $isEditing ? 'Sửa bài đăng' : 'Đăng bài mới' ?></h2>
                        <p><?= $isEditing ? 'Đang chỉnh bài #' . (int)$editPost['id'] : 'Nhập thông tin bài đăng mới.' ?></p>
                    </div>
                    <button class="admin-card-close" type="button" data-admin-collapse-close="post-editor-panel">Đóng</button>
                </div>
                <?php if (!empty($postErrors)): ?>
                    <div class="notice error">
                        <?php foreach ($postErrors as $error): ?>
                            <strong><?= e($error) ?></strong>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form class="admin-form" action="<?= $isEditing ? '/admin/posts/update' : '/admin/posts' ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?= (int)$editPost['id'] ?>">
            <?php endif; ?>
            <label>Tiêu đề<input name="title" value="<?= e($formPost['title'] ?? '') ?>" required maxlength="255" placeholder="Ví dụ: MTTQ xã tổ chức ngày hội đại đoàn kết" data-slug-source></label>
            <label>Slug SEO<input name="slug" value="<?= e($formPost['slug'] ?? '') ?>" maxlength="255" placeholder="mttq-xa-to-chuc-ngay-hoi-dai-doan-ket" data-slug-target></label>
            <label>Tóm tắt<textarea name="excerpt" rows="3" placeholder="Nội dung ngắn hiển thị ở danh sách bài đăng"><?= e($formPost['excerpt'] ?? '') ?></textarea></label>
            <div class="admin-rich-editor">
                <label class="admin-rich-editor-label" for="post-content-editor">Nội dung</label>
                <textarea id="post-content-editor" class="admin-rich-source" name="content" data-tinymce-editor data-upload-url="/admin/posts/content-image" data-csrf-token="<?= e(csrf_token()) ?>"><?= e((string)($formPost['content'] ?? '')) ?></textarea>
                <small class="form-help">Có thể copy nội dung từ Word, chèn bảng, định dạng văn bản và tải ảnh trực tiếp trong nội dung. Ảnh JPG, PNG, WEBP tối đa 2 MB.</small>
            </div>
            <label>Ảnh đại diện
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                <small class="form-help"><?= $isEditing ? 'Để trống nếu giữ ảnh hiện tại. ' : '' ?>Hỗ trợ JPG, PNG, WEBP; tối đa 2 MB.</small>
                <?php if ($isEditing && !empty($editPost['image_path'])): ?>
                    <small class="form-help"><a href="/<?= e($editPost['image_path']) ?>" target="_blank" rel="noopener">Xem ảnh hiện tại</a></small>
                <?php endif; ?>
            </label>
            <label>Trạng thái
                <select name="status">
                    <option value="published" <?= ($formPost['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Đăng công khai</option>
                    <option value="draft" <?= ($formPost['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Lưu nháp</option>
                    <option value="hidden" <?= ($formPost['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Ẩn bài viết</option>
                </select>
            </label>
            <label class="admin-checkbox-line">
                <input type="checkbox" name="is_featured" value="1" <?= !empty($formPost['is_featured']) ? 'checked' : '' ?>>
                <span>Bài viết nổi bật</span>
            </label>
            <label>Thời gian đăng<input type="datetime-local" name="published_at" value="<?= e(!empty($formPost['published_at']) ? date('Y-m-d\TH:i', strtotime($formPost['published_at'])) : date('Y-m-d\TH:i')) ?>"></label>
            <label>Meta title<input name="meta_title" value="<?= e($formPost['meta_title'] ?? '') ?>" maxlength="255" placeholder="Để trống sẽ dùng tiêu đề bài viết"></label>
            <label>Meta description<textarea name="meta_description" rows="2" maxlength="255" placeholder="Để trống sẽ dùng tóm tắt bài viết"><?= e($formPost['meta_description'] ?? '') ?></textarea></label>
            <button class="btn-submit" type="submit"><?= $isEditing ? 'Cập nhật bài đăng' : 'Đăng bài' ?></button>
            <?php if ($isEditing): ?>
                <a class="btn-cancel" href="/admin/posts">Hủy sửa</a>
                <a class="btn-cancel" href="/admin/posts/preview?id=<?= (int)$editPost['id'] ?>" target="_blank" rel="noopener">Xem trước</a>
            <?php else: ?>
                <button class="btn-cancel" type="button" data-admin-collapse-close="post-editor-panel">Hủy</button>
            <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <section class="section admin-list-panel">
        <div class="section-head">
            <h1>Bài đăng đã tạo</h1>
            <span class="admin-list-count"><?= (int)($total ?? count($posts)) ?> bản ghi</span>
        </div>
        <form class="admin-filter-bar admin-post-status-filter" action="/admin/posts" method="get" role="search">
            <label class="admin-post-search-field">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input name="q" value="<?= e((string)($filters['q'] ?? '')) ?>" placeholder="Tìm từ khóa hoặc tên bài đăng" aria-label="Tìm từ khóa hoặc tên bài đăng">
            </label>
            <select name="status" data-admin-auto-filter data-clear-url="/admin/posts" aria-label="Lọc trạng thái bài đăng">
                <option value="">Tất cả trạng thái</option>
                <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Đã đăng</option>
                <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Nháp</option>
                <option value="hidden" <?= ($filters['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Ẩn</option>
            </select>
            <button class="admin-post-search-submit" type="submit" aria-label="Tìm kiếm bài đăng" title="Tìm kiếm bài đăng">
                <i class="bi bi-search" aria-hidden="true"></i>
            </button>
        </form>
        <?php if (!empty($postStatus)): ?>
            <div class="notice <?= empty($postStatus['ok']) ? 'error' : '' ?>">
                <strong><?= e($postStatus['message']) ?></strong>
            </div>
        <?php endif; ?>
        <div class="table-wrap admin-list-scroll">
            <table class="admin-data-table admin-posts-table">
                <thead><tr><th>Bài đăng</th><th>Trạng thái</th><th>Thời gian đăng</th><th>Ảnh</th><th>Lượt xem</th><th></th></tr></thead>
                <tbody>
                <?php if (empty($posts)): ?>
                    <tr><td colspan="6" class="admin-empty-row">Chưa có bài đăng.</td></tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td class="admin-primary-cell">
                                <strong><?= e($post['title']) ?></strong>
                                <small><?= e($post['slug'] ? '/tin-tuc/' . $post['slug'] : '') ?></small>
                                <small><?= e($post['excerpt']) ?></small>
                            </td>
                            <td>
                                <span class="admin-status-pill status-<?= e($post['status']) ?>"><?= e(admin_post_status_label((string)$post['status'])) ?></span>
                                <?php if (!empty($post['is_featured'])): ?>
                                    <span class="admin-status-pill featured">Nổi bật</span>
                                <?php endif; ?>
                                <small class="admin-post-visibility <?= admin_post_is_public($post) ? 'is-public' : 'is-waiting' ?>"><?= e(admin_post_visibility_note($post)) ?></small>
                            </td>
                            <td><?= e(admin_post_date($post['published_at'])) ?></td>
                            <td>
                                <?php if (!empty($post['image_path'])): ?>
                                    <img class="admin-post-thumb" src="/<?= e($post['image_path']) ?>" alt="" width="86" height="54" loading="lazy" decoding="async">
                                <?php else: ?>
                                    <span class="admin-muted">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)($post['views'] ?? 0) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn-edit" href="<?= admin_post_is_public($post) ? e(admin_post_url($post)) : '/admin/posts/preview?id=' . (int)$post['id'] ?>" target="_blank" rel="noopener"><?= admin_post_is_public($post) ? 'Xem' : 'Preview' ?></a>
                                    <a class="btn-edit" href="/admin/posts?edit=<?= (int)$post['id'] ?>">Sửa</a>
                                    <form action="/admin/posts/featured" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                        <button type="submit"><?= !empty($post['is_featured']) ? 'Bỏ nổi bật' : 'Nổi bật' ?></button>
                                    </form>
                                    <form action="/admin/posts/status" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $post['status'] === 'published' ? 'hidden' : 'published' ?>">
                                        <button type="submit"><?= $post['status'] === 'published' ? 'Ẩn' : 'Đăng' ?></button>
                                    </form>
                                    <form action="/admin/posts/delete" method="post" onsubmit="return confirm('Xóa bài đăng này?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                        <button class="danger" type="submit">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (($totalPages ?? 1) > 1): ?>
            <nav class="pagination admin-pagination" aria-label="Phân trang bài đăng">
                <?php for ($itemPage = 1; $itemPage <= (int)$totalPages; $itemPage++): ?>
                    <a class="<?= $itemPage === (int)$page ? 'active' : '' ?>" href="/admin/posts?page=<?= $itemPage ?>&q=<?= urlencode((string)($filters['q'] ?? '')) ?>&status=<?= urlencode((string)($filters['status'] ?? '')) ?>"><?= $itemPage ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
