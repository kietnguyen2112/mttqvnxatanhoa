<?php
$templateFiles = [
    'leaders' => '/assets/templates/mau_lanh_dao.xlsx',
    'hamlet_members' => '/assets/templates/mau_thanh_vien_cap_ap.xlsx',
    'loan_groups' => '/assets/templates/mau_to_vay_von.xlsx',
    'loan_members' => '/assets/templates/mau_thanh_vien_to_vay_von.xlsx',
];
$importOpen = !empty($status) && empty($status['ok']);
?>

<div class="import-workspace">
    <section class="section import-panel">
        <div class="admin-content-card">
            <div class="admin-content-card-head">
                <div class="admin-content-card-icon" aria-hidden="true">⇪</div>
                <div class="admin-content-card-main">
                    <h1>Nhập Excel</h1>
                    <p>Cập nhật nhanh dữ liệu quản trị từ file mẫu chuẩn.</p>
                </div>
                <span class="admin-content-status is-ready">Đã có nội dung</span>
                <button class="admin-content-action" type="button" data-admin-collapse-toggle="import-editor-panel" aria-controls="import-editor-panel" aria-expanded="<?= $importOpen ? 'true' : 'false' ?>">Thêm nội dung</button>
            </div>

        <?php if ($status): ?>
            <div class="notice <?= empty($status['ok']) ? 'error' : '' ?>">
                <strong><?= e($status['message']) ?></strong>
                <?php if (!empty($status['result'])): ?>
                    <p>Đã nhập: <strong><?= (int)$status['result']['imported'] ?></strong> dòng. Bỏ qua: <strong><?= (int)$status['result']['skipped'] ?></strong> dòng.</p>
                    <?php if (!empty($status['result']['errors'])): ?>
                        <ul class="import-errors">
                            <?php foreach (array_slice($status['result']['errors'], 0, 12) as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

            <div id="import-editor-panel" class="admin-card-form <?= $importOpen ? 'is-open' : '' ?>" data-admin-collapse-panel>
                <div class="admin-card-form-head">
                    <div>
                        <h2>Nhập dữ liệu từ Excel</h2>
                        <p>Chọn đúng loại dữ liệu và file mẫu `.xlsx`.</p>
                    </div>
                    <button class="admin-card-close" type="button" data-admin-collapse-close="import-editor-panel">Đóng</button>
                </div>
                <form class="admin-form import-form" action="/admin/import" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="form-row">
                <label>Loại dữ liệu
                    <select name="type" required>
                        <?php foreach ($types as $key => $config): ?>
                            <option value="<?= e($key) ?>"><?= e($config['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>File Excel
                    <input type="file" name="xlsx_file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                </label>
            </div>
            <button class="btn-submit">Nhập dữ liệu</button>
            <button class="btn-cancel" type="button" data-admin-collapse-close="import-editor-panel">Hủy</button>
                </form>
            </div>
        </div>

        <div class="import-rules">
            <strong>Lưu ý</strong>
            <span>Chỉ dùng file `.xlsx`. Không đổi tên sheet <b>Du lieu</b>, không xóa dòng tiêu đề.</span>
        </div>
    </section>

    <section class="section import-panel">
        <div class="section-head">
            <div>
                <h1>File mẫu</h1>
                <p>Tải đúng mẫu, điền dữ liệu rồi nhập lại lên hệ thống.</p>
            </div>
        </div>

        <div class="template-list">
            <?php foreach ($types as $key => $config): ?>
                <a class="template-item" href="<?= e($templateFiles[$key] ?? '#') ?>" download>
                    <span>
                        <strong><?= e($config['label']) ?></strong>
                        <small><?= e($config['example']) ?></small>
                    </span>
                    <em>Tải mẫu</em>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>
