<?php
if (!function_exists('post_public_url')) {
    function post_public_url(array $post): string
    {
        return !empty($post['slug'])
            ? '/tin-tuc/' . rawurlencode((string)$post['slug'])
            : '/posts/show?id=' . (int)$post['id'];
    }
}
?>
<div class="breadcrumb"><a href="/">Trang chủ</a><span>/</span><span>Bài đăng</span></div>

<section class="posts-page-section">
    <div class="posts-page-header">
        <div>
            <span class="posts-page-kicker">Tin tức - hoạt động</span>
            <h1>Bài đăng</h1>
            <p>Tin tức, hoạt động và thông tin tuyên truyền của Ủy ban MTTQ Việt Nam xã Tân Hòa.</p>
        </div>
        <div class="posts-page-stat">
            <strong><?= (int)$total ?></strong>
            <span>bài công khai</span>
        </div>
    </div>

    <?php if (empty($posts)): ?>
        <p class="empty-state">Chưa có bài đăng công khai.</p>
    <?php else: ?>
        <?php
        $featuredPost = $posts[0];
        $remainingPosts = array_slice($posts, 1);
        $featuredPreview = function_exists('mb_substr') ? mb_substr(strip_tags((string)$featuredPost['content']), 0, 220, 'UTF-8') : substr(strip_tags((string)$featuredPost['content']), 0, 220);
        ?>
        <div class="posts-top-layout">
            <article class="posts-featured-card">
                <a class="posts-featured-media" href="<?= e(post_public_url($featuredPost)) ?>">
                    <?php if (!empty($featuredPost['image_path'])): ?>
                        <img src="/<?= e($featuredPost['image_path']) ?>" alt="<?= e($featuredPost['title']) ?>" width="720" height="430" loading="eager" decoding="async">
                    <?php else: ?>
                        <span>Bài đăng</span>
                    <?php endif; ?>
                </a>
                <div class="posts-featured-body">
                    <div class="post-meta-row">
                        <span class="post-label">Mới nhất</span>
                        <small><?= e(date_vi($featuredPost['published_at'])) ?></small>
                    </div>
                    <h2><a href="<?= e(post_public_url($featuredPost)) ?>"><?= e($featuredPost['title']) ?></a></h2>
                    <p><?= e($featuredPost['excerpt'] ?: $featuredPreview) ?></p>
                    <a class="read-more" href="<?= e(post_public_url($featuredPost)) ?>">Xem chi tiết</a>
                </div>
            </article>

            <aside class="posts-side-panel" aria-label="Bài viết nổi bật">
                <div class="posts-side-head">
                    <h2>Tin nổi bật</h2>
                    <a href="/posts">Tất cả</a>
                </div>
                <div class="posts-side-list">
                    <?php $sidePosts = !empty($featuredPosts) ? $featuredPosts : array_slice($posts, 0, 4); ?>
                    <?php foreach (array_slice($sidePosts, 0, 4) as $sidePost): ?>
                        <?php if ((int)$sidePost['id'] === (int)$featuredPost['id'] && count($sidePosts) > 1) { continue; } ?>
                        <a class="posts-side-item" href="<?= e(post_public_url($sidePost)) ?>">
                            <?php if (!empty($sidePost['image_path'])): ?>
                                <img src="/<?= e($sidePost['image_path']) ?>" alt="" width="92" height="68" loading="lazy" decoding="async">
                            <?php else: ?>
                                <span class="posts-side-thumb">Tin</span>
                            <?php endif; ?>
                            <span>
                                <small><?= e(date_vi($sidePost['published_at'])) ?></small>
                                <strong><?= e($sidePost['title']) ?></strong>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>

        <?php if (!empty($remainingPosts)): ?>
            <div class="posts-list-head">
                <h2>Tin mới</h2>
                <span>Trang <?= (int)$page ?> / <?= (int)$totalPages ?></span>
            </div>
            <div class="posts-grid">
                <?php foreach ($remainingPosts as $post): ?>
                    <article class="post-card">
                        <a class="post-card-media" href="<?= e(post_public_url($post)) ?>">
                            <?php if (!empty($post['image_path'])): ?>
                                <img src="/<?= e($post['image_path']) ?>" alt="<?= e($post['title']) ?>" width="360" height="210" loading="lazy" decoding="async">
                            <?php else: ?>
                                <span>Bài đăng</span>
                            <?php endif; ?>
                        </a>
                        <div class="post-card-body">
                            <div class="post-meta-row">
                                <span class="post-label">Tin hoạt động</span>
                                <small><?= e(date_vi($post['published_at'])) ?></small>
                            </div>
                            <h2><a href="<?= e(post_public_url($post)) ?>"><?= e($post['title']) ?></a></h2>
                            <?php $preview = function_exists('mb_substr') ? mb_substr(strip_tags((string)$post['content']), 0, 150, 'UTF-8') : substr(strip_tags((string)$post['content']), 0, 150); ?>
                            <p><?= e($post['excerpt'] ?: $preview) ?></p>
                            <a class="read-more" href="<?= e(post_public_url($post)) ?>">Xem chi tiết</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination posts-pagination" aria-label="Phân trang bài đăng">
                <?php for ($itemPage = 1; $itemPage <= $totalPages; $itemPage++): ?>
                    <a class="<?= $itemPage === $page ? 'active' : '' ?>" href="/posts?page=<?= $itemPage ?>"><?= $itemPage ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
