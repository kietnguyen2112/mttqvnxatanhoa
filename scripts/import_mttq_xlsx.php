<?php

require __DIR__ . '/../app/Models/Database.php';

use App\Models\Database;

if (($argv[1] ?? '') === '') {
    fwrite(STDERR, "Usage: php scripts/import_mttq_xlsx.php /path/to/file.xlsx\n");
    exit(1);
}

$xlsxPath = $argv[1];
if (!is_file($xlsxPath)) {
    fwrite(STDERR, "File not found: {$xlsxPath}\n");
    exit(1);
}

final class XlsxReader
{
    private ZipArchive $zip;
    /** @var string[] */
    private array $sharedStrings = [];

    public function __construct(private readonly string $path)
    {
        $this->zip = new ZipArchive();
        if ($this->zip->open($this->path) !== true) {
            throw new RuntimeException('Unable to open workbook.');
        }
        $this->loadSharedStrings();
    }

    /** @return array<string, array<int, array<int, string>>> */
    public function sheets(): array
    {
        $workbook = $this->xml('xl/workbook.xml');
        $rels = $this->xml('xl/_rels/workbook.xml.rels');
        $relationshipTargets = [];
        foreach ($rels->Relationship as $relationship) {
            $relationshipTargets[(string)$relationship['Id']] = ltrim((string)$relationship['Target'], '/');
        }

        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $result = [];
        foreach ($workbook->sheets->sheet as $sheet) {
            $name = trim((string)$sheet['name']);
            $attributes = $sheet->attributes('r', true);
            $target = $relationshipTargets[(string)$attributes['id']] ?? '';
            if ($target === '') {
                continue;
            }
            if (!str_starts_with($target, 'xl/')) {
                $target = 'xl/' . $target;
            }
            $result[$name] = $this->rows($target);
        }

        return $result;
    }

    private function loadSharedStrings(): void
    {
        $raw = $this->zip->getFromName('xl/sharedStrings.xml');
        if ($raw === false) {
            return;
        }
        $xml = simplexml_load_string($raw);
        if (!$xml) {
            return;
        }
        $xml->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        foreach ($xml->si as $item) {
            $parts = [];
            $item->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($item->xpath('.//a:t') ?: [] as $text) {
                $parts[] = (string)$text;
            }
            $this->sharedStrings[] = implode('', $parts);
        }
    }

    private function xml(string $name): SimpleXMLElement
    {
        $raw = $this->zip->getFromName($name);
        if ($raw === false) {
            throw new RuntimeException("Missing workbook part: {$name}");
        }
        $xml = simplexml_load_string($raw);
        if (!$xml) {
            throw new RuntimeException("Invalid workbook XML: {$name}");
        }
        return $xml;
    }

    /** @return array<int, array<int, string>> */
    private function rows(string $target): array
    {
        $xml = $this->xml($target);
        $rows = [];
        $xml->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        foreach ($xml->xpath('//a:sheetData/a:row') ?: [] as $row) {
            $cells = [];
            $maxIndex = -1;
            $row->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($row->xpath('a:c') ?: [] as $cell) {
                $index = self::columnIndex((string)$cell['r']);
                $cell->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $values = $cell->xpath('a:v') ?: [];
                $value = $values ? (string)$values[0] : '';
                if ((string)$cell['t'] === 's' && $value !== '') {
                    $value = $this->sharedStrings[(int)$value] ?? $value;
                }
                $cells[$index] = self::clean($value);
                $maxIndex = max($maxIndex, $index);
            }

            if ($maxIndex < 0 || !array_filter($cells, static fn (string $value): bool => $value !== '')) {
                continue;
            }

            $normalized = [];
            for ($index = 0; $index <= $maxIndex; $index++) {
                $normalized[] = $cells[$index] ?? '';
            }
            $rows[] = $normalized;
        }

        return $rows;
    }

    private static function columnIndex(string $cellRef): int
    {
        preg_match('/^([A-Z]+)/', $cellRef, $match);
        $index = 0;
        foreach (str_split($match[1] ?? 'A') as $char) {
            $index = ($index * 26) + ord($char) - 64;
        }
        return $index - 1;
    }

    public static function clean(string $value): string
    {
        $value = str_replace("\r", "\n", $value);
        $value = preg_replace('/[ \t]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/ *\n */u', "\n", $value) ?? $value;
        return trim($value);
    }
}

function number_value(string $value): float
{
    $value = trim(str_replace(',', '', $value));
    if ($value === '') {
        return 0.0;
    }
    return is_numeric($value) ? (float)$value : 0.0;
}

/** @return array<int, array{title:string, rows:array<int, array<int, string>>}> */
function sections(array $rows): array
{
    $sections = [];
    foreach ($rows as $row) {
        $firstCell = $row[0] ?? '';
        if (preg_match('/^\d+\.\s+/u', $firstCell)) {
            $sections[] = ['title' => $firstCell, 'rows' => []];
            continue;
        }
        if ($sections) {
            $sections[array_key_last($sections)]['rows'][] = $row;
        }
    }
    return $sections;
}

function is_data_row(array $row): bool
{
    return preg_match('/^\d+(?:\.0)?$|^0\d+$/', $row[0] ?? '') === 1;
}

function ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
    }
}

$pdo = Database::connection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

ensure_column($pdo, 'organization_chapters', 'household_count', "int unsigned NOT NULL DEFAULT 0 AFTER `name`");
ensure_column($pdo, 'organization_chapters', 'male_count', "int unsigned NOT NULL DEFAULT 0 AFTER `member_count`");
ensure_column($pdo, 'organization_chapters', 'female_count', "int unsigned NOT NULL DEFAULT 0 AFTER `male_count`");
ensure_column($pdo, 'loan_groups', 'customer_count', "int unsigned NOT NULL DEFAULT 0 AFTER `leader_phone`");
ensure_column($pdo, 'loan_groups', 'outstanding_amount', "decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `fund_source`");
ensure_column($pdo, 'loan_groups', 'savings_amount', "decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `outstanding_amount`");
ensure_column($pdo, 'loan_groups', 'rating', "varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT '' AFTER `overdue_amount`");

$sheetMap = [
    'HỘI NÔNG DÂN' => ['slug' => 'hoi-nong-dan', 'loanName' => 'Hội Nông dân'],
    'HỘI CỰU CHIẾN BINH' => ['slug' => 'hoi-cuu-chien-binh', 'loanName' => 'Hội CCB'],
    'HỘI PHỤ NỮ' => ['slug' => 'hoi-lien-hiep-phu-nu', 'loanName' => 'Phụ nữ'],
    'ĐOÀN THANH NIÊN XÃ' => ['slug' => 'doan-thanh-nien', 'loanName' => 'Thanh niên'],
];

$primaryLeaderOverrides = [
    'HỘI NÔNG DÂN' => [
        'name' => 'Nguyễn Vĩnh Thọ',
        'position' => 'Uỷ viên BCH Đảng bộ - Phó Chủ tịch TT UBMTTQVN - Chủ tịch Hội Nông dân xã',
        'phone' => '0905 555 001',
        'avatar' => 'img/hoi-nong-dan.jpg',
    ],
    'HỘI CỰU CHIẾN BINH' => [
        'name' => 'Đặng Minh Thắng',
        'position' => 'Phó Chủ tịch MTTQVN xã - Chủ tịch Hội Cựu chiến binh',
        'phone' => '0904 444 001',
        'avatar' => 'img/cuu-chien-binh.jpg',
    ],
    'HỘI PHỤ NỮ' => [
        'name' => 'Bùi Thị Hồng Thơm',
        'position' => 'Uỷ viên BCH Đảng bộ - Phó Chủ tịch UBMTTQVN - Chủ tịch Hội Liên Hiệp Phụ nữ xã',
        'phone' => '0903 333 001',
        'avatar' => 'img/hoi-phu-nu.png',
    ],
    'ĐOÀN THANH NIÊN XÃ' => [
        'name' => 'Nguyễn Ngọc Công Hữu',
        'aliases' => ['Nguyễn Ngọc Công Hữu'],
        'position' => 'Uỷ viên BCH Đảng bộ - Phó Chủ tịch UBMTTQVN - Bí thư Đoàn TNCS Hồ Chí Minh xã',
        'phone' => '0902 222 001',
        'avatar' => 'uploads/avatars/avatar-85a1b9eb41f7-1779554394.jpg',
    ],
];

$specialistPositionOverrides = [
    'HỘI NÔNG DÂN' => [
        ['name' => 'Lê Thị Thùy Dung', 'aliases' => ['Lê Thị Thuỳ Dung']],
    ],
    'ĐOÀN THANH NIÊN XÃ' => [
        ['name' => 'Nguyễn Chí Trung'],
    ],
    'HỘI PHỤ NỮ' => [
        ['name' => 'Nguyễn Thị Hồng Thoa'],
    ],
    'HỘI CỰU CHIẾN BINH' => [
        ['name' => 'Đoàn Thanh Điền'],
    ],
];

$organizations = [];
foreach ($sheetMap as $sheetName => $config) {
    $stmt = $pdo->prepare('SELECT id FROM organizations WHERE slug = ? LIMIT 1');
    $stmt->execute([$config['slug']]);
    $id = (int)$stmt->fetchColumn();
    if ($id === 0) {
        throw new RuntimeException("Missing organization slug: {$config['slug']}");
    }
    $organizations[$sheetName] = ['id' => $id] + $config;
}

$reader = new XlsxReader($xlsxPath);
$sheets = $reader->sheets();

$pdo->beginTransaction();
try {
    $organizationIds = array_column($organizations, 'id');
    $placeholders = implode(',', array_fill(0, count($organizationIds), '?'));

    $loanIdsStmt = $pdo->prepare("SELECT id FROM loan_groups WHERE organization_id IN ({$placeholders})");
    $loanIdsStmt->execute($organizationIds);
    $loanIds = array_map('intval', $loanIdsStmt->fetchAll(PDO::FETCH_COLUMN));
    if ($loanIds) {
        $loanPlaceholders = implode(',', array_fill(0, count($loanIds), '?'));
        $pdo->prepare("DELETE FROM loan_group_members WHERE loan_group_id IN ({$loanPlaceholders})")->execute($loanIds);
    }
    $pdo->prepare("DELETE FROM loan_groups WHERE organization_id IN ({$placeholders})")->execute($organizationIds);
    $pdo->prepare("DELETE FROM hamlet_members WHERE organization_id IN ({$placeholders})")->execute($organizationIds);
    $pdo->prepare("DELETE FROM organization_chapters WHERE organization_id IN ({$placeholders})")->execute($organizationIds);
    $pdo->prepare("DELETE FROM organization_leaders WHERE organization_id IN ({$placeholders})")->execute($organizationIds);

    $leaderInsert = $pdo->prepare(
        'INSERT INTO organization_leaders (organization_id, full_name, position, phone, email, avatar, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $chapterInsert = $pdo->prepare(
        'INSERT INTO organization_chapters (organization_id, name, household_count, member_count, male_count, female_count, note, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $hamletMemberInsert = $pdo->prepare(
        'INSERT INTO hamlet_members (organization_id, hamlet_name, full_name, birth_date, role, phone, note, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $loanGroupInsert = $pdo->prepare(
        'INSERT INTO loan_groups (organization_id, hamlet_name, name, leader_name, leader_phone, customer_count, fund_source, outstanding_amount, savings_amount, overdue_amount, rating, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $leaderOverrideFind = $pdo->prepare(
        'SELECT id FROM organization_leaders WHERE organization_id = ? AND full_name IN (?, ?) ORDER BY sort_order ASC, id ASC LIMIT 1'
    );
    $leaderOverrideShift = $pdo->prepare(
        'UPDATE organization_leaders SET sort_order = sort_order + 1 WHERE organization_id = ? AND sort_order >= 1'
    );
    $leaderOverrideUpdate = $pdo->prepare(
        'UPDATE organization_leaders SET full_name = ?, position = ?, phone = ?, avatar = ?, sort_order = 1 WHERE id = ?'
    );
    $leaderPositionOverrideUpdate = $pdo->prepare(
        'UPDATE organization_leaders SET position = ? WHERE id = ?'
    );

    $counts = [];
    foreach ($organizations as $sheetName => $organization) {
        $rows = $sheets[$sheetName] ?? null;
        if (!$rows) {
            throw new RuntimeException("Missing sheet: {$sheetName}");
        }

        $counts[$sheetName] = ['leaders' => 0, 'chapters' => 0, 'hamletMembers' => 0, 'loanGroups' => 0];
        $seenLeaders = [];
        foreach (sections($rows) as $section) {
            $title = mb_strtoupper($section['title'], 'UTF-8');
            $dataRows = array_values(array_filter($section['rows'], 'is_data_row'));

            if (str_contains($title, 'DANH SÁCH BAN CHẤP HÀNH')) {
                $sortOrder = 1;
                foreach ($dataRows as $row) {
                    $fullName = $row[1] ?? '';
                    if ($fullName === '') {
                        continue;
                    }
                    $position = $row[3] ?? '';
                    $unit = $row[4] ?? '';
                    $key = mb_strtolower($fullName . '|' . $position, 'UTF-8');
                    if (!isset($seenLeaders[$key])) {
                        $leaderInsert->execute([$organization['id'], $fullName, $position, '', '', '', $sortOrder]);
                        $seenLeaders[$key] = true;
                        $counts[$sheetName]['leaders']++;
                    }
                    if ($unit !== '' && !str_contains(mb_strtolower($unit, 'UTF-8'), 'xã tân hòa')) {
                        $noteParts = [];
                        if (($row[2] ?? '') !== '') {
                            $noteParts[] = 'Năm sinh/ngày sinh: ' . $row[2];
                        }
                        $hamletMemberInsert->execute([
                            $organization['id'],
                            $unit,
                            $fullName,
                            null,
                            $position !== '' ? $position : 'Thành viên',
                            '',
                            implode('; ', $noteParts),
                            $sortOrder,
                        ]);
                        $counts[$sheetName]['hamletMembers']++;
                    }
                    $sortOrder++;
                }
            } elseif (str_contains($title, 'QUẢN LÝ TỔ CHỨC') || str_contains($title, 'QUẢN LÝ TÔ CHỨC')) {
                $sortOrder = 1;
                foreach ($dataRows as $row) {
                    $name = $row[1] ?? '';
                    if ($name === '') {
                        continue;
                    }
                    $households = (int)number_value($row[2] ?? '');
                    if ($sheetName === 'HỘI PHỤ NỮ') {
                        $male = 0;
                        $female = 0;
                        $memberCount = (int)number_value($row[3] ?? '');
                        $note = $row[4] ?? '';
                    } else {
                        $male = (int)number_value($row[3] ?? '');
                        $female = (int)number_value($row[4] ?? '');
                        $memberCount = $male + $female;
                        $note = $row[5] ?? '';
                    }
                    $chapterInsert->execute([
                        $organization['id'],
                        $name,
                        $households,
                        $memberCount,
                        $male,
                        $female,
                        $note,
                        $sortOrder,
                    ]);
                    $counts[$sheetName]['chapters']++;
                    $sortOrder++;
                }
            } elseif (str_contains($title, 'ỦY THÁC') || str_contains($title, 'NGÂN HÀNG')) {
                foreach ($dataRows as $row) {
                    $leaderName = $row[1] ?? '';
                    $hamletName = $row[2] ?? '';
                    if ($leaderName === '' || $hamletName === '') {
                        continue;
                    }
                    $customers = (int)number_value($row[3] ?? '');
                    $outstandingAmount = number_value($row[4] ?? '');
                    $savingsAmount = number_value($row[5] ?? '');
                    $overdueAmount = number_value($row[6] ?? '');
                    $rating = $row[7] ?? '';
                    $note = $rating !== '' ? 'Xếp loại: ' . $rating : '';
                    $loanGroupInsert->execute([
                        $organization['id'],
                        $hamletName,
                        'Tổ vay vốn ' . $organization['loanName'] . ' - ' . $leaderName,
                        $leaderName,
                        '',
                        $customers,
                        'Ngân hàng Chính sách xã hội',
                        $outstandingAmount,
                        $savingsAmount,
                        $overdueAmount,
                        $rating,
                        $note,
                    ]);
                    $counts[$sheetName]['loanGroups']++;
                }
            }
        }

        if (!empty($primaryLeaderOverrides[$sheetName])) {
            $leader = $primaryLeaderOverrides[$sheetName];
            $alias = $leader['aliases'][0] ?? $leader['name'];
            $leaderOverrideFind->execute([$organization['id'], $leader['name'], $alias]);
            $leaderId = $leaderOverrideFind->fetchColumn();
            if ($leaderId) {
                $leaderOverrideUpdate->execute([
                    $leader['name'],
                    $leader['position'],
                    $leader['phone'],
                    $leader['avatar'],
                    $leaderId,
                ]);
            } else {
                $leaderOverrideShift->execute([$organization['id']]);
                $leaderInsert->execute([
                    $organization['id'],
                    $leader['name'],
                    $leader['position'],
                    $leader['phone'],
                    '',
                    $leader['avatar'],
                    1,
                ]);
                $counts[$sheetName]['leaders']++;
            }
        }

        foreach (($specialistPositionOverrides[$sheetName] ?? []) as $specialist) {
            $alias = $specialist['aliases'][0] ?? $specialist['name'];
            $leaderOverrideFind->execute([$organization['id'], $specialist['name'], $alias]);
            $leaderId = $leaderOverrideFind->fetchColumn();
            if ($leaderId) {
                $leaderPositionOverrideUpdate->execute(['Chuyên viên UB MTTQ VN xã', $leaderId]);
            }
        }
    }

    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}

foreach ($counts as $sheetName => $count) {
    echo $sheetName
        . ': leaders=' . $count['leaders']
        . ', chapters=' . $count['chapters']
        . ', hamlet_members=' . $count['hamletMembers']
        . ', loan_groups=' . $count['loanGroups']
        . PHP_EOL;
}
