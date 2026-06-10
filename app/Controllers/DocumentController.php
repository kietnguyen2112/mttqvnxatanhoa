<?php

namespace App\Controllers;

use App\Models\Document;

class DocumentController extends BaseController
{
    public function index(): void
    {
        $query = trim((string)($_GET['q'] ?? ''));
        $type = trim((string)($_GET['type'] ?? ''));
        $year = trim((string)($_GET['year'] ?? ''));
        $allowedLimits = [10, 20, 50];
        $limit = (int)($_GET['limit'] ?? 10);
        if (!in_array($limit, $allowedLimits, true)) {
            $limit = 10;
        }
        $page = max(1, (int)($_GET['page'] ?? 1));
        $documents = Document::all($query, $type, $year);
        $total = count($documents);
        $totalPages = max(1, (int)ceil($total / $limit));
        $page = min($page, $totalPages);
        $documents = array_slice($documents, ($page - 1) * $limit, $limit);

        $this->view('documents/index', [
            'title' => 'Văn bản',
            'metaDescription' => 'Tra cứu, xem trước và tải văn bản công bố của Ủy ban MTTQ Việt Nam xã Tân Hòa theo từ khóa, loại văn bản và năm ban hành.',
            'documents' => $documents,
            'documentTypes' => Document::types(),
            'documentYears' => Document::years(),
            'query' => $query,
            'selectedType' => $type,
            'selectedYear' => $year,
            'limit' => $limit,
            'total' => $total,
            'libraryTotal' => Document::count(),
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function download(): void
    {
        $this->sendDocumentFile(true);
    }

    public function preview(): void
    {
        $this->sendDocumentFile(false);
    }

    private function sendDocumentFile(bool $download): void
    {
        $fileId = (int)($_GET['file'] ?? 0);
        $file = $fileId > 0
            ? Document::findAttachment($fileId)
            : Document::firstAttachment((int)($_GET['id'] ?? 0));
        $publicRoot = realpath(__DIR__ . '/../../public');
        $relativePath = ltrim((string)($file['file_path'] ?? ''), '/');
        $filePath = $publicRoot ? realpath($publicRoot . '/' . $relativePath) : false;
        $documentRoot = $publicRoot ? realpath($publicRoot . '/uploads/documents') : false;

        if (!$file || !$filePath || !$documentRoot || !str_starts_with($filePath, $documentRoot . DIRECTORY_SEPARATOR)) {
            http_response_code(404);
            exit('Không tìm thấy văn bản.');
        }

        $originalName = preg_replace('/[\r\n"]+/', '', basename((string)$file['original_name']));
        $fallbackName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'van-ban';

        $mimeType = preg_match('/^[A-Za-z0-9.+-]+\/[A-Za-z0-9.+-]+$/', (string)$file['mime_type'])
            ? $file['mime_type']
            : 'application/octet-stream';

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        $disposition = $download ? 'attachment' : 'inline';
        header("Content-Disposition: {$disposition}; filename=\"{$fallbackName}\"; filename*=UTF-8''" . rawurlencode($originalName));
        readfile($filePath);
        exit;
    }
}
