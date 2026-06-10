<?php if (!$organization || !$chapter): ?>
    <section class="section">
        <h1>Không tìm thấy chi hội</h1>
        <p>Chi hội không tồn tại hoặc đã bị gỡ bỏ.</p>
    </section>
<?php else: ?>
    <?php
    $statisticalMemberCount = (int)$chapter[1];
    $recordedMemberCount = count($members);
    $unitTitle = $profile['unitTitle'] ?? 'Chi hội';
    $unitLabel = $profile['unitLabel'] ?? 'chi hội';
    $memberTitle = $profile['memberTitle'] ?? 'Hội viên';
    $memberLabel = $profile['memberLabel'] ?? 'hội viên';
    ?>
    <div class="breadcrumb">
        <a href="/">Trang chủ</a><span>/</span>
        <a href="/organizations">Tổ chức thành viên</a><span>/</span>
        <a href="/organizations/show?slug=<?= e($organization['slug']) ?>"><?= e($organization['short_name']) ?></a><span>/</span>
        <span><?= e($chapter[0]) ?></span>
    </div>

    <article class="article organization-detail women-chapter-page">
        <header class="subpage-hero">
            <small><?= e($organization['short_name']) ?> | <?= e($unitTitle) ?></small>
            <h1><?= e($chapter[0]) ?></h1>
            <p class="lead"><?= e($unitTitle) ?> trực thuộc <?= e($organization['name']) ?>.</p>
        </header>

        <div class="women-chapter-summary" aria-label="Thống kê <?= e($memberLabel) ?> <?= e($unitLabel) ?>">
            <section class="women-chapter-summary-card highlight">
                <small>Số <?= e($memberLabel) ?> thống kê</small>
                <strong><?= number_format($statisticalMemberCount, 0, ',', '.') ?></strong>
                <span><?= e($memberLabel) ?></span>
            </section>
            <section class="women-chapter-summary-card">
                <small>Hồ sơ chi tiết đã cập nhật</small>
                <strong><?= number_format($recordedMemberCount, 0, ',', '.') ?></strong>
                <span><?= e($memberLabel) ?> có thông tin</span>
            </section>
        </div>

        <div class="organization-detail-members women-chapter-members">
            <div class="section-subhead women-chapter-members-head">
                <h2>Danh sách <?= e($memberLabel) ?></h2>
                <small><?= number_format($recordedMemberCount, 0, ',', '.') ?> hồ sơ</small>
            </div>
            <?php if (empty($members)): ?>
                <p class="empty-state">Chưa cập nhật danh sách chi tiết <?= e($memberLabel) ?> cho <?= e($unitLabel) ?> này.</p>
            <?php else: ?>
                <div class="table-wrap women-chapter-members-table-wrap">
                    <table class="women-chapter-members-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Họ và tên</th>
                                <th>Vai trò</th>
                                <th>Điện thoại</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $index => $member): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><strong><?= e($member['full_name']) ?></strong></td>
                                    <td><?= e($member['role'] ?: '-') ?></td>
                                    <td><?= e($member['phone'] ?: 'Chưa cập nhật') ?></td>
                                    <td><?= e($member['note'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </article>
<?php endif; ?>
