<div class="breadcrumb"><a href="/">Trang chủ</a><span>/</span><span>Tìm kiếm</span></div>

<section class="section search-page">
    <div class="section-head">
        <h1>Tìm kiếm</h1>
    </div>

    <form class="search-panel" action="/search" method="get" data-ui-busy>
        <input type="search" name="q" value="<?= e($query) ?>" placeholder="Nhập từ khóa cần tìm..." aria-label="Từ khóa tìm kiếm" autofocus>
        <button type="submit">Tìm kiếm</button>
    </form>

    <?php if ($query === ''): ?>
        <p class="empty-state">Nhập từ khóa để tìm thông tin hội, cán bộ, thành viên và tổ vay vốn.</p>
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
                            <span class="result-type"><?= e(date_vi($post['published_at'])) ?></span>
                            <h3><a href="/posts/show?id=<?= (int)$post['id'] ?>"><?= e($post['title']) ?></a></h3>
                            <p><?= e($post['excerpt']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($results['organizations'])): ?>
            <div class="result-group">
                <h2>Tổ chức thành viên</h2>
                <div class="list">
                    <?php foreach ($results['organizations'] as $organization): ?>
                        <article class="search-result">
                            <span class="organization-badge"><?= e($organization['short_name']) ?></span>
                            <h3><a href="<?= $organization['slug'] === 'mttq-viet-nam-xa-tan-hoa' ? '/about' : '/organizations/show?slug=' . e($organization['slug']) ?>"><?= e($organization['name']) ?></a></h3>
                            <p><?= e($organization['description']) ?></p>
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
                            <h3><a href="/organizations/show?slug=<?= e($leader['organization_slug']) ?>"><?= e($leader['full_name']) ?></a></h3>
                            <p><?= e($leader['position']) ?> - <?= e($leader['organization_name']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($results['hamletMembers'])): ?>
            <div class="result-group">
                <h2>Thành viên cấp ấp</h2>
                <div class="list">
                    <?php foreach ($results['hamletMembers'] as $member): ?>
                        <article class="search-result">
                            <span class="result-type"><?= e($member['hamlet_name']) ?> - <?= e($member['organization_short_name']) ?></span>
                            <h3><a href="/organizations/show?slug=<?= e($member['organization_slug']) ?>"><?= e($member['full_name']) ?></a></h3>
                            <p><?= e($member['role']) ?><?= $member['phone'] ? ' - ' . e($member['phone']) : '' ?></p>
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
                            <h3><a href="/loan-groups/show?id=<?= (int)$group['id'] ?>"><?= e($group['name']) ?></a></h3>
                            <p>Tổ trưởng: <?= e($group['leader_name']) ?><?= $group['fund_source'] ? ' - ' . e($group['fund_source']) : '' ?></p>
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
                            <h3><a href="/loan-groups/show?id=<?= (int)$member['loan_group_id'] ?>"><?= e($member['full_name']) ?></a></h3>
                            <p><?= e($member['purpose']) ?><?= (float)$member['loan_amount'] > 0 ? ' - ' . number_format((float)$member['loan_amount'], 0, ',', '.') . ' đ' : '' ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (($totalPages ?? 1) > 1): ?>
            <nav class="pagination" aria-label="Phân trang tìm kiếm">
                <?php $prevPage = max(1, (int)$page - 1); ?>
                <?php $nextPage = min((int)$totalPages, (int)$page + 1); ?>
                <a class="<?= (int)$page <= 1 ? 'disabled' : '' ?>" href="<?= (int)$page <= 1 ? '#' : '/search?q=' . urlencode($query) . '&page=' . $prevPage ?>">← Trước</a>
                <span>Trang <?= (int)$page ?> / <?= (int)$totalPages ?></span>
                <a class="<?= (int)$page >= (int)$totalPages ? 'disabled' : '' ?>" href="<?= (int)$page >= (int)$totalPages ? '#' : '/search?q=' . urlencode($query) . '&page=' . $nextPage ?>">Sau →</a>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
