<?php
if (!function_exists('document_file_size')) {
    function document_file_size(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 1, ',', '.') . ' MB';
        }

        return number_format(max(1, $bytes / 1024), 0, ',', '.') . ' KB';
    }
}

$pageQuery = $_GET;
unset($pageQuery['page']);
?>
<div class="breadcrumb"><a href="/">Trang chủ</a><span>/</span><span>Văn bản</span></div>

<div class="document-system document-portal">
    <header class="document-system-banner document-overview-card">
        <div class="document-overview-content">
            <span class="document-live"><i aria-hidden="true"></i> Thư viện công khai</span>
            <span class="organization-badge document-overview-badge">Văn bản</span>
            <h1>Tra cứu văn bản công bố</h1>
            <p>Tra cứu các văn bản được Ủy ban MTTQ Việt Nam xã Tân Hòa ban hành và đăng tải phục vụ người dân.</p>
        </div>
        <div class="document-overview-metrics" aria-label="Thống kê văn bản">
            <div>
                <strong><?= (int)($libraryTotal ?? $total) ?></strong>
                <span>Văn bản đã đăng tải</span>
            </div>
            <div>
                <strong><?= count($documentTypes) ?></strong>
                <span>Loại văn bản</span>
            </div>
        </div>
    </header>

    <div class="document-workspace">
        <aside class="document-search-box">
            <header class="document-panel-title">
                <h2>Bộ lọc tra cứu</h2>
                <small>Tìm nhanh theo thông tin văn bản</small>
            </header>
            <form class="document-search-form" action="/documents" method="get" data-ui-busy>
                <label>
                    Từ khóa
                    <input type="search" name="q" value="<?= e($query) ?>" placeholder="Số ký hiệu hoặc trích yếu">
                </label>
                <label>
                    Loại văn bản
                    <select name="type">
                        <option value="">Tất cả loại văn bản</option>
                        <?php foreach ($documentTypes as $type): ?>
                            <option value="<?= e($type) ?>" <?= $selectedType === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Năm ban hành
                    <select name="year">
                        <option value="">Tất cả các năm</option>
                        <?php foreach ($documentYears as $year): ?>
                            <option value="<?= e($year) ?>" <?= $selectedYear === (string)$year ? 'selected' : '' ?>><?= e($year) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Cơ quan ban hành
                    <select disabled aria-label="Cơ quan ban hành">
                        <option>Ủy ban MTTQ Việt Nam xã Tân Hòa</option>
                    </select>
                </label>
                <label>
                    Hiển thị
                    <select name="limit">
                        <?php foreach ([10, 20, 50] as $size): ?>
                            <option value="<?= $size ?>" <?= $limit === $size ? 'selected' : '' ?>><?= $size ?> kết quả</option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="document-search-actions">
                    <button type="submit">Tra cứu</button>
                    <a href="/documents">Đặt lại</a>
                </div>
            </form>
        </aside>

        <section class="document-result-box">
            <div class="document-result-title">
                <div>
                    <small>Kết quả tra cứu</small>
                    <h2>Danh sách văn bản</h2>
                </div>
                <span><?= $total ?> văn bản</span>
            </div>

            <div class="document-table-wrap table-wrap">
                <table class="document-result-table">
                    <thead>
                    <tr>
                        <th>Số ký hiệu</th>
                        <th>Ngày ban hành</th>
                        <th>Trích yếu và tệp đính kèm</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($documents)): ?>
                        <tr><td class="document-empty" colspan="3">Không tìm thấy văn bản phù hợp.</td></tr>
                    <?php else: ?>
                        <?php foreach ($documents as $document): ?>
                            <tr>
                                <td class="document-number" data-label="Số ký hiệu"><?= e($document['document_number'] !== '' ? $document['document_number'] : '---') ?></td>
                                <td class="document-date" data-label="Ngày ban hành"><?= e(date_vi($document['issued_date'])) ?></td>
                                <td class="document-subject" data-label="Trích yếu và tệp">
                                    <h3><?= e($document['title']) ?></h3>
                                    <?php if ($document['description'] !== ''): ?>
                                        <p><?= e($document['description']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($document['document_type'] !== ''): ?>
                                        <small class="document-type"><?= e($document['document_type']) ?></small>
                                    <?php endif; ?>
                                    <div class="document-attachment-list">
                                        <?php foreach ($document['attachments'] as $file): ?>
                                            <?php $canPreview = strtolower(pathinfo($file['original_name'], PATHINFO_EXTENSION)) === 'pdf'; ?>
                                            <div class="document-table-file">
                                                <span class="document-file-icon"><?= e(strtoupper(pathinfo($file['original_name'], PATHINFO_EXTENSION)) ?: 'TỆP') ?></span>
                                                <span class="document-file-name"><?= e($file['original_name']) ?> <small>(<?= e(document_file_size((int)$file['file_size'])) ?>)</small></span>
                                                <?php if ($canPreview): ?>
                                                    <a href="/documents/preview?file=<?= (int)$file['id'] ?>" target="_blank" rel="noopener">Xem trước</a>
                                                <?php endif; ?>
                                                <a href="/documents/download?file=<?= (int)$file['id'] ?>">Tải về</a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($documents)): ?>
                <div class="document-mobile-list" aria-label="Danh sách văn bản dạng thẻ">
                    <?php foreach ($documents as $document): ?>
                        <article class="document-mobile-card">
                            <header class="document-mobile-card-head">
                                <span><?= e($document['document_number'] !== '' ? $document['document_number'] : '---') ?></span>
                                <time datetime="<?= e($document['issued_date']) ?>"><?= e(date_vi($document['issued_date'])) ?></time>
                            </header>
                            <h3><?= e($document['title']) ?></h3>
                            <?php if ($document['description'] !== ''): ?>
                                <p><?= e($document['description']) ?></p>
                            <?php endif; ?>
                            <?php if ($document['document_type'] !== ''): ?>
                                <small class="document-mobile-type"><?= e($document['document_type']) ?></small>
                            <?php endif; ?>
                            <?php if (!empty($document['attachments'])): ?>
                                <div class="document-mobile-files" aria-label="Tệp đính kèm">
                                    <?php foreach ($document['attachments'] as $file): ?>
                                        <?php $canPreview = strtolower(pathinfo($file['original_name'], PATHINFO_EXTENSION)) === 'pdf'; ?>
                                        <section class="document-mobile-file">
                                            <div>
                                                <span class="document-file-icon"><?= e(strtoupper(pathinfo($file['original_name'], PATHINFO_EXTENSION)) ?: 'TỆP') ?></span>
                                                <strong><?= e($file['original_name']) ?></strong>
                                                <small><?= e(document_file_size((int)$file['file_size'])) ?></small>
                                            </div>
                                            <div class="document-mobile-file-actions">
                                                <?php if ($canPreview): ?>
                                                    <a href="/documents/preview?file=<?= (int)$file['id'] ?>" target="_blank" rel="noopener">Xem trước</a>
                                                <?php endif; ?>
                                                <a href="/documents/download?file=<?= (int)$file['id'] ?>">Tải về</a>
                                            </div>
                                        </section>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($totalPages > 1): ?>
                <nav class="document-pagination" aria-label="Phân trang văn bản">
                    <?php for ($itemPage = 1; $itemPage <= $totalPages; $itemPage++): ?>
                        <?php $queryForPage = http_build_query($pageQuery + ['page' => $itemPage]); ?>
                        <a class="<?= $itemPage === $page ? 'active' : '' ?>" href="/documents?<?= e($queryForPage) ?>"><?= $itemPage ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        </section>
    </div>
</div>
