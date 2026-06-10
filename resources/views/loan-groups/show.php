<?php if (!$loanGroup): ?>
    <section class="section">
        <h1>Không tìm thấy tổ vay vốn</h1>
        <p>Tổ vay vốn không tồn tại hoặc đã bị xóa.</p>
    </section>
<?php else: ?>
    <?php
    $members = $loanGroup['members'] ?? [];
    $totalLoanAmount = 0.0;
    $totalOutstandingAmount = 0.0;
    $overdueAmount = 0.0;
    foreach ($members as $member) {
        $loanAmount = (float)($member['loan_amount'] ?? 0);
        $outstandingAmount = (float)($member['outstanding_amount'] ?? $loanAmount);
        $totalLoanAmount += $loanAmount;
        $totalOutstandingAmount += $outstandingAmount;
        $overdueAmount += (float)($member['overdue_amount'] ?? 0);
    }
    $displayMemberCount = count($members);
    if ($displayMemberCount === 0) {
        $displayMemberCount = (int)($loanGroup['customer_count'] ?? $loanGroup['member_count'] ?? 0);
        $totalLoanAmount = (float)($loanGroup['outstanding_amount'] ?? 0);
        $totalOutstandingAmount = (float)($loanGroup['outstanding_amount'] ?? 0);
        $overdueAmount = (float)($loanGroup['overdue_amount'] ?? 0);
    }
    $recoveredAmount = max(0, $totalLoanAmount - $totalOutstandingAmount);
    $hasOverdue = $overdueAmount > 0;
    $overdueRate = $hasOverdue && $totalOutstandingAmount > 0
        ? ($overdueAmount / $totalOutstandingAmount) * 100
        : null;
    $displayNote = trim((string)($loanGroup['note'] ?? ''));
    $displayNote = preg_replace('/(?:^|;\s*)NQH\s*:\s*[^;]*/iu', '', $displayNote) ?? $displayNote;
    $displayNote = trim($displayNote, " \t\n\r\0\x0B;");
    $memberRows = [];
    foreach ($members as $member) {
        $memberRows[] = [
            'full_name' => (string)$member['full_name'],
            'role' => (string)$member['role'],
            'phone' => (string)($member['phone'] ?: 'Chưa cập nhật'),
            'loan_amount' => (float)$member['loan_amount'],
            'outstanding_amount' => (float)($member['outstanding_amount'] ?? $member['loan_amount']),
            'overdue_amount' => (float)($member['overdue_amount'] ?? 0),
            'purpose' => (string)($member['purpose'] ?: '-'),
            'note' => (string)($member['note'] ?: '-'),
        ];
    }
    ?>
    <div class="breadcrumb"><a href="/">Trang chủ</a><span>/</span><a href="/loan-groups">Tổ vay vốn</a><span>/</span><span><?= e($loanGroup['name']) ?></span></div>

    <article class="article organization-detail loan-group-detail">
        <header class="subpage-hero">
            <small><?= e($loanGroup['organization_short_name']) ?> | <?= e($loanGroup['hamlet_name']) ?></small>
            <h1><?= e($loanGroup['name']) ?></h1>
            <p class="lead">Tổ thuộc <?= e($loanGroup['organization_name']) ?> quản lý. Nguồn vốn: <?= e($loanGroup['fund_source']) ?>.</p>
        </header>

        <section class="loan-detail-leader">
            <div class="loan-detail-leader-profile">
                <div class="avatar"><?= e(function_exists('mb_substr') ? mb_substr($loanGroup['leader_name'], 0, 1, 'UTF-8') : substr($loanGroup['leader_name'], 0, 1)) ?></div>
                <div>
                    <h3><?= e($loanGroup['leader_name']) ?></h3>
                    <p>Tổ trưởng</p>
                    <small><?= e($loanGroup['leader_phone'] ?: 'Chưa cập nhật số điện thoại') ?></small>
                </div>
            </div>
            <?php if ($displayNote !== ''): ?>
                <p class="loan-detail-note"><?= e($displayNote) ?></p>
            <?php endif; ?>
        </section>

        <div class="loan-member-stats" aria-label="Thống kê thành viên tổ vay vốn">
            <section class="loan-member-stat">
                <small>Khách hàng</small>
                <strong><?= number_format($displayMemberCount, 0, ',', '.') ?></strong>
                <span>theo dữ liệu ủy thác</span>
            </section>
            <section class="loan-member-stat">
                <small>Tổng vốn vay</small>
                <strong><?= number_format($totalLoanAmount, 0, ',', '.') ?> đ</strong>
                <span>ban đầu</span>
            </section>
            <section class="loan-member-stat highlight">
                <small>Tổng dư nợ</small>
                <strong><?= number_format($totalOutstandingAmount, 0, ',', '.') ?> đ</strong>
                <span>hiện tại</span>
            </section>
            <section class="loan-member-stat success">
                <small>Tiền gửi</small>
                <strong><?= number_format((float)($loanGroup['savings_amount'] ?? 0), 0, ',', '.') ?> đ</strong>
                <span>theo dữ liệu ủy thác</span>
            </section>
            <?php if (!empty($loanGroup['rating'])): ?>
                <section class="loan-member-stat">
                    <small>Xếp loại tổ</small>
                    <strong><?= e($loanGroup['rating']) ?></strong>
                    <span>đánh giá hiện có</span>
                </section>
            <?php endif; ?>
            <?php if ($hasOverdue): ?>
                <section class="loan-member-stat overdue">
                    <small>Nợ quá hạn</small>
                    <strong><?= number_format($overdueAmount, 0, ',', '.') ?> đ</strong>
                    <span>
                        <?= $overdueRate !== null ? number_format($overdueRate, 2, ',', '.') . '% tổng dư nợ' : 'Tổ có nợ quá hạn' ?>
                    </span>
                </section>
            <?php endif; ?>
        </div>

        <div class="loan-member-heading">
            <h2>Thành viên tổ vay vốn</h2>
            <small><?= count($members) > 0 ? count($members) . ' thành viên' : 'Chưa có danh sách thành viên chi tiết' ?></small>
        </div>
        <div class="table-wrap loan-members-table-wrap">
            <table class="loan-members-table">
                <thead>
                    <tr>
                        <th>Họ và tên</th>
                        <th>Vai trò</th>
                        <th>Điện thoại</th>
                        <th>Số tiền vay ban đầu</th>
                        <th>Dư nợ</th>
                        <th>Nợ quá hạn</th>
                        <th>Mục đích</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td class="loan-members-empty" colspan="8">Chưa có thành viên trong tổ vay vốn này.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($memberRows as $memberRow): ?>
                    <tr>
                        <td data-label="Họ tên"><strong><?= e($memberRow['full_name']) ?></strong></td>
                        <td data-label="Vai trò"><?= e($memberRow['role']) ?></td>
                        <td data-label="Điện thoại"><?= e($memberRow['phone']) ?></td>
                        <td data-label="Số tiền vay ban đầu"><?= number_format($memberRow['loan_amount'], 0, ',', '.') ?> đ</td>
                        <td data-label="Dư nợ"><?= number_format($memberRow['outstanding_amount'], 0, ',', '.') ?> đ</td>
                        <td data-label="Nợ quá hạn" class="<?= $memberRow['overdue_amount'] > 0 ? 'loan-overdue-cell' : '' ?>"><?= number_format($memberRow['overdue_amount'], 0, ',', '.') ?> đ</td>
                        <td data-label="Mục đích"><?= e($memberRow['purpose']) ?></td>
                        <td data-label="Ghi chú"><?= e($memberRow['note']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($memberRows)): ?>
            <p class="loan-member-mobile-empty">Chưa có thành viên trong tổ vay vốn này.</p>
        <?php else: ?>
            <div class="loan-member-mobile-list" aria-label="Danh sách thành viên dạng thẻ">
                <?php foreach ($memberRows as $memberIndex => $memberRow): ?>
                    <article class="loan-member-mobile-card">
                        <header class="loan-member-mobile-head">
                            <span>TV <?= number_format($memberIndex + 1, 0, ',', '.') ?></span>
                            <strong><?= e($memberRow['role']) ?></strong>
                        </header>
                        <h3><?= e($memberRow['full_name']) ?></h3>
                        <p><?= e($memberRow['phone']) ?></p>
                        <div class="loan-member-mobile-money">
                            <div>
                                <small>Vốn vay ban đầu</small>
                                <strong><?= number_format($memberRow['loan_amount'], 0, ',', '.') ?> đ</strong>
                            </div>
                            <div>
                                <small>Dư nợ</small>
                                <strong><?= number_format($memberRow['outstanding_amount'], 0, ',', '.') ?> đ</strong>
                            </div>
                            <div class="<?= $memberRow['overdue_amount'] > 0 ? 'overdue' : '' ?>">
                                <small>Nợ quá hạn</small>
                                <strong><?= $memberRow['overdue_amount'] > 0 ? number_format($memberRow['overdue_amount'], 0, ',', '.') . ' đ' : 'Không có' ?></strong>
                            </div>
                        </div>
                        <dl class="loan-member-mobile-detail">
                            <div>
                                <dt>Mục đích</dt>
                                <dd><?= e($memberRow['purpose']) ?></dd>
                            </div>
                            <div>
                                <dt>Ghi chú</dt>
                                <dd><?= e($memberRow['note']) ?></dd>
                            </div>
                        </dl>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
<?php endif; ?>
