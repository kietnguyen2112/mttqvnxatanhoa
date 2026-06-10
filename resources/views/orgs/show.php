<?php if (!$organization): ?>
    <section class="section">
        <h1>Không tìm thấy tổ chức</h1>
        <p>Tổ chức không tồn tại hoặc đã bị gỡ bỏ.</p>
    </section>
<?php else: ?>
    <div class="breadcrumb"><a href="/">Trang chủ</a><span>/</span><a href="/organizations">Tổ chức thành viên</a><span>/</span><span><?= e($organization['short_name']) ?></span></div>

    <?php $isMttqOrganization = ($organization['slug'] ?? '') === 'mttq-viet-nam-xa-tan-hoa'; ?>
    <article class="organization-detail <?= $isMttqOrganization ? 'mttq-detail-page' : 'article' ?>">
        <div class="organization-detail-main">
            <header class="<?= $isMttqOrganization ? 'mttq-detail-hero' : 'subpage-hero' ?>">
                <small><?= e($organization['short_name']) ?></small>
                <h1><?= e($organization['name']) ?></h1>
                <p class="lead"><?= e($organization['description']) ?></p>
                <?php if ($isMttqOrganization): ?>
                    <nav class="mttq-detail-nav" aria-label="Đi đến nội dung">
                        <a href="#ban-thuong-truc">Ban Thường trực</a>
                        <a href="#uy-vien-mttq">Danh sách uỷ viên</a>
                        <a href="#ban-cong-tac-mat-tran">Ban công tác cấp ấp</a>
                    </nav>
                <?php endif; ?>
            </header>

            <?php
            $leaderList = $organization['leaders'] ?? [];
            $topLeaders = [];
            $viceLeaders = [];
            $otherLeaders = [];
            foreach ($leaderList as $leader) {
                $position = mb_strtolower($leader['position'] ?? '', 'UTF-8');
                if ((str_contains($position, 'chủ tịch') || str_contains($position, 'bí thư') || str_contains($position, 'chủ nhiệm') || str_contains($position, 'trưởng')) && !str_contains($position, 'phó')) {
                    $topLeaders[] = $leader;
                } elseif (str_contains($position, 'phó') || str_contains($position, 'phó bí thư') || str_contains($position, 'phó chủ tịch') || str_contains($position, 'phó chủ nhiệm')) {
                    $viceLeaders[] = $leader;
                } else {
                    $otherLeaders[] = $leader;
                }
            }

            if (empty($topLeaders) && !empty($leaderList)) {
                $topLeaders[] = $leaderList[0];
            }

            $isMemberOrganization = !empty($memberOrganizationProfile['enabled']);
            $hamletMemberCount = 0;
            foreach (($organization['hamlets'] ?? []) as $members) {
                $hamletMemberCount += count($members);
            }
            $organizationLeaders = [];
            $listedOrganizationLeaders = [];
            $memberLeaderHeading = 'Chủ tịch và Phó Chủ tịch';
            $memberLeaderListHeading = 'Danh sách cán bộ, hội viên khác';
            if ($isMemberOrganization) {
                $isYouthUnion = ($organization['slug'] ?? '') === 'doan-thanh-nien';
                $memberLeaderHeading = $isYouthUnion ? 'Bí thư và Phó Bí thư' : 'Chủ tịch và Phó Chủ tịch';
                $memberLeaderListHeading = $isYouthUnion ? 'Danh sách cán bộ, đoàn viên khác' : 'Danh sách cán bộ, hội viên khác';
                foreach ($leaderList as $leader) {
                    $position = mb_strtolower((string)($leader['position'] ?? ''), 'UTF-8');
                    $isFeaturedLeader = false;
                    if ($isYouthUnion) {
                        $isFeaturedLeader = str_contains($position, 'phó bí thư')
                            || str_contains($position, 'bí thư đoàn')
                            || (str_contains($position, 'bí thư') && !str_contains($position, 'phó') && !str_contains($position, 'btcd'));
                    } else {
                        $isFeaturedLeader = (str_contains($position, 'chủ tịch') && !str_contains($position, 'phó'))
                            || str_contains($position, 'phó chủ tịch')
                            || str_contains($position, 'p. chủ tịch')
                            || str_contains($position, 'p.chủ tịch');
                    }

                    if ($isFeaturedLeader) {
                        $organizationLeaders[] = $leader;
                    } else {
                        $listedOrganizationLeaders[] = $leader;
                    }
                }

                if (empty($organizationLeaders) && !empty($leaderList)) {
                    $organizationLeaders[] = $leaderList[0];
                    $listedOrganizationLeaders = array_slice($leaderList, 1);
                }
            }
            ?>

            <?php if ($isMttqOrganization): ?>
                <?php
                $chairperson = null;
                $viceChairpersons = [];
                $associationDeputyLeaders = [];
                $associationSpecialists = [];
                foreach ($leaderList as $leader) {
                    $position = mb_strtolower((string)($leader['position'] ?? ''), 'UTF-8');
                    if (str_contains($position, 'chủ tịch') && !str_contains($position, 'phó') && !$chairperson) {
                        $chairperson = $leader;
                    } elseif (str_contains($position, 'phó chủ tịch')) {
                        $viceChairpersons[] = $leader;
                    }
                }
                if (!$chairperson && !empty($topLeaders[0])) {
                    $chairperson = $topLeaders[0];
                }
                $standingMembers = array_merge($chairperson ? [$chairperson] : [], $viceChairpersons);
                foreach (($memberOrganizations ?? []) as $memberOrganization) {
                    $isYouthUnionMember = ($memberOrganization['slug'] ?? '') === 'doan-thanh-nien';
                    foreach (($memberOrganization['leaders'] ?? []) as $leader) {
                        $position = mb_strtolower((string)($leader['position'] ?? ''), 'UTF-8');
                        $trimmedPosition = trim($position);
                        $isDeputyLeader = $isYouthUnionMember
                            ? str_contains($position, 'phó bí thư')
                            : (str_contains($position, 'phó chủ tịch hội')
                                || str_contains($position, 'p. chủ tịch')
                                || str_contains($position, 'p.chủ tịch')
                                || (str_starts_with($trimmedPosition, 'phó chủ tịch') && !str_contains($position, 'mttq')));
                        if (!$isDeputyLeader) {
                            continue;
                        }
                        $leader['member_organization_short_name'] = $memberOrganization['short_name'] ?? '';
                        $associationDeputyLeaders[] = $leader;
                        break;
                    }
                }
                $specialistNamesByOrganization = [
                    'hoi-nong-dan' => ['Lê Thị Thuỳ Dung', 'Lê Thị Thùy Dung'],
                    'doan-thanh-nien' => ['Nguyễn Chí Trung'],
                    'hoi-lien-hiep-phu-nu' => ['Nguyễn Thị Hồng Thoa'],
                    'hoi-cuu-chien-binh' => ['Đoàn Thanh Điền'],
                ];
                foreach (($memberOrganizations ?? []) as $memberOrganization) {
                    $aliases = $specialistNamesByOrganization[$memberOrganization['slug'] ?? ''] ?? [];
                    if (empty($aliases)) {
                        continue;
                    }
                    foreach (($memberOrganization['leaders'] ?? []) as $leader) {
                        if (!in_array((string)($leader['full_name'] ?? ''), $aliases, true)) {
                            continue;
                        }
                        $leader['member_organization_short_name'] = $memberOrganization['short_name'] ?? '';
                        $associationSpecialists[] = $leader;
                        break;
                    }
                }
                usort($associationSpecialists, static function (array $first, array $second): int {
                    $firstIsPriority = ($first['full_name'] ?? '') === 'Nguyễn Chí Trung';
                    $secondIsPriority = ($second['full_name'] ?? '') === 'Nguyễn Chí Trung';
                    return ($secondIsPriority <=> $firstIsPriority);
                });
                $committeeMembers = array_values(array_filter($leaderList, static function (array $leader) use ($standingMembers): bool {
                    foreach ($standingMembers as $standingMember) {
                        if (isset($leader['id'], $standingMember['id']) && (string)$leader['id'] === (string)$standingMember['id']) {
                            return false;
                        }
                        if (($leader['full_name'] ?? '') === ($standingMember['full_name'] ?? '')
                            && ($leader['position'] ?? '') === ($standingMember['position'] ?? '')) {
                            return false;
                        }
                    }
                    return true;
                }));
                ?>

                <section class="mttq-leadership-section" id="ban-thuong-truc" aria-labelledby="mttq-standing-title">
                    <header class="mttq-content-heading">
                        <div>
                            <small>Cơ cấu lãnh đạo</small>
                            <h2 id="mttq-standing-title">Ban Thường trực</h2>
                        </div>
                    </header>
                    <?php if ($chairperson): ?>
                        <section class="chairperson-card home-chairperson-card">
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
                        <div class="mttq-vice-heading">
                            <h2>Phó Chủ tịch MTTQVN xã Tân Hoà</h2>
                        </div>
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
                    <?php if (!empty($associationDeputyLeaders)): ?>
                        <div class="mttq-vice-heading mttq-association-deputy-heading">
                            <h2>Phó các Tổ chức chính trị - xã hội</h2>
                        </div>
                        <div class="vice-grid home-vice-grid mttq-association-deputy-grid">
                            <?php foreach ($associationDeputyLeaders as $leader): ?>
                                <section class="profile-card vice-card mttq-association-deputy-card">
                                    <?php if (!empty($leader['avatar'])): ?>
                                        <img src="/<?= e($leader['avatar']) ?>" alt="Avatar <?= e($leader['full_name']) ?>" class="avatar" width="66" height="88" loading="lazy" decoding="async">
                                    <?php else: ?>
                                        <div class="avatar"><?= e(function_exists('mb_substr') ? mb_substr($leader['full_name'], 0, 1, 'UTF-8') : substr($leader['full_name'], 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <div>
                                        <h3><?= e($leader['full_name']) ?></h3>
                                        <p><?= e($leader['position']) ?></p>
                                        <small>
                                            <?= e($leader['member_organization_short_name'] ?? '') ?>
                                            <?= !empty($leader['phone']) ? ' | ' . e($leader['phone']) : '' ?>
                                            <?= !empty($leader['email']) ? ' | ' . e($leader['email']) : '' ?>
                                        </small>
                                    </div>
                                </section>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($associationSpecialists)): ?>
                        <div class="mttq-vice-heading mttq-association-specialist-heading">
                            <h2>Chuyên viên Uỷ ban MTTQ Việt Nam xã</h2>
                        </div>
                        <div class="vice-grid home-vice-grid mttq-association-specialist-grid">
                            <?php foreach ($associationSpecialists as $leader): ?>
                                <section class="profile-card vice-card mttq-association-specialist-card">
                                    <?php if (!empty($leader['avatar'])): ?>
                                        <img src="/<?= e($leader['avatar']) ?>" alt="Avatar <?= e($leader['full_name']) ?>" class="avatar" width="66" height="88" loading="lazy" decoding="async">
                                    <?php else: ?>
                                        <div class="avatar"><?= e(function_exists('mb_substr') ? mb_substr($leader['full_name'], 0, 1, 'UTF-8') : substr($leader['full_name'], 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <div>
                                        <h3><?= e($leader['full_name']) ?></h3>
                                        <p><?= e($leader['position']) ?></p>
                                        <?php if (!empty($leader['phone']) || !empty($leader['email'])): ?>
                                            <small>
                                                <?= !empty($leader['phone']) ? e($leader['phone']) : '' ?>
                                                <?= !empty($leader['phone']) && !empty($leader['email']) ? ' | ' : '' ?>
                                                <?= !empty($leader['email']) ? e($leader['email']) : '' ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </section>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="mttq-committee-section" id="uy-vien-mttq" aria-labelledby="mttq-committee-title">
                    <header class="mttq-content-heading mttq-section-head">
                        <div>
                            <small>Thành viên Ủy ban</small>
                            <h2 id="mttq-committee-title">Ủy viên Ủy ban MTTQ Việt Nam xã Tân Hoà</h2>
                        </div>
                        <span><?= number_format(count($committeeMembers), 0, ',', '.') ?> uỷ viên đang hiển thị</span>
                    </header>
                    <p class="mttq-section-intro">Danh sách uỷ viên đã cập nhật, được trình bày riêng theo từng hồ sơ.</p>
                    <?php if (empty($committeeMembers)): ?>
                        <p class="empty-state">Chưa cập nhật danh sách uỷ viên Ủy ban MTTQ Việt Nam xã.</p>
                    <?php else: ?>
                        <div class="mttq-committee-grid">
                            <?php foreach ($committeeMembers as $leaderIndex => $leader): ?>
                                <article class="mttq-committee-card">
                                    <span class="mttq-committee-index"><?= str_pad((string)($leaderIndex + 1), 2, '0', STR_PAD_LEFT) ?></span>
                                    <div>
                                        <h3><?= e($leader['full_name']) ?></h3>
                                        <p><?= e($leader['position']) ?></p>
                                        <?php if (!empty($leader['phone']) || !empty($leader['email'])): ?>
                                            <small><?= e($leader['phone']) ?><?= $leader['phone'] && $leader['email'] ? ' | ' : '' ?><?= e($leader['email']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php elseif (!$isMemberOrganization): ?>
                <?php
                $communeLeaders = array_merge($topLeaders, $viceLeaders);
                ?>
                <?php if (!empty($communeLeaders)): ?>
                    <div class="section-subhead"><h2>Cán bộ cấp xã</h2></div>
                    <div class="profile-grid commune-leader-grid">
                        <?php foreach ($communeLeaders as $leader): ?>
                            <section class="profile-card commune-leader-card">
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
            <?php endif; ?>

            <?php if ($isMemberOrganization): ?>
                <?php if (!empty($organizationLeaders)): ?>
                    <div class="section-subhead member-leader-head">
                        <h2><?= e($memberLeaderHeading) ?></h2>
                        <small><?= count($organizationLeaders) ?> cán bộ nổi bật</small>
                    </div>
                    <div class="profile-grid commune-leader-grid member-featured-leader-grid">
                        <?php foreach ($organizationLeaders as $leader): ?>
                            <section class="profile-card commune-leader-card member-featured-leader-card">
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
                <?php if (!empty($listedOrganizationLeaders)): ?>
                    <div class="section-subhead member-leader-list-head">
                        <h2><?= e($memberLeaderListHeading) ?></h2>
                        <small><?= count($listedOrganizationLeaders) ?> người</small>
                    </div>
                    <div class="table-wrap member-leader-list-wrap">
                        <table class="member-leader-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Họ và tên</th>
                                    <th>Chức vụ</th>
                                    <th>Điện thoại</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listedOrganizationLeaders as $leaderIndex => $leader): ?>
                                    <tr>
                                        <td data-label="STT"><?= $leaderIndex + 1 ?></td>
                                        <td data-label="Họ và tên"><strong><?= e($leader['full_name']) ?></strong></td>
                                        <td data-label="Chức vụ"><?= e($leader['position']) ?></td>
                                        <td data-label="Điện thoại"><?= e($leader['phone'] ?: '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <div class="section-subhead"><h2><?= e($memberOrganizationProfile['statsHeading']) ?></h2></div>
                <div class="women-union-stats">
                    <?php foreach ($memberOrganizationProfile['stats'] as $item): ?>
                        <section class="women-union-stat">
                            <strong><?= e($item['value']) ?></strong>
                            <span><?= e($item['label']) ?></span>
                        </section>
                    <?php endforeach; ?>
                </div>
                <p class="women-union-structure-note">
                    <?= e($memberOrganizationProfile['structureNote']) ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="organization-detail-members<?= $isMttqOrganization ? ' mttq-hamlet-section' : '' ?>" <?= $isMttqOrganization ? 'id="ban-cong-tac-mat-tran" aria-labelledby="mttq-hamlet-title"' : '' ?>>
            <?php if ($isMemberOrganization): ?>
                <?php
                $hasChapterHouseholds = false;
                $hasChapterGenderStats = false;
                foreach ($organizationChapters as $chapter) {
                    $hasChapterHouseholds = $hasChapterHouseholds || (int)($chapter[3] ?? 0) > 0;
                    $hasChapterGenderStats = $hasChapterGenderStats || (int)($chapter[4] ?? 0) > 0 || (int)($chapter[5] ?? 0) > 0;
                }
                ?>
                <div class="section-subhead women-union-chapter-head">
                    <h2>Chi tiết từng <?= e($memberOrganizationProfile['unitLabel']) ?></h2>
                    <small><?= count($organizationChapters) ?> <?= e($memberOrganizationProfile['unitLabel']) ?> - <?= number_format($organizationMemberTotal, 0, ',', '.') ?> <?= e($memberOrganizationProfile['memberLabel']) ?></small>
                </div>
                <p class="women-union-chapter-guide">Chọn tên <?= e($memberOrganizationProfile['unitLabel']) ?> để xem danh sách <?= e($memberOrganizationProfile['memberLabel']) ?> ở trang chi tiết.</p>
                <?php if (empty($organizationChapters)): ?>
                    <p class="empty-state">Chưa cập nhật danh sách <?= e($memberOrganizationProfile['unitLabel']) ?> cho tổ chức này.</p>
                <?php else: ?>
                    <div class="table-wrap women-union-chapter-table-wrap">
                        <table class="women-union-chapter-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên <?= e($memberOrganizationProfile['unitTitle']) ?></th>
                                    <?php if ($hasChapterHouseholds): ?>
                                        <th>Số hộ</th>
                                    <?php endif; ?>
                                    <th>Số <?= e($memberOrganizationProfile['memberTitle']) ?></th>
                                    <?php if ($hasChapterGenderStats): ?>
                                        <th>Nam</th>
                                        <th>Nữ</th>
                                    <?php endif; ?>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($organizationChapters as $index => $chapter): ?>
                                    <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><a class="women-chapter-link" href="/organizations/chapter?slug=<?= e($organization['slug']) ?>&amp;chapter=<?= $index ?>"><?= e($chapter[0]) ?></a></td>
                                        <?php if ($hasChapterHouseholds): ?>
                                            <td><?= (int)($chapter[3] ?? 0) > 0 ? number_format((int)$chapter[3], 0, ',', '.') : '-' ?></td>
                                        <?php endif; ?>
                                        <td><?= number_format((int)$chapter[1], 0, ',', '.') ?></td>
                                        <?php if ($hasChapterGenderStats): ?>
                                            <td><?= (int)($chapter[4] ?? 0) > 0 ? number_format((int)$chapter[4], 0, ',', '.') : '-' ?></td>
                                            <td><?= (int)($chapter[5] ?? 0) > 0 ? number_format((int)$chapter[5], 0, ',', '.') : '-' ?></td>
                                        <?php endif; ?>
                                        <td><?= e($chapter[2]) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="<?= 2 + ($hasChapterHouseholds ? 1 : 0) ?>">Tổng cộng</th>
                                    <th><?= number_format($organizationMemberTotal, 0, ',', '.') ?></th>
                                    <?php if ($hasChapterGenderStats): ?>
                                        <th><?= number_format(array_sum(array_map(static fn (array $chapter): int => (int)($chapter[4] ?? 0), $organizationChapters)), 0, ',', '.') ?></th>
                                        <th><?= number_format(array_sum(array_map(static fn (array $chapter): int => (int)($chapter[5] ?? 0), $organizationChapters)), 0, ',', '.') ?></th>
                                    <?php endif; ?>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($isMttqOrganization): ?>
                    <header class="mttq-content-heading mttq-hamlet-heading">
                        <div>
                            <small>Mạng lưới địa bàn</small>
                            <h2 id="mttq-hamlet-title">Ban công tác Mặt trận tại các ấp</h2>
                        </div>
                        <span><?= count($organization['hamlets'] ?? []) ?> ban - <?= $hamletMemberCount ?> cán bộ</span>
                    </header>
                <?php else: ?>
                    <div class="section-subhead commune-hamlet-list-head">
                        <h2>Các hội cấp ấp và thành viên</h2>
                        <small><?= count($organization['hamlets'] ?? []) ?> đơn vị - <?= $hamletMemberCount ?> thành viên</small>
                    </div>
                <?php endif; ?>
                <?php if (empty($organization['hamlets'])): ?>
                    <p class="empty-state"><?= $isMttqOrganization ? 'Chưa cập nhật Ban công tác Mặt trận tại các ấp.' : 'Chưa có thành viên cấp ấp được ghi nhận cho tổ chức này.' ?></p>
                <?php else: ?>
                    <?php if ($isMttqOrganization): ?>
                        <div class="mttq-hamlet-card-list" aria-label="Danh sách Ban công tác Mặt trận tại các ấp">
                            <?php $memberIndex = 0; ?>
                            <?php foreach ($organization['hamlets'] as $hamletName => $members): ?>
                                <?php foreach ($members as $member): ?>
                                    <?php $memberIndex++; ?>
                                    <article class="mttq-hamlet-card">
                                        <span class="mttq-hamlet-index"><?= str_pad((string)$memberIndex, 2, '0', STR_PAD_LEFT) ?></span>
                                        <div class="mttq-hamlet-person">
                                            <h3><?= e($member['full_name']) ?></h3>
                                            <dl>
                                                <div>
                                                    <dt>Ấp</dt>
                                                    <dd><?= e($hamletName) ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Số điện thoại</dt>
                                                    <dd><?= e($member['phone'] ?: '-') ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Ngày sinh</dt>
                                                    <dd><?= e($member['birth_date'] ?: '-') ?></dd>
                                                </div>
                                            </dl>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-wrap commune-hamlet-list-wrap">
                            <table class="commune-hamlet-members-table">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Chi hội / Đơn vị cấp ấp</th>
                                        <th>Họ và tên</th>
                                        <th>Chức vụ</th>
                                        <th>Điện thoại</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $memberIndex = 0; ?>
                                    <?php foreach ($organization['hamlets'] as $hamletName => $members): ?>
                                        <?php foreach ($members as $member): ?>
                                            <?php $memberIndex++; ?>
                                            <tr>
                                                <td data-label="STT"><?= $memberIndex ?></td>
                                                <td data-label="Đơn vị"><strong><?= e($hamletName) ?></strong></td>
                                                <td data-label="Họ và tên"><?= e($member['full_name']) ?></td>
                                                <td data-label="Chức vụ"><?= e($member['role']) ?></td>
                                                <td data-label="Điện thoại"><?= e($member['phone'] ?: '-') ?></td>
                                                <td data-label="Ghi chú"><?= e($member['note'] ?: '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </article>
<?php endif; ?>
