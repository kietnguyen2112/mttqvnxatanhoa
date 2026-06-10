<div class="breadcrumb"><a href="/">Trang chủ</a><span>/</span><span>Tổ vay vốn</span></div>

<?php
$overviewStats = $overviewStats ?? [
    'organization_count' => count($organizations),
    'group_count' => 0,
    'member_count' => 0,
    'outstanding_amount' => 0.0,
    'overdue_amount' => 0.0,
];
$selectedStats = $selectedOrganization
    ? ($loanProgramStatsByOrganizationId[(int)$selectedOrganization['id']] ?? [])
    : [];
$selectedOrganizationLogo = $selectedOrganization
    ? organization_logo_path((string)($selectedOrganization['slug'] ?? ''))
    : '';
$overviewOverdueRate = (float)$overviewStats['overdue_amount'] > 0 && (float)$overviewStats['outstanding_amount'] > 0
    ? number_format(((float)$overviewStats['overdue_amount'] / (float)$overviewStats['outstanding_amount']) * 100, 2, ',', '.') . '%'
    : '';
?>

<section class="loan-dashboard" data-loan-browser>
    <header class="loan-page-intro">
        <div>
            <small class="portal-eyebrow">Chương trình hỗ trợ</small>
            <h1>Tổ vay vốn</h1>
            <p>Theo dõi các tổ vay vốn, thành viên và dư nợ được các tổ chức thành viên quản lý trên địa bàn xã.</p>
        </div>
        <div class="loan-intro-signal">
            <small>Tổng quan chương trình</small>
            <strong><?= (int)$overviewStats['group_count'] ?> tổ đang theo dõi</strong>
            <?php if ($overviewOverdueRate !== ''): ?>
                <span><b><?= e($overviewOverdueRate) ?></b> nợ quá hạn / dư nợ</span>
            <?php else: ?>
                <span class="healthy">Không ghi nhận nợ quá hạn</span>
            <?php endif; ?>
        </div>
    </header>
    <div class="loan-summary-grid">
        <article class="loan-summary-card">
            <small>Tổ vay vốn</small>
            <strong><?= (int)$overviewStats['group_count'] ?></strong>
            <span>tổ đang theo dõi</span>
        </article>
        <article class="loan-summary-card">
            <small>Thành viên vay vốn</small>
            <strong><?= (int)$overviewStats['member_count'] ?></strong>
            <span>hồ sơ thành viên</span>
        </article>
        <article class="loan-summary-card primary">
            <small>Tổng dư nợ</small>
            <strong><?= number_format((float)$overviewStats['outstanding_amount'], 0, ',', '.') ?> đ</strong>
            <span>toàn bộ chương trình</span>
        </article>
        <article class="loan-summary-card">
            <small>Tổng tiền gửi</small>
            <strong><?= number_format((float)($overviewStats['savings_amount'] ?? 0), 0, ',', '.') ?> đ</strong>
            <span>theo dữ liệu ủy thác</span>
        </article>
    </div>

    <div class="loan-dashboard-workspace">
        <aside class="loan-filter-panel">
            <div class="loan-panel-title">
                <h2>Hội quản lý</h2>
                <small>Chọn hội để xem danh sách tổ</small>
            </div>
            <nav class="loan-filter-list" aria-label="Lọc theo hội quản lý" data-loan-filter-list>
                <?php foreach ($organizations as $organization): ?>
                    <?php
                    $organizationId = (int)$organization['id'];
                    $isSelected = $selectedOrganizationId === $organizationId;
                    $organizationLogo = organization_logo_path((string)($organization['slug'] ?? ''));
                    $programStats = $loanProgramStatsByOrganizationId[$organizationId] ?? [
                        'tong_to' => '0 tổ',
                        'thanh_vien' => '0 người',
                        'du_no' => '0 đ',
                        'no_qua_han_amount' => 0.0,
                        'no_qua_han' => '',
                        'no_qua_han_rate' => '',
                    ];
                    ?>
                    <a
                        class="loan-filter-card <?= $isSelected ? 'active' : '' ?><?= $organizationLogo !== '' ? ' has-logo' : '' ?>"
                        href="/loan-groups?organization_id=<?= $organizationId ?>"
                        data-loan-filter-link
                        <?= $organizationLogo !== '' ? 'style="--loan-filter-card-logo: url(\'/' . e($organizationLogo) . '\');"' : '' ?>
                        <?= $isSelected ? 'aria-current="true"' : '' ?>
                    >
                        <span class="loan-filter-head">
                            <!-- <span class="loan-filter-badge"><?= e($organization['short_name']) ?></span> -->
                            <?php if ($organizationLogo !== ''): ?>
                                <img src="/<?= e($organizationLogo) ?>" alt="" class="loan-filter-logo" width="18" height="18" loading="lazy" decoding="async">
                            <?php endif; ?>
                        </span>
                        <strong><?= e($organization['name']) ?></strong>
                        <span class="loan-filter-meta"><?= e($programStats['tong_to']) ?> · <?= e($programStats['thanh_vien']) ?></span>
                        <span class="loan-filter-balance"><?= e($programStats['du_no']) ?></span>
                        <?php if ((float)($programStats['no_qua_han_amount'] ?? 0) > 0): ?>
                            <small class="loan-filter-overdue">
                                NQH <?= e($programStats['no_qua_han']) ?>
                                <?php if (!empty($programStats['no_qua_han_rate'])): ?>
                                    <span><?= e($programStats['no_qua_han_rate']) ?> dư nợ</span>
                                <?php endif; ?>
                            </small>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <section class="loan-result-panel" data-loan-result aria-live="polite" aria-busy="false">
            <?php if (!$selectedOrganization): ?>
                <p class="empty-state">Chưa có hội quản lý để hiển thị dữ liệu tổ vay vốn.</p>
            <?php else: ?>
                <header class="loan-result-head">
                    <div>
                        <div class="loan-result-org-head">
                            <!-- <span class="organization-badge"><?= e($selectedOrganization['short_name']) ?></span> -->
                             <?php if ($selectedOrganizationLogo !== ''): ?>
                                <img src="/<?= e($selectedOrganizationLogo) ?>" alt="" class="loan-result-org-logo" width="20" height="20" loading="lazy" decoding="async">
                            <?php endif; ?> 
                        </div>
                        <h2 class="loan-result-org-title"><?= e($selectedOrganization['name']) ?></h2>
                        <?php if (!empty($selectedOrganization['leaders'][0])): ?>
                            <p>Phụ trách: <strong><?= e($selectedOrganization['leaders'][0]['full_name']) ?></strong></p>
                        <?php endif; ?>
                    </div>
                    <span class="loan-result-count"><?= count($loanGroups) ?> tổ vay vốn</span>
                </header>

                <div class="loan-selected-summary">
                    <div><small>Số tổ</small><strong><?= e($selectedStats['tong_to'] ?? '0 tổ') ?></strong></div>
                    <div><small>Thành viên</small><strong><?= e($selectedStats['thanh_vien'] ?? '0 người') ?></strong></div>
                    <div><small>Dư nợ</small><strong><?= e($selectedStats['du_no'] ?? '0 đ') ?></strong></div>
                    <div><small>Tiền gửi</small><strong><?= e($selectedStats['tien_gui'] ?? '0 đ') ?></strong></div>
                    <div class="<?= (float)($selectedStats['no_qua_han_amount'] ?? 0) > 0 ? 'overdue' : '' ?>">
                        <small>Nợ quá hạn</small>
                        <strong><?= !empty($selectedStats['no_qua_han']) ? e($selectedStats['no_qua_han']) : '0 đ' ?></strong>
                        <?php if (!empty($selectedStats['no_qua_han_rate'])): ?>
                            <span class="loan-overdue-rate"><?= e($selectedStats['no_qua_han_rate']) ?> tổng dư nợ</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($loanGroups)): ?>
                    <p class="empty-state">Hội này chưa có tổ vay vốn nào.</p>
                <?php else: ?>
                    <?php
                    $loanGroupRows = [];
                    foreach ($loanGroups as $index => $group) {
                        $groupOutstanding = 0.0;
                        $groupOverdue = 0.0;
                        $groupMemberCount = count($group['members']);
                        foreach (($group['members'] ?? []) as $member) {
                            $groupOutstanding += (float)($member['outstanding_amount'] ?? $member['loan_amount'] ?? 0);
                            $groupOverdue += (float)($member['overdue_amount'] ?? 0);
                        }
                        if ($groupMemberCount === 0) {
                            $groupMemberCount = (int)($group['customer_count'] ?? $group['member_count'] ?? 0);
                            $groupOutstanding = (float)($group['outstanding_amount'] ?? 0);
                            $groupOverdue = (float)($group['overdue_amount'] ?? 0);
                        }

                        $loanGroupRows[] = [
                            'index' => $index + 1,
                            'id' => (int)$group['id'],
                            'name' => (string)$group['name'],
                            'hamlet_name' => (string)$group['hamlet_name'],
                            'fund_source' => (string)($group['fund_source'] ?: 'Chưa cập nhật nguồn vốn'),
                            'leader_name' => (string)$group['leader_name'],
                            'leader_phone' => (string)($group['leader_phone'] ?: 'Chưa cập nhật'),
                            'member_count' => $groupMemberCount,
                            'outstanding_amount' => $groupOutstanding,
                            'savings_amount' => (float)($group['savings_amount'] ?? 0),
                            'overdue_amount' => $groupOverdue,
                            'rating' => (string)(($group['rating'] ?? '-') ?: '-'),
                        ];
                    }
                    ?>
                    <div class="table-wrap loan-dashboard-table-wrap" data-loan-table-wrap>
                        <table class="loan-dashboard-table" data-loan-table>
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tổ vay vốn</th>
                                    <th>Tổ trưởng</th>
                                    <th>KH</th>
                                    <th>Dư nợ</th>
                                    <th>Tiền gửi</th>
                                    <th>Nợ quá hạn</th>
                                    <th>Xếp loại</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($loanGroupRows as $groupRow): ?>
                                    <tr>
                                        <td data-label="STT"><?= $groupRow['index'] ?></td>
                                        <td class="loan-group-name-cell" data-label="Tổ vay vốn">
                                            <strong><?= e($groupRow['name']) ?></strong>
                                            <small><?= e($groupRow['hamlet_name']) ?> · <?= e($groupRow['fund_source']) ?></small>
                                        </td>
                                        <td class="loan-group-leader-cell" data-label="Tổ trưởng">
                                            <strong><?= e($groupRow['leader_name']) ?></strong>
                                            <small><?= e($groupRow['leader_phone']) ?></small>
                                        </td>
                                        <td data-label="KH"><?= number_format($groupRow['member_count'], 0, ',', '.') ?></td>
                                        <td class="loan-money-cell" data-label="Dư nợ"><?= number_format($groupRow['outstanding_amount'], 0, ',', '.') ?> đ</td>
                                        <td class="loan-money-cell" data-label="Tiền gửi"><?= number_format($groupRow['savings_amount'], 0, ',', '.') ?> đ</td>
                                        <td class="<?= $groupRow['overdue_amount'] > 0 ? 'loan-overdue-cell' : 'loan-money-cell' ?>" data-label="Nợ quá hạn">
                                            <?= $groupRow['overdue_amount'] > 0 ? number_format($groupRow['overdue_amount'], 0, ',', '.') . ' đ' : '-' ?>
                                        </td>
                                        <td data-label="Xếp loại"><?= e($groupRow['rating']) ?></td>
                                        <td data-label="Chi tiết"><a class="loan-row-action" href="/loan-groups/show?id=<?= $groupRow['id'] ?>">Xem</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="loan-mobile-list" data-loan-mobile-list aria-label="Danh sách tổ vay vốn dạng thẻ">
                        <?php foreach ($loanGroupRows as $cardIndex => $groupRow): ?>
                            <article class="loan-mobile-card" data-loan-mobile-card>
                                <header class="loan-mobile-card-head">
                                    <span>Tổ <?= number_format($groupRow['index'], 0, ',', '.') ?></span>
                                    <strong><?= e($groupRow['rating']) ?></strong>
                                </header>
                                <h3><?= e($groupRow['name']) ?></h3>
                                <p><?= e($groupRow['hamlet_name']) ?> · <?= e($groupRow['fund_source']) ?></p>
                                <dl class="loan-mobile-meta">
                                    <div>
                                        <dt>Tổ trưởng</dt>
                                        <dd><?= e($groupRow['leader_name']) ?></dd>
                                    </div>
                                    <div>
                                        <dt>Điện thoại</dt>
                                        <dd><?= e($groupRow['leader_phone']) ?></dd>
                                    </div>
                                    <div>
                                        <dt>Thành viên</dt>
                                        <dd><?= number_format($groupRow['member_count'], 0, ',', '.') ?></dd>
                                    </div>
                                </dl>
                                <div class="loan-mobile-money">
                                    <div>
                                        <small>Dư nợ</small>
                                        <strong><?= number_format($groupRow['outstanding_amount'], 0, ',', '.') ?> đ</strong>
                                    </div>
                                    <div>
                                        <small>Tiền gửi</small>
                                        <strong><?= number_format($groupRow['savings_amount'], 0, ',', '.') ?> đ</strong>
                                    </div>
                                    <div class="<?= $groupRow['overdue_amount'] > 0 ? 'overdue' : '' ?>">
                                        <small>Nợ quá hạn</small>
                                        <strong><?= $groupRow['overdue_amount'] > 0 ? number_format($groupRow['overdue_amount'], 0, ',', '.') . ' đ' : 'Không có' ?></strong>
                                    </div>
                                </div>
                                <a class="loan-mobile-action" href="/loan-groups/show?id=<?= $groupRow['id'] ?>">Xem chi tiết</a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <footer class="loan-table-footer" data-loan-pagination data-page-size="8">
                        <p data-loan-page-status>Hiển thị đầy đủ <?= count($loanGroups) ?> tổ vay vốn</p>
                        <nav class="loan-page-actions" data-loan-page-controls aria-label="Phân trang danh sách tổ vay vốn" hidden>
                            <button type="button" data-loan-page-prev aria-label="Trang trước">Trước</button>
                            <span class="loan-page-numbers" data-loan-page-numbers></span>
                            <button type="button" data-loan-page-next aria-label="Trang sau">Sau</button>
                        </nav>
                    </footer>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</section>
