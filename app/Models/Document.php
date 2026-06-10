<?php

namespace App\Models;

use PDO;

class Document
{
    public static function all(string $query = '', string $type = '', string $year = ''): array
    {
        self::ensureTables();

        $conditions = [];
        $parameters = [];

        if ($query !== '') {
            $conditions[] = '(title LIKE ? OR document_number LIKE ? OR description LIKE ?)';
            $term = '%' . $query . '%';
            $parameters = [$term, $term, $term];
        }

        if ($type !== '') {
            $conditions[] = 'document_type = ?';
            $parameters[] = $type;
        }

        if ($year !== '') {
            $conditions[] = 'YEAR(issued_date) = ?';
            $parameters[] = (int)$year;
        }

        $sql = 'SELECT * FROM documents';
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY issued_date DESC, created_at DESC, id DESC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);

        return self::withAttachments($statement->fetchAll());
    }

    public static function types(): array
    {
        self::ensureTables();

        return Database::connection()
            ->query("SELECT DISTINCT document_type FROM documents WHERE document_type <> '' ORDER BY document_type ASC")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function years(): array
    {
        self::ensureTables();

        return Database::connection()
            ->query('SELECT DISTINCT YEAR(issued_date) AS year FROM documents WHERE issued_date IS NOT NULL ORDER BY year DESC')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function count(): int
    {
        self::ensureTables();

        return (int)Database::connection()->query('SELECT COUNT(*) FROM documents')->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        self::ensureTables();

        $statement = Database::connection()->prepare('SELECT * FROM documents WHERE id = ? LIMIT 1');
        $statement->execute([$id]);
        $document = $statement->fetch();

        return $document ? self::withAttachments([$document])[0] : null;
    }

    public static function findAttachment(int $id): ?array
    {
        self::ensureTables();

        $statement = Database::connection()->prepare('SELECT * FROM document_files WHERE id = ? LIMIT 1');
        $statement->execute([$id]);
        $file = $statement->fetch();

        return $file ?: null;
    }

    public static function firstAttachment(int $documentId): ?array
    {
        self::ensureTables();

        $statement = Database::connection()->prepare('SELECT * FROM document_files WHERE document_id = ? ORDER BY id ASC LIMIT 1');
        $statement->execute([$documentId]);
        $file = $statement->fetch();

        return $file ?: null;
    }

    public static function create(array $data, array $files): void
    {
        self::ensureTables();
        $connection = Database::connection();
        $firstFile = $files[0];

        $connection->beginTransaction();
        try {
            $statement = $connection->prepare(
                'INSERT INTO documents (title, document_number, document_type, issued_date, description, original_name, file_path, file_size, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $statement->execute([
                trim((string)$data['title']),
                trim((string)($data['document_number'] ?? '')),
                trim((string)($data['document_type'] ?? '')),
                self::dateOrNull((string)($data['issued_date'] ?? '')),
                trim((string)($data['description'] ?? '')),
                trim((string)$firstFile['original_name']),
                trim((string)$firstFile['file_path']),
                (int)$firstFile['file_size'],
                trim((string)$firstFile['mime_type']),
            ]);

            $documentId = (int)$connection->lastInsertId();
            $fileStatement = $connection->prepare(
                'INSERT INTO document_files (document_id, original_name, file_path, file_size, mime_type) VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($files as $file) {
                $fileStatement->execute([
                    $documentId,
                    trim((string)$file['original_name']),
                    trim((string)$file['file_path']),
                    (int)$file['file_size'],
                    trim((string)$file['mime_type']),
                ]);
            }

            $connection->commit();
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
    }

    public static function update(int $id, array $data, ?array $files = null): ?array
    {
        self::ensureTables();
        $document = self::find($id);
        if (!$document) {
            return null;
        }

        $connection = Database::connection();
        $replacedFiles = [];

        $connection->beginTransaction();
        try {
            $statement = $connection->prepare(
                'UPDATE documents SET title = ?, document_number = ?, document_type = ?, issued_date = ?, description = ? WHERE id = ?'
            );
            $statement->execute([
                trim((string)$data['title']),
                trim((string)($data['document_number'] ?? '')),
                trim((string)($data['document_type'] ?? '')),
                self::dateOrNull((string)($data['issued_date'] ?? '')),
                trim((string)($data['description'] ?? '')),
                $id,
            ]);

            if ($files && count($files) > 0) {
                $firstFile = $files[0];
                $summaryStatement = $connection->prepare(
                    'UPDATE documents SET original_name = ?, file_path = ?, file_size = ?, mime_type = ? WHERE id = ?'
                );
                $summaryStatement->execute([
                    trim((string)$firstFile['original_name']),
                    trim((string)$firstFile['file_path']),
                    (int)$firstFile['file_size'],
                    trim((string)$firstFile['mime_type']),
                    $id,
                ]);

                $deleteFilesStatement = $connection->prepare('DELETE FROM document_files WHERE document_id = ?');
                $deleteFilesStatement->execute([$id]);

                $insertFileStatement = $connection->prepare(
                    'INSERT INTO document_files (document_id, original_name, file_path, file_size, mime_type) VALUES (?, ?, ?, ?, ?)'
                );
                foreach ($files as $file) {
                    $insertFileStatement->execute([
                        $id,
                        trim((string)$file['original_name']),
                        trim((string)$file['file_path']),
                        (int)$file['file_size'],
                        trim((string)$file['mime_type']),
                    ]);
                }

                $replacedFiles = $document['attachments'] ?? [];
            }

            $connection->commit();
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }

        return $replacedFiles;
    }

    public static function delete(int $id): ?array
    {
        $document = self::find($id);
        if (!$document) {
            return null;
        }

        $statement = Database::connection()->prepare('DELETE FROM documents WHERE id = ?');
        $statement->execute([$id]);

        return $document;
    }

    private static function withAttachments(array $documents): array
    {
        if (!$documents) {
            return [];
        }

        $ids = array_column($documents, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $statement = Database::connection()->prepare(
            "SELECT * FROM document_files WHERE document_id IN ({$placeholders}) ORDER BY id ASC"
        );
        $statement->execute($ids);
        $filesByDocument = [];
        foreach ($statement->fetchAll() as $file) {
            $filesByDocument[$file['document_id']][] = $file;
        }

        foreach ($documents as &$document) {
            $document['attachments'] = $filesByDocument[$document['id']] ?? [];
        }

        return $documents;
    }

    private static function ensureTables(): void
    {
        $connection = Database::connection();
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS documents (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                document_number VARCHAR(120) DEFAULT '',
                document_type VARCHAR(120) DEFAULT '',
                issued_date DATE DEFAULT NULL,
                description TEXT NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
                mime_type VARCHAR(120) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX documents_issued_date_index (issued_date),
                INDEX documents_type_index (document_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS document_files (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                document_id BIGINT UNSIGNED NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
                mime_type VARCHAR(120) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT document_files_document_id_fk FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
                UNIQUE KEY document_files_path_unique (file_path),
                INDEX document_files_document_index (document_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $connection->exec(
            "INSERT INTO document_files (document_id, original_name, file_path, file_size, mime_type)
             SELECT d.id, d.original_name, d.file_path, d.file_size, d.mime_type
             FROM documents d
             LEFT JOIN document_files f ON f.file_path = d.file_path
             WHERE d.file_path <> '' AND f.id IS NULL"
        );
    }

    private static function dateOrNull(string $date): ?string
    {
        $date = trim($date);
        return $date !== '' ? $date : null;
    }
}
