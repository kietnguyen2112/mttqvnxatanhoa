<div class="breadcrumb"><a href="/">Trang chủ</a><span>/</span><span>Giới thiệu MTTQVN xã</span></div>

<?php if (!empty($latestPosts)): ?>
    <?php $recentPosts = array_slice($latestPosts, 0, 3); ?>
    <section class="ticker-container" role="region" aria-live="polite" aria-label="Tin mới nhất">
        <div class="ticker-content">
            <div class="ticker-track" data-ticker-track>
                <?php foreach ($recentPosts as $post): ?>
                    <div class="ticker-item">
                        <a href="<?= e(post_public_url($post)) ?>" title="<?= e($post['title']) ?>" class="ticker-link">
                            <span class="ticker-date"><?= e(date_vi($post['published_at'])) ?></span>
                            <span class="ticker-title"><?= e($post['title']) ?></span>
                            <span class="ticker-arrow" aria-hidden="true">›</span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="hero-card experience-hero">
    <div class="hero-body">
        <small>Giới thiệu chung</small>
        <h1>Ủy ban Mặt trận Tổ quốc Việt Nam xã Tân Hòa</h1>
        <p class="lead">
            MTTQVN xã Tân Hòa là nơi tập hợp và xây dựng khối đại đoàn kết toàn dân tộc, phối hợp các tổ chức chính trị - xã hội
            thực hiện công tác tuyên truyền, vận động nhân dân, giám sát phản biện xã hội và chăm lo an sinh tại địa phương.
        </p>
        <div class="hero-actions">
            <?php if (post_module_enabled()): ?>
                <a class="button-link" href="/posts">Bài đăng mới</a>
            <?php endif; ?>
            <a class="button-link" href="/organizations">Tổ chức thành viên</a>
            <a class="button-link" href="/loan-groups">Tổ vay vốn</a>
        </div>
    </div>
    <div class="hero-showcase">
        <div class="hero-img hero-img-about">
            <div>
                <strong>Khối đại đoàn kết</strong>
                <?php $heroTickerText = 'Hội Nông dân, Đoàn Thanh niên, Hội Phụ nữ và các tổ chức thành viên phối hợp chặt chẽ vì sự phát triển bền vững của xã Tân Hòa.'; ?>
                <p class="hero-caption"><?= e($heroTickerText) ?></p>
            </div>
        </div>
    </div>
</section>


<?php if (post_module_enabled()): ?>
    <section class="section home-posts-section">
        <div class="section-head">
            <h2>Bài đăng mới</h2>
            <a href="/posts">Xem tất cả</a>
        </div>
        <p class="section-intro">Cập nhật tin tức, hoạt động và thông tin tuyên truyền của Ủy ban MTTQ Việt Nam xã Tân Hòa.</p>
        <?php if (!empty($latestPosts)): ?>
            <?php
            $featuredPost = $latestPosts[0];
            $secondaryPosts = array_slice($latestPosts, 0, 20);
            ?>
            <div class="home-posts-layout" data-home-posts>
                <article
                    class="home-featured-post"
                    data-home-featured
                    data-default-post='<?= e(json_encode([
                        'id' => (int)$featuredPost['id'],
                        'title' => (string)$featuredPost['title'],
                        'excerpt' => (string)$featuredPost['excerpt'],
                        'date' => date_vi($featuredPost['published_at']),
                        'image' => (string)($featuredPost['image_path'] ?? ''),
                        'url' => post_public_url($featuredPost),
                    ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'
                >
                    <a class="home-featured-post-media" data-home-featured-media href="<?= e(post_public_url($featuredPost)) ?>">
                        <?php if (!empty($featuredPost['image_path'])): ?>
                            <img data-home-featured-image src="/<?= e($featuredPost['image_path']) ?>" alt="<?= e($featuredPost['title']) ?>" width="640" height="360" loading="lazy" decoding="async">
                        <?php else: ?>
                            <span data-home-featured-placeholder>Tin chính</span>
                        <?php endif; ?>
                    </a>
                    <div class="home-featured-post-body">
                        <small data-home-featured-date><?= e(date_vi($featuredPost['published_at'])) ?></small>
                        <h2><a data-home-featured-title href="<?= e(post_public_url($featuredPost)) ?>"><?= e($featuredPost['title']) ?></a></h2>
                        <p data-home-featured-excerpt><?= e($featuredPost['excerpt']) ?></p>
                        <a class="read-more" data-home-featured-link href="<?= e(post_public_url($featuredPost)) ?>">Xem chi tiết</a>
                    </div>
                </article>

                <?php if (!empty($secondaryPosts)): ?>
                    <div class="home-secondary-posts" aria-label="Danh sách bài đăng mới">
                        <?php foreach ($secondaryPosts as $post): ?>
                            <?php $isCurrentFeaturedPost = (int)$post['id'] === (int)$featuredPost['id']; ?>
                            <article
                                class="home-secondary-post <?= $isCurrentFeaturedPost ? 'active-preview' : '' ?>"
                                data-home-secondary-post
                                data-post='<?= e(json_encode([
                                    'id' => (int)$post['id'],
                                    'title' => (string)$post['title'],
                                    'excerpt' => (string)$post['excerpt'],
                                    'date' => date_vi($post['published_at']),
                                    'image' => (string)($post['image_path'] ?? ''),
                                    'url' => post_public_url($post),
                                ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'
                            >
                                <a class="home-secondary-post-media" href="<?= e(post_public_url($post)) ?>">
                                    <?php if (!empty($post['image_path'])): ?>
                                        <img src="/<?= e($post['image_path']) ?>" alt="<?= e($post['title']) ?>" width="150" height="96" loading="lazy" decoding="async">
                                    <?php else: ?>
                                        <span>Tin phụ</span>
                                    <?php endif; ?>
                                </a>
                                <div>
                                    <small><?= e(date_vi($post['published_at'])) ?></small>
                                    <h3><a href="<?= e(post_public_url($post)) ?>"><?= e($post['title']) ?></a></h3>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="empty-state">Chưa có bài đăng công khai. Các bài viết sau khi đăng trong trang quản trị sẽ hiển thị tại đây.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="section">
    <div class="section-head">
        <h2>Thông tin tổng hợp</h2>
    </div>
    <p class="section-intro">Thông tin tổng hợp về các cơ sở tôn giáo, ủy viên Ủy ban MTTQ Việt Nam xã và Ban công tác Mặt trận ấp trên địa bàn xã Tân Hòa.</p>

    <div class="summary-grid">
        <a class="summary-card" href="/organizations/show?slug=mttq-viet-nam-xa-tan-hoa#uy-vien-mttq" aria-label="Xem danh sách Ủy viên Ủy ban MTTQ Việt Nam xã">
            <strong>54</strong>
            <span>Ủy viên Ủy ban MTTQ Việt Nam xã</span>
        </a>
        <a class="summary-card" href="/organizations/show?slug=mttq-viet-nam-xa-tan-hoa#ban-cong-tac-mat-tran" aria-label="Xem Ban công tác Mặt trận ấp">
            <strong>32</strong>
            <span>Ban công tác Mặt trận ấp</span>
        </a>
        <a class="summary-card" href="#quan-ly-ton-giao-dan-toc" aria-label="Xem danh sách cơ sở tôn giáo trên địa bàn xã">
            <strong>12</strong>
            <span>Cơ sở tôn giáo trên địa bàn xã</span>
        </a>
    </div>
</section>

<section class="section standing-board-section">
    <div class="section-head">
        <h2>Ban Thường trực</h2>
    </div>
    <p class="section-intro">Lãnh đạo Ban Thường trực Uỷ ban MTTQ Việt Nam xã Tân Hoà </p>

    <?php
    $leaders = $mttq['leaders'] ?? [];
    $chairperson = null;
    $viceChairpersons = [];
    foreach ($leaders as $leader) {
        $position = mb_strtolower((string)($leader['position'] ?? ''), 'UTF-8');
        if (str_starts_with($position, 'ủy viên ban mttqvn -')) {
            continue;
        }

        $leaderName = mb_strtolower(trim((string)($leader['full_name'] ?? '')), 'UTF-8');
        if ($leaderName === 'phạm hoàng hiệp') {
            $chairperson = $leader;
        } elseif (!$chairperson && (str_contains($position, 'chủ tịch') || str_contains($position, 'bí thư')) && !str_contains($position, 'phó')) {
            $chairperson = $leader;
        } elseif (str_contains($position, 'phó chủ tịch')) {
            $viceChairpersons[] = $leader;
        }
    }
    ?>

    <?php if ($chairperson): ?>
        <?php
        $chairpersonNameNormalized = function_exists('mb_strtolower')
            ? mb_strtolower(trim((string)($chairperson['full_name'] ?? '')), 'UTF-8')
            : strtolower(trim((string)($chairperson['full_name'] ?? '')));
        $chairpersonCardClass = $chairpersonNameNormalized === 'phạm hoàng hiệp'
            ? ' chairperson-pham-hoang-hiep'
            : '';
        ?>
        <section class="chairperson-card home-chairperson-card<?= $chairpersonCardClass ?>">
            <?php if (!empty($chairperson['avatar'])): ?>
                <img src="/<?= e($chairperson['avatar']) ?>" alt="Avatar <?= e($chairperson['full_name']) ?>" class="chairperson-avatar" width="120" height="180" loading="lazy" decoding="async">
            <?php else: ?>
                <div class="chairperson-avatar"><?= e(function_exists('mb_substr') ? mb_substr($chairperson['full_name'], 0, 1, 'UTF-8') : substr($chairperson['full_name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div>
                <h2><?= e($chairperson['full_name']) ?></h2>
                <p><?= e($chairperson['position']) ?></p>
                <small><?= e($chairperson['phone']) ?><?= $chairperson['email'] ? ' | ' . e($chairperson['email']) : '' ?></small>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($viceChairpersons)): ?>
        <h2>Các Phó Chủ tịch MTTQVN xã</h2>
        <div class="vice-grid home-vice-grid">
            <?php foreach ($viceChairpersons as $leader): ?>
                <section class="profile-card vice-card">
                    <?php if (!empty($leader['avatar'])): ?>
                        <img src="/<?= e($leader['avatar']) ?>" alt="Avatar <?= e($leader['full_name']) ?>" class="avatar" width="66" height="88" loading="lazy" decoding="async">
                    <?php else: ?>
                        <div class="avatar"><?= e(function_exists('mb_substr') ? mb_substr($leader['full_name'], 0, 1, 'UTF-8') : substr($leader['full_name'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <div>
                        <h3><?= e($leader['full_name']) ?></h3>
                        <p><?= e($leader['position']) ?></p>
                        <small><?= e($leader['phone']) ?><?= $leader['email'] ? ' | ' . e($leader['email']) : '' ?></small>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="section" id="quan-ly-ton-giao-dan-toc">
    <div class="section-head">
        <h2>Các hội phối hợp</h2>
    </div>
    <p class="section-intro">Những tổ chức thành viên đang triển khai các hoạt động thiết thực tại địa phương.</p>

    <div class="member-org-grid">
        <?php foreach ($memberOrganizations as $organization): ?>
            <?php $organizationLogo = organization_logo_path((string)($organization['slug'] ?? '')); ?>
            <article class="organization-card">
                <?php if ($organizationLogo !== ''): ?>
                    <div class="organization-card-logo">
                        <img src="/<?= e($organizationLogo) ?>" alt="Logo <?= e($organization['name']) ?>" width="72" height="72" loading="lazy" decoding="async">
                    </div>
                <?php endif; ?>
                <!-- <div class="organization-badge"><?= e($organization['short_name']) ?></div> -->
                <h2><a href="/organizations/show?slug=<?= e($organization['slug']) ?>"><?= e($organization['name']) ?></a></h2>
                <p><?= e($organization['description']) ?></p>
                <?php if (!empty($organization['leaders'][0])): ?>
                    <div class="leader-row">
                        <span>Phụ trách</span>
                        <strong><?= e($organization['leaders'][0]['full_name']) ?> - <?= e($organization['leaders'][0]['position']) ?></strong>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2 id="quan-ly-ton-giao-dan-toc">Quản lý tôn giáo, dân tộc, chức sắc, chức việc trên địa bàn xã</h2>
    </div>
    <p class="section-intro">Danh sách các cơ sở tôn giáo, tín ngưỡng trên địa bàn xã Tân Hòa.</p>

    <?php
    $religionFacilities = [
        ['Nhà thờ Bảy Ngàn', 'Ấp 3, xã Tân Hòa'],
        ['Điểm nhóm Tin lành Bảy Ngàn', 'Ấp Bảy Ngàn, xã Tân Hòa'],
        ['Điểm nhóm Tin lành Một Ngàn', 'Ấp Nhơn Thuận 1A, xã Tân Hòa'],
        ['Chùa Bửu Tường', 'Ấp 4, xã Tân Hòa'],
        ['Đình Thần Tân Hòa', 'Ấp Bảy Ngàn, xã Tân Hòa'],
        ['Chùa Khem Ma Răng Sây', 'Ấp Bảy Ngàn, xã Tân Hòa'],
        ['Chùa Thơm Ma Răng Sây', 'Ấp 4, xã Tân Hòa'],
        ['Hưng Nghĩa Tự', 'Ấp Bảy Ngàn, xã Tân Hòa'],
        ['Hưng Thiện Tự', 'Ấp Một Ngàn, xã Tân Hòa'],
        ['Phật giáo Hòa Hảo xã Tân Hòa', 'Ấp 3B, xã Tân Hòa'],
        ['Thánh thất Cao Đài', 'Ấp 3, xã Tân Hòa'],
        ['Miếu Bà Chúa Xứ', 'Ấp 3B, xã Tân Hòa'],
        ['Đình Thần', 'Ấp 3A, xã Tân Hòa'],
    ];
    ?>

    <div class="table-wrap religion-table-wrap">
        <table class="religion-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Cơ sở tôn giáo</th>
                    <th>Địa chỉ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($religionFacilities as $index => [$facilityName, $facilityAddress]): ?>
                    <tr>
                        <td data-label="STT"><?= $index + 1 ?></td>
                        <td data-label="Cơ sở tôn giáo"><?= e($facilityName) ?></td>
                        <td data-label="Địa chỉ"><?= e($facilityAddress) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="religion-mobile-list" aria-label="Danh sách cơ sở tôn giáo dạng thẻ">
        <?php foreach ($religionFacilities as $index => [$facilityName, $facilityAddress]): ?>
            <article class="religion-mobile-card">
                <span><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <div>
                    <h3><?= e($facilityName) ?></h3>
                    <p><?= e($facilityAddress) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
