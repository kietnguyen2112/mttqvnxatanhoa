<?php
$featuredLeaders = $featuredOrganization['leaders'] ?? [];
$featuredChair = $featuredLeaders[0] ?? null;
$featuredUnitCount = count($featuredOrganization['hamlets'] ?? []);
?>
<div class="breadcrumb"><a href="/">Trang chủ</a><span>/</span><span>Tổ chức thành viên</span></div>

<?php if ($featuredOrganization): ?>
    <section class="organizations-parent-card">
        <div class="organizations-parent-content">
            <small class="portal-eyebrow">Tổ chức thành viên</small>
            <h1><?= e($featuredOrganization['name']) ?></h1>
            <p><?= e($featuredOrganization['description']) ?></p>
            <a class="organizations-action" href="/organizations/show?slug=<?= e($featuredOrganization['slug']) ?>">Xem thông tin MTTQVN xã</a>
        </div>
        <div class="organizations-parent-side">
            <?php if ($featuredChair): ?>
                <small>Chủ tịch MTTQVN xã</small>
                <strong><?= e($featuredChair['full_name']) ?></strong>
                <span><?= e($featuredChair['phone'] ?: 'Chưa cập nhật điện thoại') ?></span>
            <?php endif; ?>
            <div class="organizations-parent-metrics">
                <div><b><?= count($featuredLeaders) ?></b><em>Cán bộ</em></div>
                <div><b><?= $featuredUnitCount ?></b><em>Đơn vị cấp ấp</em></div>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="organizations-member-section">
    <div class="organizations-section-head">
        <div>
            <h2>Các tổ chức thành viên</h2>
        </div>
    </div>

    <div class="organizations-directory-grid">
        <?php foreach ($memberOrganizations as $item): ?>
            <?php
            $organization = $item['organization'];
            $headLeader = $item['headLeader'];
            $organizationLogo = organization_logo_path((string)($organization['slug'] ?? ''));
            ?>
            <article class="organizations-directory-card">
                <?php if ($organizationLogo !== ''): ?>
                    <div class="organizations-directory-logo">
                        <img src="/<?= e($organizationLogo) ?>" alt="Logo <?= e($organization['name']) ?>" width="76" height="76" loading="lazy" decoding="async">
                    </div>
                <?php endif; ?>
                <h3><a href="/organizations/show?slug=<?= e($organization['slug']) ?>"><?= e($organization['name']) ?></a></h3>
                <p><?= e($organization['description']) ?></p>

                <div class="organizations-card-metrics">
                    <div>
                        <strong><?= number_format($item['leaderCount'], 0, ',', '.') ?></strong>
                        <span>Cán bộ</span>
                    </div>
                    <div>
                        <strong><?= number_format($item['unitCount'], 0, ',', '.') ?></strong>
                        <span><?= e($item['unitMetricLabel']) ?></span>
                    </div>
                    <div class="<?= !empty($organization['chapters']) ? 'highlight' : '' ?>">
                        <strong><?= number_format($item['memberCount'], 0, ',', '.') ?></strong>
                        <span><?= e($item['memberMetricLabel']) ?></span>
                    </div>
                </div>

                <?php if ($headLeader): ?>
                    <div class="organizations-card-leader">
                        <small><?= e($headLeader['position']) ?></small>
                        <strong><?= e($headLeader['full_name']) ?></strong>
                    </div>
                <?php endif; ?>

                <a class="organizations-action" href="/organizations/show?slug=<?= e($organization['slug']) ?>">Xem chi tiết và thành viên</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
