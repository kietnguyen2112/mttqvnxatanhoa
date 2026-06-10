<?php
$isEditing = !empty($editMember);
$editorOpen = $isEditing;
$hasLoanMemberDetails = !empty($loanMembers);
?>
<div class="admin-page-grid admin-crud-page">
    <section class="section admin-editor-panel">
        <div class="admin-content-card">
            <div class="admin-content-card-head">
                <div class="admin-content-card-icon" aria-hidden="true">◇</div>
                <div class="admin-content-card-main">
                    <h1>Thành viên tổ vay vốn</h1>
                    <p>Quản lý thành viên, số tiền vay, dư nợ, nợ quá hạn và mục đích vay.</p>
                </div>
                <span class="admin-content-status <?= !$hasLoanMemberDetails ? 'is-empty' : 'is-ready' ?>"><?= !$hasLoanMemberDetails ? 'Chưa có danh sách chi tiết' : 'Đã có nội dung' ?></span>
                <button class="admin-content-action <?= $isEditing ? 'is-editing' : '' ?>" type="button" data-admin-collapse-toggle="loan-member-editor-panel" aria-controls="loan-member-editor-panel" aria-expanded="<?= $editorOpen ? 'true' : 'false' ?>">
                    <?= $isEditing ? 'Sửa nội dung' : 'Thêm nội dung' ?>
                </button>
            </div>
            <div id="loan-member-editor-panel" class="admin-card-form <?= $editorOpen ? 'is-open' : '' ?>" data-admin-collapse-panel>
                <div class="admin-card-form-head">
                    <div>
                        <h2><?= $isEditing ? 'Sửa thành viên tổ vay vốn' : 'Thêm thành viên tổ vay vốn' ?></h2>
                        <p><?= $isEditing ? 'Đang chỉnh thành viên #' . (int)$editMember['id'] : 'Nhập thông tin thành viên vay vốn mới.' ?></p>
                    </div>
                    <button class="admin-card-close" type="button" data-admin-collapse-close="loan-member-editor-panel">Đóng</button>
                </div>
                <form class="admin-form" action="<?= $isEditing ? '/admin/loan-members/update' : '/admin/loan-members' ?>" method="post">
            <?= csrf_field() ?>
            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?= (int)$editMember['id'] ?>">
            <?php endif; ?>
            <label>Tổ vay vốn
                <select name="loan_group_id" required>
                    <?php foreach ($loanGroups as $group): ?>
                        <option value="<?= (int)$group['id'] ?>" <?= $isEditing && (int)$editMember['loan_group_id'] === (int)$group['id'] ? 'selected' : '' ?>><?= e($group['name']) ?> - <?= e($group['hamlet_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Họ và tên<input name="full_name" value="<?= e($editMember['full_name'] ?? '') ?>" required></label>
            <label>Vai trò<input name="role" value="<?= e($editMember['role'] ?? 'Thành viên') ?>" required></label>
            <label>Điện thoại<input name="phone" value="<?= e($editMember['phone'] ?? '') ?>"></label>
            <label>Số tiền vay<input type="number" name="loan_amount" min="0" step="100000" value="<?= e($editMember['loan_amount'] ?? '') ?>"></label>
            <label>Dư nợ<input type="number" name="outstanding_amount" min="0" step="1000" value="<?= e($editMember['outstanding_amount'] ?? $editMember['loan_amount'] ?? '') ?>"></label>
            <label>Nợ quá hạn (đ)<input type="number" name="overdue_amount" min="0" step="1000" value="<?= e($editMember['overdue_amount'] ?? 0) ?>"></label>
            <label>Mục đích vay<input name="purpose" value="<?= e($editMember['purpose'] ?? '') ?>"></label>
            <label>Ghi chú<input name="note" value="<?= e($editMember['note'] ?? '') ?>"></label>
            <label>Thứ tự<input type="number" name="sort_order" value="<?= e($editMember['sort_order'] ?? 0) ?>"></label>
            <button class="btn-submit"><?= $isEditing ? 'Cập nhật thành viên tổ' : 'Lưu thành viên tổ' ?></button>
            <?php if ($isEditing): ?>
                <a class="btn-cancel" href="/admin/loan-members">Hủy sửa</a>
            <?php else: ?>
                <button class="btn-cancel" type="button" data-admin-collapse-close="loan-member-editor-panel">Hủy</button>
            <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <section class="section admin-list-panel">
        <div class="section-head">
            <h1>Danh sách thành viên tổ vay vốn</h1>
            <span class="admin-list-count"><?= count($loanMembers) ?> bản ghi</span>
        </div>
        <?php if (!empty($importStatus)): ?>
            <div class="notice <?= empty($importStatus['ok']) ? 'error' : '' ?>">
                <strong><?= e($importStatus['message']) ?></strong>
            </div>
        <?php endif; ?>
        <?php if (!$hasLoanMemberDetails): ?>
            <p class="empty-state">Chưa có danh sách từng thành viên vay vốn. Bảng dưới đây hiển thị số khách hàng và dư nợ tổng hợp theo từng tổ vay vốn đã nhập từ Excel.</p>
            <div class="table-wrap admin-list-scroll">
                <table class="admin-data-table admin-loan-members-table">
                    <thead><tr><th>Tổ / Ấp</th><th>Hội</th><th>Khách hàng</th><th>Dư nợ</th><th>Tiền gửi</th><th>Nợ quá hạn</th><th>Xếp loại</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($loanGroups as $group): ?>
                        <tr>
                            <td class="admin-primary-cell">
                                <strong><?= e($group['name']) ?></strong>
                                <small><?= e($group['hamlet_name']) ?></small>
                            </td>
                            <td><?= e($group['organization_short_name'] ?? '') ?></td>
                            <td><?= number_format((int)($group['customer_count'] ?? 0), 0, ',', '.') ?></td>
                            <td><?= number_format((float)($group['outstanding_amount'] ?? 0), 0, ',', '.') ?> đ</td>
                            <td><?= number_format((float)($group['savings_amount'] ?? 0), 0, ',', '.') ?> đ</td>
                            <td><?= number_format((float)($group['overdue_amount'] ?? 0), 0, ',', '.') ?> đ</td>
                            <td><?= e($group['rating'] ?? '') ?: '-' ?></td>
                            <td><a class="btn-edit" href="/admin/loan-groups?edit=<?= (int)$group['id'] ?>">Sửa tổ</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-wrap admin-list-scroll">
                <table class="admin-data-table admin-loan-members-table">
                    <thead><tr><th>Tổ / Ấp</th><th>Họ tên</th><th>Số tiền vay</th><th>Dư nợ</th><th>Nợ quá hạn</th><th>Mục đích</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($loanMembers as $member): ?>
                        <tr>
                            <td class="admin-primary-cell">
                                <strong><?= e($member['loan_group_name']) ?></strong>
                                <small><?= e($member['hamlet_name']) ?></small>
                            </td>
                            <td><?= e($member['full_name']) ?></td>
                            <td><?= number_format((float)$member['loan_amount'], 0, ',', '.') ?> đ</td>
                            <td><?= number_format((float)($member['outstanding_amount'] ?? $member['loan_amount']), 0, ',', '.') ?> đ</td>
                            <td><?= number_format((float)($member['overdue_amount'] ?? 0), 0, ',', '.') ?> đ</td>
                            <td><?= e($member['purpose']) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn-edit" href="/admin/loan-members?edit=<?= (int)$member['id'] ?>">Sửa</a>
                                    <form action="/admin/loan-members/delete" method="post" onsubmit="return confirm('Xóa thành viên tổ vay vốn này?')">
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
        <?php endif; ?>
    </section>
</div>
