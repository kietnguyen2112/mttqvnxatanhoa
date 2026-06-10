<section class="section search-page admin-search-page">
    <div class="section-head"><h1>Tìm kiếm quản trị</h1></div>

    <form class="search-panel" action="/admin/search" method="get">
        <input type="search" name="q" value="<?= e($query) ?>" placeholder="Nhập tên, chức vụ, chi đoàn, chi hội, số điện thoại..." aria-label="Từ khóa tìm kiếm quản trị" autofocus>
        <button type="submit">Tìm kiếm</button>
    </form>

    <?php if ($query === ''): ?>
        <p class="empty-state">Nhập từ khóa để tìm nhanh dữ liệu đang quản lý.</p>
    <?php else: ?>
        <p class="search-summary">Tìm thấy <strong><?= (int)$total ?></strong> kết quả cho từ khóa <strong><?= e($query) ?></strong>.</p>

        <?php if ($total === 0): ?>
            <p class="empty-state">Không tìm thấy kết quả phù hợp.</p>
        <?php endif; ?>

        <?php if (post_module_enabled() && !empty($results['posts'])): ?>
            <div class="result-group">
                <h2>Bài đăng</h2>
                <div class="list">
                    <?php foreach ($results['posts'] as $post): ?>
                        <article class="search-result">
                            <span class="result-type"><?= e(date('d/m/Y', strtotime($post['published_at']))) ?></span>
                            <h3><?= e($post['title']) ?></h3>
                            <p><?= e($post['excerpt']) ?></p>
                            <div class="search-actions">
                                <a class="btn-edit" href="/admin/posts?edit=<?= (int)$post['id'] ?>">Sửa</a>
                                <a class="btn-cancel compact" href="/posts/show?id=<?= (int)$post['id'] ?>">Xem</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($results['organizations'])): ?>
            <div class="result-group">
                <h2>Tổ chức</h2>
                <div class="list">
                    <?php foreach ($results['organizations'] as $organization): ?>
                        <article class="search-result">
                            <span class="organization-badge"><?= e($organization['short_name']) ?></span>
                            <h3><?= e($organization['name']) ?></h3>
                            <p><?= e($organization['description']) ?></p>
                            <div class="table-actions">
                                <a class="btn-edit" href="<?= $organization['slug'] === 'mttq-viet-nam-xa-tan-hoa' ? '/about' : '/organizations/show?slug=' . e($organization['slug']) ?>">Xem trang hiển thị</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($results['leaders'])): ?>
            <div class="result-group">
                <h2>Cán bộ</h2>
                <div class="list">
                    <?php foreach ($results['leaders'] as $leader): ?>
                        <article class="search-result">
                            <span class="result-type"><?= e($leader['organization_short_name']) ?></span>
                            <h3><?= e($leader['full_name']) ?></h3>
                            <p><?= e($leader['position']) ?><?= $leader['phone'] ? ' - ' . e($leader['phone']) : '' ?></p>
                            <div class="table-actions">
                                <a class="btn-edit" href="/admin/leaders?edit=<?= (int)$leader['id'] ?>">Sửa</a>
                                <a class="btn-cancel compact" href="/organizations/show?slug=<?= e($leader['organization_slug']) ?>">Xem</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($results['hamletMembers'])): ?>
            <div class="result-group">
                <h2>Hồ sơ cấp ấp</h2>
                <div class="list">
                    <?php foreach ($results['hamletMembers'] as $member): ?>
                        <article class="search-result">
                            <span class="result-type"><?= e($member['hamlet_name']) ?> - <?= e($member['organization_short_name']) ?></span>
                            <h3><?= e($member['full_name']) ?></h3>
                            <p><?= e($member['role']) ?><?= $member['phone'] ? ' - ' . e($member['phone']) : '' ?></p>
                            <div class="table-actions">
                                <a class="btn-edit" href="/admin/hamlet-members?edit=<?= (int)$member['id'] ?>">Sửa</a>
                                <a class="btn-cancel compact" href="/organizations/show?slug=<?= e($member['organization_slug']) ?>">Xem</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($results['loanGroups'])): ?>
            <div class="result-group">
                <h2>Tổ vay vốn</h2>
                <div class="list">
                    <?php foreach ($results['loanGroups'] as $group): ?>
                        <article class="search-result">
                            <span class="result-type"><?= e($group['organization_short_name']) ?> - <?= e($group['hamlet_name']) ?></span>
                            <h3><?= e($group['name']) ?></h3>
                            <p>Tổ trưởng: <?= e($group['leader_name']) ?><?= $group['fund_source'] ? ' - ' . e($group['fund_source']) : '' ?></p>
                            <div class="table-actions">
                                <a class="btn-edit" href="/admin/loan-groups?edit=<?= (int)$group['id'] ?>">Sửa</a>
                                <a class="btn-cancel compact" href="/loan-groups/show?id=<?= (int)$group['id'] ?>">Xem</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($results['loanMembers'])): ?>
            <div class="result-group">
                <h2>Thành viên vay vốn</h2>
                <div class="list">
                    <?php foreach ($results['loanMembers'] as $member): ?>
                        <article class="search-result">
                            <span class="result-type"><?= e($member['organization_short_name']) ?> - <?= e($member['loan_group_name']) ?></span>
                            <h3><?= e($member['full_name']) ?></h3>
                            <p><?= e($member['purpose']) ?><?= (float)$member['loan_amount'] > 0 ? ' - ' . number_format((float)$member['loan_amount'], 0, ',', '.') . ' đ' : '' ?></p>
                            <div class="table-actions">
                                <a class="btn-edit" href="/admin/loan-members?edit=<?= (int)$member['id'] ?>">Sửa</a>
                                <a class="btn-cancel compact" href="/loan-groups/show?id=<?= (int)$member['loan_group_id'] ?>">Xem</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (($totalPages ?? 1) > 1): ?>
            <nav class="pagination" aria-label="Phân trang tìm kiếm quản trị">
                <?php $prevPage = max(1, (int)$page - 1); ?>
                <?php $nextPage = min((int)$totalPages, (int)$page + 1); ?>
                <a class="<?= (int)$page <= 1 ? 'disabled' : '' ?>" href="<?= (int)$page <= 1 ? '#' : '/admin/search?q=' . urlencode($query) . '&page=' . $prevPage ?>">← Trước</a>
                <span>Trang <?= (int)$page ?> / <?= (int)$totalPages ?></span>
                <a class="<?= (int)$page >= (int)$totalPages ? 'disabled' : '' ?>" href="<?= (int)$page >= (int)$totalPages ? '#' : '/admin/search?q=' . urlencode($query) . '&page=' . $nextPage ?>">Sau →</a>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
