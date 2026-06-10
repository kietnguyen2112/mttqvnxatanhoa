<?php
$isEditing = !empty($editLeader);
$editorOpen = $isEditing;
$presidents = [];
$vicePresidents = [];
$otherLeaders = [];
foreach ($leaders as $leader) {
    $position = mb_strtolower($leader['position'] ?? '');
    if (str_starts_with($position, 'ủy viên ban mttqvn -')) {
        $otherLeaders[] = $leader;
    } elseif (str_contains($position, 'phó')) {
        $vicePresidents[] = $leader;
    } elseif (str_contains($position, 'chủ tịch') || str_contains($position, 'bí thư')) {
        $presidents[] = $leader;
    } else {
        $otherLeaders[] = $leader;
    }
}
$leaderGroups = [
    ['title' => 'Chủ tịch', 'rows' => $presidents, 'role' => 'president', 'rowClass' => 'leader-president-row', 'empty' => 'Chưa có Chủ tịch'],
    ['title' => 'Phó Chủ tịch / Phó cán bộ', 'rows' => $vicePresidents, 'role' => 'vice', 'rowClass' => 'leader-vice-row', 'empty' => 'Chưa có Phó Chủ tịch'],
    ['title' => 'Các cán bộ khác', 'rows' => $otherLeaders, 'role' => 'other', 'rowClass' => 'leader-other-row', 'empty' => 'Chưa có cán bộ khác'],
];
$organizationCounts = [];
foreach ($leaders as $leader) {
    $shortName = (string)($leader['organization_short_name'] ?? 'Khác');
    $organizationCounts[$shortName] = ($organizationCounts[$shortName] ?? 0) + 1;
}
if (!function_exists('admin_leader_initial')) {
    function admin_leader_initial(string $name): string
    {
        return function_exists('mb_substr') ? mb_substr($name, 0, 1, 'UTF-8') : substr($name, 0, 1);
    }
}
?>
<div class="leaders-admin-page">
    <div class="admin-page-grid admin-crud-page leaders-admin-workspace">
    <section class="section admin-editor-panel leaders-editor-panel">
        <div class="admin-content-card">
            <div class="admin-content-card-head">
                <div class="admin-content-card-icon" aria-hidden="true">◎</div>
                <div class="admin-content-card-main">
                    <h1>Cán bộ</h1>
                    <p>Tạo hoặc cập nhật hồ sơ lãnh đạo, cán bộ hội và ảnh đại diện.</p>
                </div>
                <span class="admin-content-status <?= empty($leaders) ? 'is-empty' : 'is-ready' ?>"><?= empty($leaders) ? 'Chưa có nội dung' : 'Đã có nội dung' ?></span>
                <button class="admin-content-action <?= $isEditing ? 'is-editing' : '' ?>" type="button" data-admin-collapse-toggle="leader-editor-panel" aria-controls="leader-editor-panel" aria-expanded="<?= $editorOpen ? 'true' : 'false' ?>">
                    <?= $isEditing ? 'Sửa nội dung' : 'Thêm nội dung' ?>
                </button>
            </div>
            <div id="leader-editor-panel" class="admin-card-form <?= $editorOpen ? 'is-open' : '' ?>" data-admin-collapse-panel>
                <div class="admin-card-form-head">
                    <div>
                        <h2><?= $isEditing ? 'Sửa cán bộ' : 'Thêm cán bộ' ?></h2>
                        <p><?= $isEditing ? 'Đang chỉnh hồ sơ #' . (int)$editLeader['id'] : 'Tạo hồ sơ lãnh đạo hoặc cán bộ hội.' ?></p>
                    </div>
                    <button class="admin-card-close" type="button" data-admin-collapse-close="leader-editor-panel">Đóng</button>
                </div>
                <form class="admin-form" action="<?= $isEditing ? '/admin/organization-leaders/update' : '/admin/organization-leaders' ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?= (int)$editLeader['id'] ?>">
            <?php endif; ?>
            <div class="leaders-form-block">
                <label class="leaders-form-wide">Đơn vị
                    <select name="organization_id" required>
                        <?php foreach ($organizations as $organization): ?>
                            <option value="<?= (int)$organization['id'] ?>" <?= $isEditing && (int)$editLeader['organization_id'] === (int)$organization['id'] ? 'selected' : '' ?>><?= e($organization['short_name']) ?> - <?= e($organization['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Họ và tên<input name="full_name" value="<?= e($editLeader['full_name'] ?? '') ?>" required></label>
                <label>Thứ tự<input type="number" name="sort_order" value="<?= e($editLeader['sort_order'] ?? 0) ?>"></label>
                <label class="leaders-form-wide">Chức vụ<input name="position" value="<?= e($editLeader['position'] ?? '') ?>" required placeholder="Ví dụ: Phó Chủ tịch kiêm Chủ tịch Hội Nông dân"></label>
            </div>

            <div class="leaders-form-block">
                <label>Điện thoại<input name="phone" value="<?= e($editLeader['phone'] ?? '') ?>"></label>
                <label>Email<input name="email" value="<?= e($editLeader['email'] ?? '') ?>"></label>
                <label class="leaders-form-wide">Avatar
                    <span class="leaders-avatar-upload">
                        <?php if (!empty($editLeader['avatar'])): ?>
                            <img src="/<?= e($editLeader['avatar']) ?>" alt="Avatar <?= e($editLeader['full_name'] ?? '') ?>" class="leader-edit-avatar" width="120" height="180" decoding="async">
                        <?php else: ?>
                            <span class="leaders-avatar-placeholder"><?= e(admin_leader_initial((string)($editLeader['full_name'] ?? 'C'))) ?></span>
                        <?php endif; ?>
                        <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp">
                    </span>
                </label>
            </div>

            <div class="leaders-form-actions">
                <button class="btn-submit"><?= $isEditing ? 'Cập nhật cán bộ' : 'Lưu cán bộ' ?></button>
                <?php if ($isEditing): ?>
                    <a class="btn-cancel" href="/admin/leaders">Hủy sửa</a>
                <?php else: ?>
                    <button class="btn-cancel" type="button" data-admin-collapse-close="leader-editor-panel">Hủy</button>
                <?php endif; ?>
            </div>
                </form>
            </div>
        </div>
    </section>

    <section class="section admin-list-panel leaders-list-panel">
        <div class="section-head">
            <div>
                <h1>Danh sách cán bộ</h1>
                <small><?= count($organizationCounts) ?> đơn vị, <?= count($leaders) ?> bản ghi</small>
            </div>
            <div class="leaders-org-pills" aria-label="Số cán bộ theo đơn vị">
                <?php foreach ($organizationCounts as $shortName => $count): ?>
                    <span data-leader-org-pill="<?= e($shortName) ?>"><?= e($shortName) ?> <strong><?= (int)$count ?></strong></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if (!empty($importStatus)): ?>
            <div class="notice <?= empty($importStatus['ok']) ? 'error' : '' ?>">
                <strong><?= e($importStatus['message']) ?></strong>
            </div>
        <?php endif; ?>
        <form class="leaders-filter-bar" data-leaders-filter>
            <label>
                <span>Tìm nhanh</span>
                <input type="search" name="leader_q" placeholder="Tên, chức vụ, số điện thoại..." autocomplete="off" data-leaders-filter-search>
            </label>
            <label>
                <span>Đơn vị</span>
                <select name="leader_org" data-leaders-filter-org>
                    <option value="">Tất cả đơn vị</option>
                    <?php foreach ($organizationCounts as $shortName => $count): ?>
                        <option value="<?= e($shortName) ?>"><?= e($shortName) ?> (<?= (int)$count ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Nhóm chức vụ</span>
                <select name="leader_role" data-leaders-filter-role>
                    <option value="">Tất cả nhóm</option>
                    <?php foreach ($leaderGroups as $group): ?>
                        <option value="<?= e($group['role']) ?>"><?= e($group['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="reset" class="leaders-filter-reset">Xóa lọc</button>
            <output data-leaders-filter-count><?= count($leaders) ?> hồ sơ</output>
        </form>
        <div class="leaders-directory admin-list-scroll">
            <?php foreach ($leaderGroups as $group): ?>
                <section class="leaders-group" aria-label="<?= e($group['title']) ?>" data-leader-group data-leader-role="<?= e($group['role']) ?>">
                    <div class="leaders-group-head">
                        <h2><?= e($group['title']) ?></h2>
                        <span data-leader-group-count><?= count($group['rows']) ?> hồ sơ</span>
                    </div>
                    <?php if (empty($group['rows'])): ?>
                        <p class="leaders-empty-state"><?= e($group['empty']) ?></p>
                    <?php else: ?>
                        <div class="leaders-card-list">
                            <?php foreach ($group['rows'] as $leader): ?>
                                <article
                                    class="leaders-record <?= e($group['rowClass']) ?>"
                                    data-leader-record
                                    data-leader-role="<?= e($group['role']) ?>"
                                    data-leader-org="<?= e($leader['organization_short_name'] ?? '') ?>"
                                    data-leader-search="<?= e(($leader['full_name'] ?? '') . ' ' . ($leader['position'] ?? '') . ' ' . ($leader['phone'] ?? '') . ' ' . ($leader['email'] ?? '') . ' ' . ($leader['organization_short_name'] ?? '') . ' ' . ($leader['organization_name'] ?? '')) ?>"
                                >
                                    <div class="leaders-record-person">
                                        <?php if (!empty($leader['avatar'])): ?>
                                            <img src="/<?= e($leader['avatar']) ?>" alt="Avatar <?= e($leader['full_name']) ?>" class="leader-avatar-img" width="66" height="88" loading="lazy" decoding="async">
                                        <?php else: ?>
                                            <div class="avatar leader-avatar"><?= e(admin_leader_initial($leader['full_name'])) ?></div>
                                        <?php endif; ?>
                                        <div class="leaders-record-main">
                                            <div class="leaders-record-topline">
                                                <span class="leaders-org-badge"><?= e($leader['organization_short_name']) ?></span>
                                                <span>STT <?= (int)$leader['sort_order'] ?></span>
                                            </div>
                                            <h3><?= e($leader['full_name']) ?></h3>
                                            <p><?= e($leader['position']) ?></p>
                                        </div>
                                    </div>
                                    <div class="leaders-record-meta">
                                        <?php if (!empty($leader['phone'])): ?>
                                            <span><?= e($leader['phone']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($leader['email'])): ?>
                                            <span><?= e($leader['email']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="table-actions leaders-record-actions">
                                        <a class="btn-edit" href="/admin/leaders?edit=<?= (int)$leader['id'] ?>">Sửa</a>
                                        <form action="/admin/organization-leaders/delete" method="post" onsubmit="return confirm('Xóa cán bộ này?')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$leader['id'] ?>">
                                            <button class="danger">Xóa</button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
            <p class="leaders-empty-state leaders-filter-empty" data-leaders-filter-empty hidden>Không tìm thấy cán bộ phù hợp với bộ lọc.</p>
        </div>
    </section>
    </div>
</div>
