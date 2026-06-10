<?php
$isEditing = !empty($editMember);
$editorOpen = $isEditing;
?>
<div class="admin-page-grid admin-crud-page">
    <section class="section admin-editor-panel">
        <div class="admin-content-card">
            <div class="admin-content-card-head">
                <div class="admin-content-card-icon" aria-hidden="true">⌂</div>
                <div class="admin-content-card-main">
                    <h1>Hồ sơ cấp ấp</h1>
                    <p>Quản lý thành viên, Ban Công tác Mặt trận ấp và vai trò phụ trách.</p>
                </div>
                <span class="admin-content-status <?= empty($hamletMembers) ? 'is-empty' : 'is-ready' ?>"><?= empty($hamletMembers) ? 'Chưa có nội dung' : 'Đã có nội dung' ?></span>
                <button class="admin-content-action <?= $isEditing ? 'is-editing' : '' ?>" type="button" data-admin-collapse-toggle="hamlet-editor-panel" aria-controls="hamlet-editor-panel" aria-expanded="<?= $editorOpen ? 'true' : 'false' ?>">
                    <?= $isEditing ? 'Sửa nội dung' : 'Thêm nội dung' ?>
                </button>
            </div>
            <div id="hamlet-editor-panel" class="admin-card-form <?= $editorOpen ? 'is-open' : '' ?>" data-admin-collapse-panel>
                <div class="admin-card-form-head">
                    <div>
                        <h2><?= $isEditing ? 'Sửa hồ sơ cấp ấp' : 'Thêm hồ sơ cấp ấp' ?></h2>
                        <p><?= $isEditing ? 'Đang chỉnh hồ sơ #' . (int)$editMember['id'] : 'Nhập hồ sơ cấp ấp mới.' ?></p>
                    </div>
                    <button class="admin-card-close" type="button" data-admin-collapse-close="hamlet-editor-panel">Đóng</button>
                </div>
                <form class="admin-form" action="<?= $isEditing ? '/admin/hamlet-members/update' : '/admin/hamlet-members' ?>" method="post">
            <?= csrf_field() ?>
            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?= (int)$editMember['id'] ?>">
            <?php endif; ?>
            <label>Đơn vị quản lý
                <select name="organization_id" required>
                    <?php foreach ($memberOrganizations as $organization): ?>
                        <option value="<?= (int)$organization['id'] ?>" <?= $isEditing && (int)$editMember['organization_id'] === (int)$organization['id'] ? 'selected' : '' ?>><?= e($organization['short_name']) ?> - <?= e($organization['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Chi đoàn, chi hội / ấp<input name="hamlet_name" value="<?= e($editMember['hamlet_name'] ?? '') ?>" required placeholder="Ví dụ: Ấp Một Ngàn"></label>
            <label>Họ và tên<input name="full_name" value="<?= e($editMember['full_name'] ?? '') ?>" required></label>
            <label>Ngày tháng năm sinh<input type="date" name="birth_date" value="<?= e($editMember['birth_date'] ?? '') ?>"></label>
            <label>Chức vụ / Vai trò<input name="role" value="<?= e($editMember['role'] ?? '') ?>" required placeholder="Ví dụ: Trưởng ban Công tác Mặt trận ấp"></label>
            <label>Điện thoại<input name="phone" value="<?= e($editMember['phone'] ?? '') ?>"></label>
            <label>Ghi chú<input name="note" value="<?= e($editMember['note'] ?? '') ?>"></label>
            <label>Thứ tự<input type="number" name="sort_order" value="<?= e($editMember['sort_order'] ?? 0) ?>"></label>
            <button class="btn-submit"><?= $isEditing ? 'Cập nhật hồ sơ' : 'Lưu hồ sơ' ?></button>
            <?php if ($isEditing): ?>
                <a class="btn-cancel" href="/admin/hamlet-members">Hủy sửa</a>
            <?php else: ?>
                <button class="btn-cancel" type="button" data-admin-collapse-close="hamlet-editor-panel">Hủy</button>
            <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <section class="section admin-list-panel">
        <div class="section-head">
            <h1>Danh sách thành viên và Ban Công tác Mặt trận ấp</h1>
            <span class="admin-list-count"><?= count($hamletMembers) ?> bản ghi</span>
        </div>
        <?php if (!empty($importStatus)): ?>
            <div class="notice <?= empty($importStatus['ok']) ? 'error' : '' ?>">
                <strong><?= e($importStatus['message']) ?></strong>
            </div>
        <?php endif; ?>
        <div class="table-wrap admin-list-scroll">
            <table class="admin-data-table admin-hamlet-members-table">
                <thead><tr><th>Chi đoàn, chi hội / ấp</th><th>Đơn vị</th><th>Họ tên</th><th>Ngày sinh</th><th>Chức vụ</th><th>Điện thoại</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($hamletMembers as $member): ?>
                    <tr>
                        <td><?= e($member['hamlet_name']) ?></td>
                        <td><?= e($member['organization_short_name']) ?></td>
                        <td><?= e($member['full_name']) ?></td>
                        <td><?= !empty($member['birth_date']) ? e(date('d/m/Y', strtotime($member['birth_date']))) : '-' ?></td>
                        <td><?= e($member['role']) ?></td>
                        <td><?= e($member['phone']) ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn-edit" href="/admin/hamlet-members?edit=<?= (int)$member['id'] ?>">Sửa</a>
                                <form action="/admin/hamlet-members/delete" method="post" onsubmit="return confirm('Xóa thành viên này?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int)$member['id'] ?>">
                                    <button class="danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
