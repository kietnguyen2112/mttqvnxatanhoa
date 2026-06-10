<?php

namespace App\Models;

use PDO;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class ExcelImporter
{
    public static function types(): array
    {
        return [
            'leaders' => [
                'label' => 'Cán bộ',
                'fields' => ['organization', 'full_name', 'position', 'phone', 'email', 'sort_order'],
                'required' => ['organization', 'full_name', 'position'],
                'example' => 'Tổ chức, Họ và tên, Chức vụ, Điện thoại, Email, Thứ tự',
            ],
            'hamlet_members' => [
                'label' => 'Thành viên / Ban Công tác Mặt trận ấp',
                'fields' => ['organization', 'hamlet_name', 'full_name', 'birth_date', 'role', 'phone', 'note', 'sort_order'],
                'required' => ['organization', 'hamlet_name', 'full_name', 'role'],
                'example' => 'Tổ chức, Chi đoàn/chi hội/ấp, Họ và tên, Ngày sinh, Vai trò, Điện thoại, Ghi chú, Thứ tự',
            ],
            'loan_groups' => [
                'label' => 'Tổ vay vốn',
                'fields' => ['organization', 'hamlet_name', 'name', 'leader_name', 'leader_phone', 'customer_count', 'fund_source', 'outstanding_amount', 'savings_amount', 'overdue_amount', 'rating', 'note'],
                'required' => ['organization', 'hamlet_name', 'name', 'leader_name'],
                'example' => 'Tổ chức, Tên ấp, Tên tổ vay vốn, Tên tổ trưởng, Điện thoại tổ trưởng, Số khách hàng, Nguồn vốn, Dư nợ, Tiền gửi, Nợ quá hạn, Xếp loại tổ, Ghi chú',
            ],
            'loan_members' => [
                'label' => 'Thành viên tổ vay vốn',
                'fields' => ['loan_group', 'full_name', 'role', 'phone', 'loan_amount', 'outstanding_amount', 'overdue_amount', 'purpose', 'note', 'sort_order'],
                'required' => ['loan_group', 'full_name'],
                'example' => 'Tổ vay vốn, Họ và tên, Vai trò, Điện thoại, Số tiền vay ban đầu, Dư nợ, Nợ quá hạn, Mục đích vay, Ghi chú, Thứ tự',
            ],
        ];
    }

    public static function import(string $type, string $path): array
    {
        $types = self::types();
        if (!isset($types[$type])) {
            throw new RuntimeException('Loại dữ liệu import không hợp lệ.');
        }

        $rows = self::readFirstSheet($path);
        if (count($rows) < 2) {
            throw new RuntimeException('File XLSX cần có dòng tiêu đề và ít nhất một dòng dữ liệu.');
        }

        $headers = self::normalizeHeaders(array_shift($rows));
        $result = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $data = self::rowToData($headers, $row);

            if (self::isBlankRow($data)) {
                continue;
            }

            $missing = self::missingRequired($data, $types[$type]['required']);
            if ($missing) {
                $result['skipped']++;
                $result['errors'][] = 'Dòng ' . $line . ': thiếu ' . implode(', ', $missing) . '.';
                continue;
            }

            try {
                self::importRow($type, $data);
                $result['imported']++;
            } catch (RuntimeException $exception) {
                $result['skipped']++;
                $result['errors'][] = 'Dòng ' . $line . ': ' . $exception->getMessage();
            }
        }

        return $result;
    }

    private static function importRow(string $type, array $data): void
    {
        match ($type) {
            'leaders' => self::importLeader($data),
            'hamlet_members' => self::importHamletMember($data),
            'loan_groups' => self::importLoanGroup($data),
            'loan_members' => self::importLoanMember($data),
            default => throw new RuntimeException('Loại dữ liệu import không hợp lệ.'),
        };
    }

    private static function importLeader(array $data): void
    {
        $organizationId = self::organizationId($data['organization']);
        $stmt = Database::connection()->prepare(
            'INSERT INTO organization_leaders (organization_id, full_name, position, phone, email, sort_order)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE phone = VALUES(phone), email = VALUES(email), sort_order = VALUES(sort_order)'
        );
        $stmt->execute([
            $organizationId,
            self::text($data['full_name']),
            self::text($data['position']),
            self::text($data['phone'] ?? ''),
            self::text($data['email'] ?? ''),
            self::integer($data['sort_order'] ?? 0),
        ]);
    }

    private static function importHamletMember(array $data): void
    {
        $organizationId = self::organizationId($data['organization']);
        $stmt = Database::connection()->prepare(
            'INSERT INTO hamlet_members (organization_id, hamlet_name, full_name, birth_date, role, phone, note, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE birth_date = VALUES(birth_date), phone = VALUES(phone), note = VALUES(note), sort_order = VALUES(sort_order)'
        );
        $stmt->execute([
            $organizationId,
            self::text($data['hamlet_name']),
            self::text($data['full_name']),
            self::text($data['birth_date'] ?? '') ?: null,
            self::text($data['role']),
            self::text($data['phone'] ?? ''),
            self::text($data['note'] ?? ''),
            self::integer($data['sort_order'] ?? 0),
        ]);
    }

    private static function importLoanGroup(array $data): void
    {
        $organizationId = self::organizationId($data['organization']);
        $existingId = self::loanGroupIdByIdentity($organizationId, self::text($data['hamlet_name']), self::text($data['name']));

        if ($existingId) {
            $stmt = Database::connection()->prepare(
                'UPDATE loan_groups SET leader_name = ?, leader_phone = ?, customer_count = ?, fund_source = ?, outstanding_amount = ?, savings_amount = ?, overdue_amount = ?, rating = ?, note = ? WHERE id = ?'
            );
            $stmt->execute([
                self::text($data['leader_name']),
                self::text($data['leader_phone'] ?? ''),
                self::integer($data['customer_count'] ?? 0),
                self::text($data['fund_source'] ?? ''),
                self::money($data['outstanding_amount'] ?? 0),
                self::money($data['savings_amount'] ?? 0),
                self::money($data['overdue_amount'] ?? 0),
                self::text($data['rating'] ?? ''),
                self::text($data['note'] ?? ''),
                $existingId,
            ]);
            return;
        }

        $stmt = Database::connection()->prepare(
            'INSERT INTO loan_groups (organization_id, hamlet_name, name, leader_name, leader_phone, customer_count, fund_source, outstanding_amount, savings_amount, overdue_amount, rating, note)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $organizationId,
            self::text($data['hamlet_name']),
            self::text($data['name']),
            self::text($data['leader_name']),
            self::text($data['leader_phone'] ?? ''),
            self::integer($data['customer_count'] ?? 0),
            self::text($data['fund_source'] ?? ''),
            self::money($data['outstanding_amount'] ?? 0),
            self::money($data['savings_amount'] ?? 0),
            self::money($data['overdue_amount'] ?? 0),
            self::text($data['rating'] ?? ''),
            self::text($data['note'] ?? ''),
        ]);
    }

    private static function importLoanMember(array $data): void
    {
        $loanGroupId = self::loanGroupId($data['loan_group']);
        $fullName = self::text($data['full_name']);
        $role = self::text($data['role'] ?? 'Thành viên') ?: 'Thành viên';
        $existingId = self::loanMemberIdByIdentity($loanGroupId, $fullName, $role);

        if ($existingId) {
            $stmt = Database::connection()->prepare(
                'UPDATE loan_group_members SET phone = ?, loan_amount = ?, outstanding_amount = ?, overdue_amount = ?, purpose = ?, note = ?, sort_order = ? WHERE id = ?'
            );
            $stmt->execute([
                self::text($data['phone'] ?? ''),
                self::money($data['loan_amount'] ?? 0),
                self::money($data['outstanding_amount'] ?? ($data['loan_amount'] ?? 0)),
                self::money($data['overdue_amount'] ?? 0),
                self::text($data['purpose'] ?? ''),
                self::text($data['note'] ?? ''),
                self::integer($data['sort_order'] ?? 0),
                $existingId,
            ]);
            return;
        }

        $stmt = Database::connection()->prepare(
            'INSERT INTO loan_group_members (loan_group_id, full_name, role, phone, loan_amount, outstanding_amount, overdue_amount, purpose, note, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $loanGroupId,
            $fullName,
            $role,
            self::text($data['phone'] ?? ''),
            self::money($data['loan_amount'] ?? 0),
            self::money($data['outstanding_amount'] ?? ($data['loan_amount'] ?? 0)),
            self::money($data['overdue_amount'] ?? 0),
            self::text($data['purpose'] ?? ''),
            self::text($data['note'] ?? ''),
            self::integer($data['sort_order'] ?? 0),
        ]);
    }

    private static function organizationId(string $value): int
    {
        $value = self::text($value);
        $stmt = Database::connection()->prepare(
            'SELECT id FROM organizations WHERE id = ? OR short_name = ? OR name = ? OR slug = ? LIMIT 1'
        );
        $stmt->execute([(int)$value, $value, $value, $value]);
        $id = $stmt->fetchColumn();

        if (!$id) {
            throw new RuntimeException('không tìm thấy hội/tổ chức "' . $value . '".');
        }

        return (int)$id;
    }

    private static function loanGroupId(string $value): int
    {
        $value = self::text($value);
        $stmt = Database::connection()->prepare('SELECT id FROM loan_groups WHERE id = ? OR name = ? LIMIT 1');
        $stmt->execute([(int)$value, $value]);
        $id = $stmt->fetchColumn();

        if (!$id) {
            throw new RuntimeException('không tìm thấy tổ vay vốn "' . $value . '".');
        }

        return (int)$id;
    }

    private static function loanGroupIdByIdentity(int $organizationId, string $hamletName, string $name): ?int
    {
        $stmt = Database::connection()->prepare(
            'SELECT id FROM loan_groups WHERE organization_id = ? AND hamlet_name = ? AND name = ? LIMIT 1'
        );
        $stmt->execute([$organizationId, $hamletName, $name]);
        $id = $stmt->fetchColumn();

        return $id ? (int)$id : null;
    }

    private static function loanMemberIdByIdentity(int $loanGroupId, string $fullName, string $role): ?int
    {
        $stmt = Database::connection()->prepare(
            'SELECT id FROM loan_group_members WHERE loan_group_id = ? AND full_name = ? AND role = ? LIMIT 1'
        );
        $stmt->execute([$loanGroupId, $fullName, $role]);
        $id = $stmt->fetchColumn();

        return $id ? (int)$id : null;
    }

    private static function readFirstSheet(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Không mở được file XLSX.');
        }

        $sharedStrings = self::sharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('Không tìm thấy sheet đầu tiên trong file XLSX.');
        }

        $xml = new SimpleXMLElement($sheetXml);
        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $rowValues = [];
            foreach ($row->c as $cell) {
                $ref = (string)$cell['r'];
                $index = self::columnIndex($ref);
                $rowValues[$index] = self::cellValue($cell, $sharedStrings);
            }

            if (!$rowValues) {
                $rows[] = [];
                continue;
            }

            $max = max(array_keys($rowValues));
            $values = [];
            for ($i = 0; $i <= $max; $i++) {
                $values[] = $rowValues[$i] ?? '';
            }
            $rows[] = $values;
        }

        return $rows;
    }

    private static function sharedStrings(ZipArchive $zip): array
    {
        $content = $zip->getFromName('xl/sharedStrings.xml');
        if ($content === false) {
            return [];
        }

        $xml = new SimpleXMLElement($content);
        $strings = [];
        foreach ($xml->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string)$item->t;
                continue;
            }

            $text = '';
            foreach ($item->r as $run) {
                $text .= (string)$run->t;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private static function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string)$cell['t'];

        if ($type === 'inlineStr') {
            return trim((string)($cell->is->t ?? ''));
        }

        $value = (string)($cell->v ?? '');

        if ($type === 's') {
            return trim($sharedStrings[(int)$value] ?? '');
        }

        return trim($value);
    }

    private static function columnIndex(string $reference): int
    {
        preg_match('/^[A-Z]+/i', $reference, $match);
        $letters = strtoupper($match[0] ?? 'A');
        $index = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    private static function normalizeHeaders(array $headers): array
    {
        return array_map(fn ($header) => self::fieldName((string)$header), $headers);
    }

    private static function rowToData(array $headers, array $row): array
    {
        $data = [];
        foreach ($headers as $index => $field) {
            if ($field === '') {
                continue;
            }
            $data[$field] = self::text($row[$index] ?? '');
        }

        return $data;
    }

    private static function fieldName(string $header): string
    {
        $key = self::asciiKey($header);
        $aliases = [
            'organization' => ['organization', 'organization_id', 'hoi', 'to_chuc', 'don_vi', 'ma_hoi'],
            'loan_group' => ['loan_group', 'loan_group_id', 'to_vay_von', 'ten_to', 'ma_to'],
            'hamlet_name' => ['hamlet_name', 'ap', 'ten_ap', 'chi_doan_chi_hoi', 'ten_chi_doan_chi_hoi', 'ten_chi_doan_chi_hoi_ap'],
            'full_name' => ['full_name', 'ho_ten', 'ho_va_ten', 'ten'],
            'position' => ['position', 'chuc_vu'],
            'role' => ['role', 'vai_tro', 'chuc_vu_tai_ap', 'chuc_vu_tai_chi_doan_chi_hoi'],
            'phone' => ['phone', 'dien_thoai', 'so_dien_thoai', 'sdt'],
            'birth_date' => ['birth_date', 'ngay_sinh', 'ngay_thang_nam_sinh'],
            'email' => ['email', 'thu_dien_tu'],
            'sort_order' => ['sort_order', 'thu_tu', 'stt'],
            'note' => ['note', 'ghi_chu'],
            'name' => ['name', 'ten', 'ten_to_vay_von'],
            'leader_name' => ['leader_name', 'to_truong', 'ten_to_truong'],
            'leader_phone' => ['leader_phone', 'dien_thoai_to_truong', 'sdt_to_truong'],
            'customer_count' => ['customer_count', 'so_khach_hang', 'khach_hang', 'so_thanh_vien', 'thanh_vien'],
            'fund_source' => ['fund_source', 'nguon_von', 'ngan_hang'],
            'overdue_amount' => ['overdue_amount', 'no_qua_han', 'no_qua_han_dong', 'no_qua_han_vnd'],
            'savings_amount' => ['savings_amount', 'tien_gui', 'tien_gui_vnd'],
            'rating' => ['rating', 'xep_loai', 'xep_loai_to'],
            'loan_amount' => ['loan_amount', 'so_tien_vay', 'so_tien'],
            'outstanding_amount' => ['outstanding_amount', 'du_no', 'du_no_vnd'],
            'purpose' => ['purpose', 'muc_dich', 'muc_dich_vay'],
        ];

        foreach ($aliases as $field => $names) {
            if (in_array($key, $names, true)) {
                return $field;
            }
        }

        return $key;
    }

    private static function asciiKey(string $value): string
    {
        $value = trim(mb_strtolower($value, 'UTF-8'));
        $from = ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ','è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ','ì','í','ị','ỉ','ĩ','ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ','ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ','ỳ','ý','ỵ','ỷ','ỹ','đ'];
        $to = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','d'];
        $value = str_replace($from, $to, $value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';

        return trim($value, '_');
    }

    private static function missingRequired(array $data, array $required): array
    {
        return array_values(array_filter($required, fn ($field) => self::text($data[$field] ?? '') === ''));
    }

    private static function isBlankRow(array $data): bool
    {
        foreach ($data as $value) {
            if (self::text($value) !== '') {
                return false;
            }
        }

        return true;
    }

    private static function text(mixed $value): string
    {
        return trim((string)$value);
    }

    private static function integer(mixed $value): int
    {
        return (int)preg_replace('/[^0-9-]/', '', (string)$value);
    }

    private static function money(mixed $value): float
    {
        $value = str_replace(['.', ','], ['', '.'], (string)$value);
        return (float)preg_replace('/[^0-9.]/', '', $value);
    }
}
