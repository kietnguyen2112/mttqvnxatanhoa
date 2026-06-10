<?php

namespace App\Models;

use PDO;

class Post
{
    public const STATUSES = ['draft', 'published', 'hidden'];

    public static function all(bool $publishedOnly = false): array
    {
        self::ensureTable();

        $sql = 'SELECT * FROM posts';
        if ($publishedOnly) {
            $sql .= " WHERE status = 'published' AND published_at <= " . Database::connection()->quote(self::now());
        }
        $sql .= ' ORDER BY published_at DESC, created_at DESC, id DESC';

        return Database::connection()->query($sql)->fetchAll();
    }

    public static function paginated(int $page = 1, int $limit = 12, array $filters = []): array
    {
        self::ensureTable();

        $page = max(1, $page);
        $limit = max(1, min(100, $limit));
        [$whereSql, $params] = self::adminFilterSql($filters);

        $connection = Database::connection();
        $countStatement = $connection->prepare('SELECT COUNT(*) FROM posts' . $whereSql);
        $countStatement->execute($params);
        $total = (int)$countStatement->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $limit));
        $page = min($page, $totalPages);

        $statement = $connection->prepare(
            'SELECT * FROM posts' . $whereSql . ' ORDER BY published_at DESC, created_at DESC, id DESC LIMIT ? OFFSET ?'
        );
        $position = 1;
        foreach ($params as $param) {
            $statement->bindValue($position++, $param);
        }
        $statement->bindValue($position++, $limit, PDO::PARAM_INT);
        $statement->bindValue($position, ($page - 1) * $limit, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
        ];
    }

    public static function publishedPaginated(int $page = 1, int $limit = 9): array
    {
        self::ensureTable();

        $page = max(1, $page);
        $limit = max(1, min(60, $limit));
        $connection = Database::connection();
        $now = self::now();
        $countStatement = $connection->prepare("SELECT COUNT(*) FROM posts WHERE status = 'published' AND published_at <= ?");
        $countStatement->execute([$now]);
        $total = (int)$countStatement->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $limit));
        $page = min($page, $totalPages);

        $statement = $connection->prepare(
            "SELECT * FROM posts
             WHERE status = 'published' AND published_at <= ?
             ORDER BY is_featured DESC, published_at DESC, created_at DESC, id DESC
             LIMIT ? OFFSET ?"
        );
        $statement->bindValue(1, $now);
        $statement->bindValue(2, $limit, PDO::PARAM_INT);
        $statement->bindValue(3, ($page - 1) * $limit, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
        ];
    }

    public static function latest(int $limit = 3): array
    {
        self::ensureTable();

        $statement = Database::connection()->prepare(
            "SELECT * FROM posts WHERE status = 'published' AND published_at <= ? ORDER BY published_at DESC, created_at DESC, id DESC LIMIT ?"
        );
        $statement->bindValue(1, self::now());
        $statement->bindValue(2, max(1, $limit), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function featured(int $limit = 4): array
    {
        self::ensureTable();

        $statement = Database::connection()->prepare(
            "SELECT * FROM posts
             WHERE status = 'published' AND is_featured = 1 AND published_at <= ?
             ORDER BY published_at DESC, created_at DESC, id DESC
             LIMIT ?"
        );
        $statement->bindValue(1, self::now());
        $statement->bindValue(2, max(1, $limit), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function count(): int
    {
        self::ensureTable();

        return (int)Database::connection()->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        self::ensureTable();

        $statement = Database::connection()->prepare('SELECT * FROM posts WHERE id = ? LIMIT 1');
        $statement->execute([$id]);
        $post = $statement->fetch();

        return $post ? self::withContentImages($post) : null;
    }

    public static function findPublished(int $id): ?array
    {
        self::ensureTable();

        $statement = Database::connection()->prepare("SELECT * FROM posts WHERE id = ? AND status = 'published' AND published_at <= ? LIMIT 1");
        $statement->execute([$id, self::now()]);
        $post = $statement->fetch();

        return $post ? self::withContentImages($post) : null;
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        self::ensureTable();

        $statement = Database::connection()->prepare(
            "SELECT * FROM posts WHERE slug = ? AND status = 'published' AND published_at <= ? LIMIT 1"
        );
        $statement->execute([trim($slug), self::now()]);
        $post = $statement->fetch();

        return $post ? self::withContentImages($post) : null;
    }

    public static function create(array $data, string $imagePath = '', array $contentImages = []): int
    {
        self::ensureTable();

        $connection = Database::connection();
        $connection->beginTransaction();
        try {
            $statement = $connection->prepare(
                'INSERT INTO posts (title, slug, excerpt, content, image_path, thumbnail, status, is_featured, views, published_at, meta_title, meta_description, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)'
            );
            $title = trim((string)$data['title']);
            $slug = self::uniqueSlug((string)($data['slug'] ?? $title));
            $statement->execute([
                $title,
                $slug,
                trim((string)($data['excerpt'] ?? '')),
                self::sanitizeContent((string)$data['content']),
                $imagePath,
                $imagePath,
                self::status((string)($data['status'] ?? 'published')),
                !empty($data['is_featured']) ? 1 : 0,
                self::dateTimeOrNow((string)($data['published_at'] ?? '')),
                trim((string)($data['meta_title'] ?? '')),
                trim((string)($data['meta_description'] ?? '')),
                self::nullablePositiveInt($data['created_by'] ?? null),
                self::nullablePositiveInt($data['updated_by'] ?? null),
            ]);

            $postId = (int)$connection->lastInsertId();
            self::insertContentImages($postId, $contentImages);
            $connection->commit();

            return $postId;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
    }

    public static function update(int $id, array $data, ?string $imagePath = null, array $newContentImages = [], array $removeContentImageIds = []): ?array
    {
        self::ensureTable();
        $post = self::find($id);
        if (!$post) {
            return null;
        }

        $title = trim((string)$data['title']);
        $sql = 'UPDATE posts SET title = ?, slug = ?, excerpt = ?, content = ?, status = ?, is_featured = ?, published_at = ?, meta_title = ?, meta_description = ?, updated_by = ?';
        $params = [
            $title,
            self::uniqueSlug((string)($data['slug'] ?? $title), $id),
            trim((string)($data['excerpt'] ?? '')),
            self::sanitizeContent((string)$data['content']),
            self::status((string)($data['status'] ?? 'published')),
            !empty($data['is_featured']) ? 1 : 0,
            self::dateTimeOrNow((string)($data['published_at'] ?? '')),
            trim((string)($data['meta_title'] ?? '')),
            trim((string)($data['meta_description'] ?? '')),
            self::nullablePositiveInt($data['updated_by'] ?? null),
        ];

        if ($imagePath !== null) {
            $sql .= ', image_path = ?, thumbnail = ?';
            $params[] = $imagePath;
            $params[] = $imagePath;
        }

        $sql .= ' WHERE id = ?';
        $params[] = $id;

        $connection = Database::connection();
        $connection->beginTransaction();
        try {
            $connection->prepare($sql)->execute($params);

            if ($removeContentImageIds) {
                $placeholders = implode(',', array_fill(0, count($removeContentImageIds), '?'));
                $deleteStatement = $connection->prepare("DELETE FROM post_images WHERE post_id = ? AND id IN ({$placeholders})");
                $deleteStatement->execute(array_merge([$id], $removeContentImageIds));
            }

            self::insertContentImages($id, $newContentImages);
            $connection->commit();
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }

        return $post;
    }

    public static function setStatus(int $id, string $status): bool
    {
        self::ensureTable();

        $statement = Database::connection()->prepare('UPDATE posts SET status = ? WHERE id = ?');

        return $statement->execute([self::status($status), $id]);
    }

    public static function toggleFeatured(int $id): bool
    {
        self::ensureTable();

        $statement = Database::connection()->prepare('UPDATE posts SET is_featured = IF(is_featured = 1, 0, 1) WHERE id = ?');

        return $statement->execute([$id]);
    }

    public static function incrementViews(int $id): void
    {
        self::ensureTable();

        Database::connection()->prepare('UPDATE posts SET views = views + 1 WHERE id = ?')->execute([$id]);
    }

    public static function delete(int $id): ?array
    {
        self::ensureTable();
        $post = self::find($id);
        if (!$post) {
            return null;
        }

        Database::connection()->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);

        return $post;
    }

    public static function findContentImages(int $postId): array
    {
        self::ensureTable();

        $statement = Database::connection()->prepare('SELECT * FROM post_images WHERE post_id = ? ORDER BY sort_order ASC, id ASC');
        $statement->execute([$postId]);

        return $statement->fetchAll();
    }

    private static function ensureTable(): void
    {
        $connection = Database::connection();
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS posts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) DEFAULT '',
                excerpt TEXT NOT NULL,
                content MEDIUMTEXT NOT NULL,
                image_path VARCHAR(255) DEFAULT '',
                thumbnail VARCHAR(255) DEFAULT '',
                status ENUM('draft','published','hidden') NOT NULL DEFAULT 'published',
                is_featured TINYINT(1) NOT NULL DEFAULT 0,
                views BIGINT UNSIGNED NOT NULL DEFAULT 0,
                published_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                meta_title VARCHAR(255) DEFAULT '',
                meta_description VARCHAR(255) DEFAULT '',
                created_by BIGINT UNSIGNED NULL,
                updated_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY posts_slug_unique (slug),
                INDEX posts_status_published_index (status, published_at),
                INDEX posts_featured_index (is_featured, published_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        self::ensurePostColumns($connection);
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS post_images (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                post_id BIGINT UNSIGNED NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                caption VARCHAR(255) DEFAULT '',
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT post_images_post_id_fk FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                UNIQUE KEY post_images_path_unique (image_path),
                INDEX post_images_post_index (post_id, sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private static function ensurePostColumns(PDO $connection): void
    {
        $columns = self::columns($connection, 'posts');
        $columnSql = [
            'slug' => "ALTER TABLE posts ADD COLUMN slug VARCHAR(255) DEFAULT '' AFTER title",
            'thumbnail' => "ALTER TABLE posts ADD COLUMN thumbnail VARCHAR(255) DEFAULT '' AFTER image_path",
            'is_featured' => "ALTER TABLE posts ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER status",
            'views' => "ALTER TABLE posts ADD COLUMN views BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER is_featured",
            'meta_title' => "ALTER TABLE posts ADD COLUMN meta_title VARCHAR(255) DEFAULT '' AFTER published_at",
            'meta_description' => "ALTER TABLE posts ADD COLUMN meta_description VARCHAR(255) DEFAULT '' AFTER meta_title",
            'created_by' => "ALTER TABLE posts ADD COLUMN created_by BIGINT UNSIGNED NULL AFTER meta_description",
            'updated_by' => "ALTER TABLE posts ADD COLUMN updated_by BIGINT UNSIGNED NULL AFTER created_by",
        ];

        foreach ($columnSql as $column => $sql) {
            if (!in_array($column, $columns, true)) {
                $connection->exec($sql);
            }
        }

        $connection->exec("ALTER TABLE posts MODIFY COLUMN status ENUM('draft','published','hidden') NOT NULL DEFAULT 'published'");
        $connection->exec("UPDATE posts SET thumbnail = image_path WHERE (thumbnail IS NULL OR thumbnail = '') AND image_path <> ''");
        self::backfillSlugs($connection);
        self::ensureIndex($connection, 'posts', 'posts_slug_unique', 'CREATE UNIQUE INDEX posts_slug_unique ON posts (slug)');
        self::ensureIndex($connection, 'posts', 'posts_featured_index', 'CREATE INDEX posts_featured_index ON posts (is_featured, published_at)');
    }

    private static function withContentImages(array $post): array
    {
        $post['content'] = self::sanitizeContent((string)($post['content'] ?? ''));
        $post['content_images'] = self::findContentImages((int)$post['id']);

        return $post;
    }

    private static function insertContentImages(int $postId, array $images): void
    {
        if (!$images) {
            return;
        }

        $currentMax = Database::connection()
            ->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM post_images WHERE post_id = ?');
        $currentMax->execute([$postId]);
        $sortOrder = (int)$currentMax->fetchColumn();

        $statement = Database::connection()->prepare(
            'INSERT INTO post_images (post_id, image_path, caption, sort_order) VALUES (?, ?, ?, ?)'
        );
        foreach ($images as $image) {
            $sortOrder++;
            $statement->execute([
                $postId,
                trim((string)$image['image_path']),
                trim((string)($image['caption'] ?? '')),
                $sortOrder,
            ]);
        }
    }

    private static function adminFilterSql(array $filters): array
    {
        $where = [];
        $params = [];

        $search = trim((string)($filters['q'] ?? ''));
        if ($search !== '') {
            $where[] = '(title LIKE ? OR excerpt LIKE ? OR slug LIKE ?)';
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $status = trim((string)($filters['status'] ?? ''));
        if (in_array($status, self::STATUSES, true)) {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        return [$where ? ' WHERE ' . implode(' AND ', $where) : '', $params];
    }

    private static function columns(PDO $connection, string $table): array
    {
        $statement = $connection->prepare(
            'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $statement->execute([$table]);

        return array_map('strval', array_column($statement->fetchAll(), 'COLUMN_NAME'));
    }

    private static function ensureIndex(PDO $connection, string $table, string $index, string $sql): void
    {
        $statement = $connection->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?'
        );
        $statement->execute([$table, $index]);
        if ((int)$statement->fetchColumn() === 0) {
            $connection->exec($sql);
        }
    }

    private static function backfillSlugs(PDO $connection): void
    {
        $statement = $connection->query("SELECT id, title, slug FROM posts WHERE slug IS NULL OR slug = '' ORDER BY id ASC");
        foreach ($statement->fetchAll() as $row) {
            $slug = self::uniqueSlug((string)$row['title'], (int)$row['id']);
            $update = $connection->prepare('UPDATE posts SET slug = ? WHERE id = ?');
            $update->execute([$slug, (int)$row['id']]);
        }
    }

    public static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = self::slugify($value);
        if ($base === '') {
            $base = 'bai-dang';
        }

        $slug = $base;
        $suffix = 2;
        while (self::slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM posts WHERE slug = ?';
        $params = [$slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return (int)$statement->fetchColumn() > 0;
    }

    public static function slugify(string $value): string
    {
        $value = trim(function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value));
        $map = [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ];
        $value = strtr($value, $map);
        $value = preg_replace('/[^a-z0-9]+/u', '-', $value) ?? '';
        $value = trim($value, '-');

        return substr($value, 0, 180);
    }

    private static function status(string $status): string
    {
        return in_array($status, self::STATUSES, true) ? $status : 'published';
    }

    private static function nullablePositiveInt(mixed $value): ?int
    {
        $int = (int)$value;

        return $int > 0 ? $int : null;
    }

    private static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    private static function sanitizeContent(string $content): string
    {
        $content = trim($content);
        if ($content === '') {
            return '';
        }

        if ($content === strip_tags($content)) {
            $paragraphs = preg_split('/\R{2,}/', $content) ?: [];
            $html = [];
            foreach ($paragraphs as $paragraph) {
                $paragraph = trim($paragraph);
                if ($paragraph !== '') {
                    $html[] = '<p>' . nl2br(htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8')) . '</p>';
                }
            }
            return implode('', $html);
        }

        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><h1><h2><h3><h4><a><img><figure><figcaption><div><span><table><thead><tbody><tr><th><td>';
        $content = strip_tags($content, $allowedTags);
        $content = preg_replace('/\s+on[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/iu', '', $content) ?? $content;
        $content = preg_replace_callback('/\s+style\s*=\s*(["\'])(.*?)\1/iu', static function (array $match): string {
            $style = self::sanitizeStyle($match[2]);
            return $style !== '' ? ' style="' . $style . '"' : '';
        }, $content) ?? $content;
        $content = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2/iu', '$1="#"', $content) ?? $content;

        $content = preg_replace_callback('/<img\b[^>]*>/iu', static function (array $match): string {
            $tag = $match[0];
            if (!preg_match('/\ssrc\s*=\s*(["\'])(.*?)\1/iu', $tag, $srcMatch)) {
                return '';
            }

            $src = trim(html_entity_decode($srcMatch[2], ENT_QUOTES, 'UTF-8'));
            $isAllowedSource = preg_match('#^(data:image/(png|jpe?g|webp|gif);base64,|/|img/|uploads/)#i', $src);
            if (!$isAllowedSource) {
                return '';
            }

            $alt = '';
            if (preg_match('/\salt\s*=\s*(["\'])(.*?)\1/iu', $tag, $altMatch)) {
                $alt = $altMatch[2];
            }

            $sizeAttrs = '';
            foreach (['width', 'height'] as $dimension) {
                if (preg_match('/\s' . $dimension . '\s*=\s*(["\']?)(\d{1,4})\1/iu', $tag, $dimensionMatch)) {
                    $sizeAttrs .= ' ' . $dimension . '="' . (int)$dimensionMatch[2] . '"';
                }
            }

            return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '"' . $sizeAttrs . '>';
        }, $content) ?? $content;

        $content = preg_replace_callback('/<a\b[^>]*>/iu', static function (array $match): string {
            $tag = $match[0];
            $href = '#';
            if (preg_match('/\shref\s*=\s*(["\'])(.*?)\1/iu', $tag, $hrefMatch)) {
                $candidate = trim(html_entity_decode($hrefMatch[2], ENT_QUOTES, 'UTF-8'));
                if (preg_match('#^(https?://|/|mailto:)#i', $candidate)) {
                    $href = $candidate;
                }
            }

            return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';
        }, $content) ?? $content;

        $content = preg_replace_callback('/<([a-z][a-z0-9]*)(\s[^>]*)?>/iu', static function (array $match): string {
            $tag = strtolower($match[1]);
            if ($tag === 'a' || $tag === 'img') {
                return $match[0];
            }

            $attributes = $match[2] ?? '';
            $attributeText = '';
            if (preg_match('/\sstyle\s*=\s*(["\'])(.*?)\1/iu', $attributes, $styleMatch)) {
                $style = self::sanitizeStyle($styleMatch[2]);
                if ($style !== '') {
                    $attributeText .= ' style="' . $style . '"';
                }
            }

            if (($tag === 'td' || $tag === 'th') && preg_match('/\scolspan\s*=\s*(["\']?)(\d{1,2})\1/iu', $attributes, $colspanMatch)) {
                $attributeText .= ' colspan="' . (int)$colspanMatch[2] . '"';
            }
            if (($tag === 'td' || $tag === 'th') && preg_match('/\srowspan\s*=\s*(["\']?)(\d{1,2})\1/iu', $attributes, $rowspanMatch)) {
                $attributeText .= ' rowspan="' . (int)$rowspanMatch[2] . '"';
            }

            return '<' . $tag . $attributeText . '>';
        }, $content) ?? $content;

        return trim($content);
    }

    private static function sanitizeStyle(string $style): string
    {
        $allowed = [];
        foreach (explode(';', $style) as $declaration) {
            $parts = explode(':', $declaration, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $property = strtolower(trim($parts[0]));
            $value = trim($parts[1]);
            if ($value === '' || preg_match('/expression|javascript:|url\s*\(/iu', $value)) {
                continue;
            }

            if ($property === 'text-align' && preg_match('/^(left|center|right|justify)$/iu', $value)) {
                $allowed[] = 'text-align: ' . strtolower($value);
            } elseif (($property === 'color' || $property === 'background-color') && self::isSafeCssColor($value)) {
                $allowed[] = $property . ': ' . $value;
            } elseif ($property === 'font-weight' && preg_match('/^(normal|bold|[1-9]00)$/iu', $value)) {
                $allowed[] = 'font-weight: ' . strtolower($value);
            } elseif ($property === 'font-style' && preg_match('/^(normal|italic)$/iu', $value)) {
                $allowed[] = 'font-style: ' . strtolower($value);
            } elseif ($property === 'text-decoration' && preg_match('/^(none|underline|line-through)(\s+(underline|line-through))*$/iu', $value)) {
                $allowed[] = 'text-decoration: ' . strtolower($value);
            } elseif ($property === 'font-size' && preg_match('/^\d{1,2}(\.\d{1,2})?(pt|px|em|rem|%)$/iu', $value)) {
                $allowed[] = 'font-size: ' . $value;
            } elseif ($property === 'font-family' && preg_match('/^[\p{L}\p{N}\s,\"\'._-]{1,120}$/u', $value)) {
                $allowed[] = 'font-family: ' . $value;
            } elseif (($property === 'margin-left' || $property === 'padding-left') && preg_match('/^\d{1,3}(\.\d{1,2})?(pt|px|em|rem|%)$/iu', $value)) {
                $allowed[] = $property . ': ' . $value;
            } elseif (($property === 'width' || $property === 'height') && preg_match('/^\d{1,4}(\.\d{1,2})?(pt|px|%)$/iu', $value)) {
                $allowed[] = $property . ': ' . $value;
            } elseif ($property === 'border' && preg_match('/^[\d\.]+(px|pt)\s+(solid|dashed|dotted)\s+[#a-z0-9(),\s\.]+$/iu', $value)) {
                $allowed[] = 'border: ' . $value;
            }
        }

        return implode('; ', array_unique($allowed));
    }

    private static function isSafeCssColor(string $value): bool
    {
        return (bool)preg_match('/^(#[0-9a-f]{3,8}|rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\)|rgba\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*(0|1|0?\.\d+)\s*\)|[a-z]{3,24})$/iu', trim($value));
    }

    private static function dateTimeOrNow(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return date('Y-m-d H:i:s');
        }

        return str_replace('T', ' ', $value) . (strlen($value) === 16 ? ':00' : '');
    }
}
