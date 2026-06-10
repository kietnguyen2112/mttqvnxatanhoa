<?php
$isEditing = !empty($editDocument);
$editorOpen = $isEditing || (!empty($documentStatus) && empty($documentStatus['ok']));
if (!function_exists('admin_document_size')) {
    function admin_document_size(int $bytes): string
    {
        return $bytes >= 1024 * 1024
            ? number_format($bytes / (1024 * 1024), 1, ',', '.') . ' MB'
            : number_format(max(1, $bytes / 1024), 0, ',', '.') . ' KB';
    }
}
if (!function_exists('admin_document_date')) {
    function admin_document_date(?string $date): string
    {
        return $date ? date('d/m/Y', strtotime($date)) : '';
    }
}
?>
<div class="admin-page-grid admin-crud-page admin-documents-page">
    <section class="section admin-editor-panel">
        <div class="admin-content-card">
            <div class="admin-content-card-head">
                <div class="admin-content-card-icon" aria-hidden="true">▤</div>
                <div class="admin-content-card-main">
                    <h1>Văn bản</h1>
                    <p>Cập nhật thông tin văn bản, ngày ban hành và tệp đính kèm.</p>
                </div>
                <span class="admin-content-status <?= empty($documents) ? 'is-empty' : 'is-ready' ?>"><?= empty($documents) ? 'Chưa có nội dung' : 'Đã có nội dung' ?></span>
                <button class="admin-content-action <?= $isEditing ? 'is-editing' : '' ?>" type="button" data-admin-collapse-toggle="document-editor-panel" aria-controls="document-editor-panel" aria-expanded="<?= $editorOpen ? 'true' : 'false' ?>">
                    <?= $isEditing ? 'Sửa nội dung' : 'Thêm nội dung' ?>
                </button>
            </div>
            <div id="document-editor-panel" class="admin-card-form <?= $editorOpen ? 'is-open' : '' ?>" data-admin-collapse-panel>
                <div class="admin-card-form-head">
                    <div>
                        <h2><?= $isEditing ? 'Sửa văn bản' : 'Tải văn bản mới' ?></h2>
                        <p><?= $isEditing ? 'Đang chỉnh văn bản #' . (int)$editDocument['id'] : 'Nhập thông tin và chọn tệp văn bản.' ?></p>
                    </div>
                    <button class="admin-card-close" type="button" data-admin-collapse-close="document-editor-panel">Đóng</button>
                </div>
                <form class="admin-form" action="<?= $isEditing ? '/admin/documents/update' : '/admin/documents' ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?= (int)$editDocument['id'] ?>">
            <?php endif; ?>
            <label>Tên văn bản<input name="title" value="<?= e($editDocument['title'] ?? '') ?>" required placeholder="Ví dụ: Kế hoạch triển khai phong trào năm 2026"></label>
            <label>Số / ký hiệu<input name="document_number" value="<?= e($editDocument['document_number'] ?? '') ?>" placeholder="Ví dụ: 12/KH-MTTQ"></label>
            <label>Loại văn bản<input name="document_type" value="<?= e($editDocument['document_type'] ?? '') ?>" placeholder="Ví dụ: Kế hoạch, Thông báo, Biểu mẫu"></label>
            <label>Ngày ban hành<input type="date" name="issued_date" value="<?= e($editDocument['issued_date'] ?? '') ?>"></label>
            <label>Mô tả<textarea name="description" placeholder="Thông tin ngắn về nội dung văn bản"><?= e($editDocument['description'] ?? '') ?></textarea></label>
            <label>Tệp đính kèm
                <input type="file" name="document_files[]" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple <?= $isEditing ? '' : 'required' ?>>
                <small class="form-help">
                    <?= $isEditing
                        ? 'Để trống nếu chỉ sửa thông tin. Chọn tệp mới để thay toàn bộ tệp đính kèm hiện tại.'
                        : 'Có thể chọn nhiều tệp. Hỗ trợ PDF, DOC, DOCX, XLS, XLSX; tối đa 10 MB mỗi tệp.' ?>
                </small>
                <?php if ($isEditing && !empty($editDocument['attachments'])): ?>
                    <small class="form-help">
                        Tệp hiện tại:
                        <?php foreach ($editDocument['attachments'] as $index => $file): ?>
                            <a href="/documents/download?file=<?= (int)$file['id'] ?>"><?= e($file['original_name']) ?></a><?= $index < count($editDocument['attachments']) - 1 ? ', ' : '' ?>
                        <?php endforeach; ?>
                    </small>
                <?php endif; ?>
            </label>
            <button class="btn-submit" type="submit"><?= $isEditing ? 'Cập nhật văn bản' : 'Tải văn bản lên' ?></button>
            <?php if ($isEditing): ?>
                <a class="btn-cancel" href="/admin/documents">Hủy sửa</a>
            <?php else: ?>
                <button class="btn-cancel" type="button" data-admin-collapse-close="document-editor-panel">Hủy</button>
            <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <section class="section admin-list-panel">
        <div class="section-head">
            <h1>Văn bản đã tải</h1>
            <span class="admin-list-count"><?= count($documents) ?> bản ghi</span>
        </div>
        <?php if (!empty($documentStatus)): ?>
            <div class="notice <?= empty($documentStatus['ok']) ? 'error' : '' ?>">
                <strong><?= e($documentStatus['message']) ?></strong>
            </div>
        <?php endif; ?>
        <div class="table-wrap admin-list-scroll">
            <table class="admin-data-table admin-documents-table">
                <thead><tr><th>Văn bản</th><th>Loại / số</th><th>Ngày ban hành</th><th>Tệp</th><th></th></tr></thead>
                <tbody>
                <?php if (empty($documents)): ?>
                    <tr><td colspan="5" class="admin-empty-row">Chưa có văn bản được tải lên.</td></tr>
                <?php else: ?>
                    <?php foreach ($documents as $document): ?>
                        <tr>
                            <td class="admin-primary-cell"><strong><?= e($document['title']) ?></strong><small><?= e($document['description']) ?></small></td>
                            <td><?= e($document['document_type']) ?><br><small><?= e($document['document_number']) ?></small></td>
                            <td><?= e(admin_document_date($document['issued_date'])) ?></td>
                            <td>
                                <div class="admin-document-files">
                                    <?php foreach ($document['attachments'] as $file): ?>
                                        <?php $canPreview = strtolower(pathinfo($file['original_name'], PATHINFO_EXTENSION)) === 'pdf'; ?>
                                        <div class="admin-document-file">
                                            <a class="admin-document-download" href="/documents/download?file=<?= (int)$file['id'] ?>"><?= e($file['original_name']) ?></a>
                                            <small><?= e(admin_document_size((int)$file['file_size'])) ?></small>
                                            <?php if ($canPreview): ?>
                                                <a class="admin-file-preview" href="/documents/preview?file=<?= (int)$file['id'] ?>" target="_blank" rel="noopener">Xem trước</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn-edit" href="/admin/documents?edit=<?= (int)$document['id'] ?>">Sửa</a>
                                    <form action="/admin/documents/delete" method="post" onsubmit="return confirm('Xóa văn bản và tệp đã tải lên?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$document['id'] ?>">
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
    </section>
</div>
