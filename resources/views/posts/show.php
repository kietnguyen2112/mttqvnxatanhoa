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
<?php if (!$post): ?>
    <div class="breadcrumb"><a href="/">Trang chủ</a><span>/</span><a href="/posts">Bài đăng</a><span>/</span><span>Không tìm thấy</span></div>
    <section class="section">
        <div class="section-head"><h1>Không tìm thấy bài đăng</h1></div>
        <p>Bài đăng không tồn tại hoặc chưa được công khai.</p>
        <a class="button-link" href="/posts">Quay lại danh sách bài đăng</a>
    </section>
<?php else: ?>
    <div class="breadcrumb"><a href="/">Trang chủ</a><span>/</span><a href="/posts">Bài đăng</a><span>/</span><span><?= e($post['title']) ?></span></div>

    <div class="post-detail-layout">
        <article class="article post-detail">
            <?php if (!empty($post['image_path'])): ?>
                <figure class="post-detail-hero">
                    <img class="post-detail-image" src="/<?= e($post['image_path']) ?>" alt="<?= e($post['title']) ?>" width="960" height="540" decoding="async" fetchpriority="high">
                </figure>
            <?php endif; ?>
            <div class="post-detail-body">
                <header class="post-detail-header">
                    <div class="post-meta-row">
                        <span class="post-label"><?= !empty($isPreview) ? 'Xem trước' : 'Bài đăng' ?></span>
                        <small><?= e(date_vi($post['published_at'])) ?></small>
                        <small><?= (int)($post['views'] ?? 0) ?> lượt xem</small>
                    </div>
                    <h1><?= e($post['title']) ?></h1>
                    <?php if (!empty($post['excerpt'])): ?>
                        <p class="lead"><?= e($post['excerpt']) ?></p>
                    <?php endif; ?>
                </header>
                <div class="post-content">
                    <?php $postContent = trim((string)$post['content']); ?>
                    <?php if ($postContent !== '' && $postContent !== strip_tags($postContent)): ?>
                        <?= $postContent ?>
                    <?php else: ?>
                        <?php foreach (preg_split('/\R{2,}/', $postContent) as $paragraph): ?>
                            <?php if (trim($paragraph) !== ''): ?>
                                <p><?= nl2br(e($paragraph)) ?></p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </article>

        <?php if (empty($isPreview) && !empty($relatedPosts)): ?>
            <aside class="post-detail-sidebar" aria-label="Tin liên quan">
                <div class="post-detail-sidebar-inner">
                    <h2>Tin mới</h2>
                    <?php foreach ($relatedPosts as $relatedPost): ?>
                        <?php if ((int)$relatedPost['id'] === (int)$post['id']) { continue; } ?>
                        <a class="post-related-item" href="<?= e(post_public_url($relatedPost)) ?>">
                            <?php if (!empty($relatedPost['image_path'])): ?>
                                <img src="/<?= e($relatedPost['image_path']) ?>" alt="" width="76" height="58" loading="lazy" decoding="async">
                            <?php else: ?>
                                <span>Tin</span>
                            <?php endif; ?>
                            <strong><?= e($relatedPost['title']) ?></strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>
        <?php endif; ?>
    </div>
<?php endif; ?>
