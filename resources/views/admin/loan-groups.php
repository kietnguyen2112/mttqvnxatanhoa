<?php
$isEditing = !empty($editGroup);
$editorOpen = $isEditing;
?>
<div class="admin-page-grid admin-crud-page">
    <section class="section admin-editor-panel">
        <div class="admin-content-card">
            <div class="admin-content-card-head">
                <div class="admin-content-card-icon" aria-hidden="true">☷</div>
                <div class="admin-content-card-main">
                    <h1>Tổ vay vốn</h1>
                    <p>Quản lý tổ vay, ấp phụ trách, tổ trưởng và nguồn vốn.</p>
                </div>
                <span class="admin-content-status <?= empty($loanGroups) ? 'is-empty' : 'is-ready' ?>"><?= empty($loanGroups) ? 'Chưa có nội dung' : 'Đã có nội dung' ?></span>
                <button class="admin-content-action <?= $isEditing ? 'is-editing' : '' ?>" type="button" data-admin-collapse-toggle="loan-group-editor-panel" aria-controls="loan-group-editor-panel" aria-expanded="<?= $editorOpen ? 'true' : 'false' ?>">
                    <?= $isEditing ? 'Sửa nội dung' : 'Thêm nội dung' ?>
                </button>
            </div>
            <div id="loan-group-editor-panel" class="admin-card-form <?= $editorOpen ? 'is-open' : '' ?>" data-admin-collapse-panel>
                <div class="admin-card-form-head">
                    <div>
                        <h2><?= $isEditing ? 'Sửa tổ vay vốn' : 'Thêm tổ vay vốn' ?></h2>
                        <p><?= $isEditing ? 'Đang chỉnh tổ #' . (int)$editGroup['id'] : 'Nhập thông tin tổ vay vốn mới.' ?></p>
                    </div>
                    <button class="admin-card-close" type="button" data-admin-collapse-close="loan-group-editor-panel">Đóng</button>
                </div>
                <form class="admin-form" action="<?= $isEditing ? '/admin/loan-groups/update' : '/admin/loan-groups' ?>" method="post">
            <?= csrf_field() ?>
            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?= (int)$editGroup['id'] ?>">
            <?php endif; ?>
            <label>Hội quản lý
                <select name="organization_id" required>
                    <?php foreach ($memberOrganizations as $organization): ?>
                        <option value="<?= (int)$organization['id'] ?>" <?= $isEditing && (int)$editGroup['organization_id'] === (int)$organization['id'] ? 'selected' : '' ?>><?= e($organization['short_name']) ?> - <?= e($organization['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Ấp phụ trách<input name="hamlet_name" value="<?= e($editGroup['hamlet_name'] ?? '') ?>" required></label>
            <label>Tên tổ vay vốn<input name="name" value="<?= e($editGroup['name'] ?? '') ?>" required></label>
            <label>Tổ trưởng<input name="leader_name" value="<?= e($editGroup['leader_name'] ?? '') ?>" required></label>
            <label>Điện thoại tổ trưởng<input name="leader_phone" value="<?= e($editGroup['leader_phone'] ?? '') ?>"></label>
            <label>Số khách hàng<input type="number" name="customer_count" min="0" step="1" value="<?= e($editGroup['customer_count'] ?? 0) ?>"></label>
            <label>Nguồn vốn / ngân hàng<input name="fund_source" value="<?= e($editGroup['fund_source'] ?? '') ?>"></label>
            <label>Dư nợ<input type="number" name="outstanding_amount" min="0" step="1000" value="<?= e($editGroup['outstanding_amount'] ?? 0) ?>"></label>
            <label>Tiền gửi<input type="number" name="savings_amount" min="0" step="1000" value="<?= e($editGroup['savings_amount'] ?? 0) ?>"></label>
            <label>Nợ quá hạn<input type="number" name="overdue_amount" min="0" step="1000" value="<?= e($editGroup['overdue_amount'] ?? 0) ?>"></label>
            <label>Xếp loại tổ<input name="rating" value="<?= e($editGroup['rating'] ?? '') ?>" placeholder="Ví dụ: Tốt, Khá, Trung bình"></label>
            <label>Ghi chú<input name="note" value="<?= e($editGroup['note'] ?? '') ?>"></label>
            <button class="btn-submit"><?= $isEditing ? 'Cập nhật tổ vay vốn' : 'Lưu tổ vay vốn' ?></button>
            <?php if ($isEditing): ?>
                <a class="btn-cancel" href="/admin/loan-groups">Hủy sửa</a>
            <?php else: ?>
                <button class="btn-cancel" type="button" data-admin-collapse-close="loan-group-editor-panel">Hủy</button>
            <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <section class="section admin-list-panel">
        <div class="section-head">
            <h1>Danh sách tổ vay vốn</h1>
            <span class="admin-list-count"><?= count($loanGroups) ?> bản ghi</span>
        </div>
        <?php if (!empty($importStatus)): ?>
            <div class="notice <?= empty($importStatus['ok']) ? 'error' : '' ?>">
                <strong><?= e($importStatus['message']) ?></strong>
            </div>
        <?php endif; ?>
        <div class="table-wrap admin-list-scroll">
            <table class="admin-data-table admin-loan-groups-table">
                <thead><tr><th>Tổ</th><th>Ấp</th><th>Hội</th><th>Tổ trưởng</th><th>KH</th><th>Dư nợ</th><th>Tiền gửi</th><th>Nợ quá hạn</th><th>Xếp loại</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($loanGroups as $group): ?>
                    <?php
                        $groupMemberCount = count($group['members']);
                        $groupOutstandingAmount = 0.0;
                        $groupOverdueAmount = 0.0;
                        foreach (($group['members'] ?? []) as $member) {
                            $groupOutstandingAmount += (float)($member['outstanding_amount'] ?? $member['loan_amount'] ?? 0);
                            $groupOverdueAmount += (float)($member['overdue_amount'] ?? 0);
                        }
                        if ($groupMemberCount === 0) {
                            $groupMemberCount = (int)($group['customer_count'] ?? 0);
                            $groupOutstandingAmount = (float)($group['outstanding_amount'] ?? 0);
                            $groupOverdueAmount = (float)($group['overdue_amount'] ?? 0);
                        }
                    ?>
                    <tr>
                        <td><a href="/loan-groups/show?id=<?= (int)$group['id'] ?>"><?= e($group['name']) ?></a></td>
                        <td><?= e($group['hamlet_name']) ?></td>
                        <td><?= e($group['organization_short_name']) ?></td>
                        <td><?= e($group['leader_name']) ?></td>
                        <td><?= number_format($groupMemberCount, 0, ',', '.') ?></td>
                        <td><?= number_format($groupOutstandingAmount, 0, ',', '.') ?> đ</td>
                        <td><?= number_format((float)($group['savings_amount'] ?? 0), 0, ',', '.') ?> đ</td>
                        <td><?= number_format($groupOverdueAmount, 0, ',', '.') ?> đ</td>
                        <td><?= e($group['rating'] ?? '') ?: '-' ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn-edit" href="/admin/loan-groups?edit=<?= (int)$group['id'] ?>">Sửa</a>
                                <form action="/admin/loan-groups/delete" method="post" onsubmit="return confirm('Xóa tổ vay vốn này và toàn bộ thành viên?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int)$group['id'] ?>">
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
