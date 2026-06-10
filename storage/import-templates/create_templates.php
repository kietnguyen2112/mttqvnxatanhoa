<?php

function columnName(int $index): string
{
    $name = '';
    while ($index > 0) {
        $mod = ($index - 1) % 26;
        $name = chr(65 + $mod) . $name;
        $index = intdiv($index - $mod, 26);
    }
    return $name;
}

function xml(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function colXml(array $widths): string
{
    $xml = '<cols>';
    foreach ($widths as $index => $width) {
        $column = $index + 1;
        $xml .= '<col min="' . $column . '" max="' . $column . '" width="' . $width . '" customWidth="1"/>';
    }
    return $xml . '</cols>';
}

function cellXml(string $cell, mixed $value, int $style = 0): string
{
    $styleAttr = $style > 0 ? ' s="' . $style . '"' : '';

    if (is_int($value) || is_float($value)) {
        return '<c r="' . $cell . '"' . $styleAttr . '><v>' . $value . '</v></c>';
    }

    return '<c r="' . $cell . '"' . $styleAttr . ' t="inlineStr"><is><t>' . xml((string)$value) . '</t></is></c>';
}

function rowXml(int $rowNumber, array $row, int $defaultStyle = 0, array $styles = [], float $height = 0): string
{
    $heightAttr = $height > 0 ? ' ht="' . $height . '" customHeight="1"' : '';
    $xml = '<row r="' . $rowNumber . '"' . $heightAttr . '>';
    foreach ($row as $columnIndex => $value) {
        $cell = columnName($columnIndex + 1) . $rowNumber;
        $xml .= cellXml($cell, $value, $styles[$columnIndex] ?? $defaultStyle);
    }
    return $xml . '</row>';
}

function sheetXml(array $config): string
{
    $rows = $config['rows'];
    $widths = $config['widths'];
    $lastColumn = columnName(count($rows[0]));
    $lastRow = count($rows);
    $numericColumns = $config['numericColumns'] ?? [];
    $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<dimension ref="A1:' . $lastColumn . $lastRow . '"/>'
        . '<sheetViews><sheetView showGridLines="0" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
        . colXml($widths)
        . '<sheetData>';

    foreach ($rows as $rowIndex => $row) {
        $rowNumber = $rowIndex + 1;
        $styles = [];
        if ($rowIndex === 0) {
            $style = 1;
            $height = 30;
        } else {
            $style = $rowIndex % 2 === 0 ? 3 : 2;
            $height = 24;
            foreach ($numericColumns as $columnIndex => $_numberStyle) {
                $styles[$columnIndex] = $style === 3 ? 7 : 6;
            }
        }
        $sheet .= rowXml($rowNumber, $row, $style, $styles, $height);
    }

    $sheet .= '</sheetData>'
        . '<autoFilter ref="A1:' . $lastColumn . $lastRow . '"/>'
        . '<pageMargins left="0.4" right="0.4" top="0.7" bottom="0.7" header="0.3" footer="0.3"/>'
        . '</worksheet>';

    return $sheet;
}

function guideSheetXml(string $title, array $headers, array $required, array $notes): string
{
    $rows = [
        ['Mẫu nhập dữ liệu', $title],
        ['Sheet cần nhập', 'Du lieu'],
        ['Lưu ý', 'Giữ nguyên dòng tiêu đề ở sheet Du lieu. Có thể xóa các dòng ví dụ trước khi nhập dữ liệu thật.'],
        ['Cột bắt buộc', implode(', ', $required)],
        [],
        ['Tên cột', 'Bắt buộc', 'Ghi chú'],
    ];

    foreach ($headers as $header) {
        $rows[] = [$header, in_array($header, $required, true) ? 'Có' : 'Không', $notes[$header] ?? ''];
    }

    $lastRow = count($rows);
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<dimension ref="A1:C' . $lastRow . '"/>'
        . '<sheetViews><sheetView showGridLines="0" workbookViewId="0"/></sheetViews>'
        . colXml([24, 22, 70])
        . '<sheetData>';

    foreach ($rows as $index => $row) {
        $rowNumber = $index + 1;
        if ($row === []) {
            $xml .= '<row r="' . $rowNumber . '" ht="8" customHeight="1"/>';
            continue;
        }
        $style = match (true) {
            $rowNumber === 1 => 5,
            $rowNumber === 6 => 1,
            $rowNumber < 6 => 4,
            default => $rowNumber % 2 === 0 ? 3 : 2,
        };
        $xml .= rowXml($rowNumber, $row, $style, [], $rowNumber === 1 ? 32 : 24);
    }

    return $xml . '</sheetData><mergeCells count="1"><mergeCell ref="B1:C1"/></mergeCells></worksheet>';
}

function workbookXml(array $sheets): string
{
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets>';
    foreach ($sheets as $index => $sheet) {
        $id = $index + 1;
        $xml .= '<sheet name="' . xml($sheet['name']) . '" sheetId="' . $id . '" r:id="rId' . $id . '"/>';
    }
    return $xml . '</sheets></workbook>';
}

function workbookRelsXml(array $sheets): string
{
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    foreach ($sheets as $index => $_sheet) {
        $id = $index + 1;
        $xml .= '<Relationship Id="rId' . $id . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $id . '.xml"/>';
    }
    $xml .= '<Relationship Id="rId' . (count($sheets) + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
    return $xml . '</Relationships>';
}

function contentTypesXml(array $sheets): string
{
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
    foreach ($sheets as $index => $_sheet) {
        $xml .= '<Override PartName="/xl/worksheets/sheet' . ($index + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
    }
    return $xml . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';
}

function stylesXml(): string
{
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <numFmts count="2">
    <numFmt numFmtId="164" formatCode="#,##0"/>
    <numFmt numFmtId="165" formatCode="@"/>
  </numFmts>
  <fonts count="4">
    <font><sz val="11"/><name val="Arial"/><color rgb="FF1F2937"/></font>
    <font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Arial"/></font>
    <font><color rgb="FF1F2937"/><sz val="11"/><name val="Arial"/></font>
    <font><b/><color rgb="FF7F1D1D"/><sz val="14"/><name val="Arial"/></font>
  </fonts>
  <fills count="5">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFB91C1C"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="3">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border><left style="thin"><color rgb="FFE5E7EB"/></left><right style="thin"><color rgb="FFE5E7EB"/></right><top style="thin"><color rgb="FFE5E7EB"/></top><bottom style="thin"><color rgb="FFE5E7EB"/></bottom><diagonal/></border>
    <border><bottom style="medium"><color rgb="FFD69E2E"/></bottom></border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="8">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="2" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="3" fillId="0" borderId="2" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>
    <xf numFmtId="164" fontId="2" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>
    <xf numFmtId="164" fontId="2" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>
  </cellXfs>
</styleSheet>';
}

function createXlsx(string $path, array $config): void
{
    $sheets = [
        ['name' => 'Du lieu', 'xml' => sheetXml($config)],
        ['name' => 'Huong dan', 'xml' => guideSheetXml($config['title'], $config['rows'][0], $config['requiredHeaders'], $config['notes'])],
    ];

    $zip = new ZipArchive();
    if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('Cannot create ' . $path);
    }

    $zip->addFromString('[Content_Types].xml', contentTypesXml($sheets));
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    $zip->addFromString('xl/workbook.xml', workbookXml($sheets));
    $zip->addFromString('xl/_rels/workbook.xml.rels', workbookRelsXml($sheets));
    $zip->addFromString('xl/styles.xml', stylesXml());
    foreach ($sheets as $index => $sheet) {
        $zip->addFromString('xl/worksheets/sheet' . ($index + 1) . '.xml', $sheet['xml']);
    }
    $zip->close();
}

function templateDefinitions(): array
{
    return [
    'mau_lanh_dao.xlsx' => [
        'title' => 'Danh sách cán bộ',
        'requiredHeaders' => ['Tổ chức', 'Họ và tên', 'Chức vụ'],
        'notes' => [
            'Tổ chức' => 'Nhập ID, tên đầy đủ, tên viết tắt hoặc slug của hội/tổ chức.',
            'Thứ tự' => 'Số nhỏ hiển thị trước.',
        ],
        'widths' => [24, 28, 34, 18, 30, 12],
        'numericColumns' => [5 => 6],
        'rows' => [
            ['Tổ chức', 'Họ và tên', 'Chức vụ', 'Điện thoại', 'Email', 'Thứ tự'],
            ['MTTQVN', 'Nguyễn Văn A', 'Chủ tịch MTTQVN xã', '0901 000 001', 'nguyenvana@example.com', 1],
            ['Hội Phụ nữ', 'Trần Thị B', 'Phó Chủ tịch Hội Phụ nữ xã', '0901 000 002', '', 2],
        ],
    ],
    'mau_thanh_vien_cap_ap.xlsx' => [
        'title' => 'Danh sách hồ sơ cấp ấp',
        'requiredHeaders' => ['Tổ chức', 'Tên chi đoàn, chi hội / ấp', 'Họ và tên', 'Vai trò'],
        'notes' => [
            'Tổ chức' => 'Nhập hội/tổ chức quản lý thành viên; dùng MTTQVN cho Ban Công tác Mặt trận.',
            'Tên chi đoàn, chi hội / ấp' => 'Ví dụ: Ấp Một Ngàn, Chi hội phụ nữ Công An.',
            'Ngày sinh' => 'Nhập theo dạng YYYY-MM-DD hoặc để trống.',
            'Thứ tự' => 'Số nhỏ hiển thị trước.',
        ],
        'widths' => [24, 30, 28, 16, 34, 18, 34, 12],
        'numericColumns' => [7 => 6],
        'rows' => [
            ['Tổ chức', 'Tên chi đoàn, chi hội / ấp', 'Họ và tên', 'Ngày sinh', 'Vai trò', 'Điện thoại', 'Ghi chú', 'Thứ tự'],
            ['MTTQVN', 'Ấp Một Ngàn', 'Nguyễn Văn A', '', 'Trưởng ban Công tác Mặt trận ấp', '0901 000 001', '', 1],
            ['Hội Phụ nữ', 'Chi hội phụ nữ Công An', 'Lê Thị C', '', 'Chi hội trưởng', '0902 000 001', 'Mẫu nhập liệu', 2],
        ],
    ],
    'mau_to_vay_von.xlsx' => [
        'title' => 'Danh sách tổ vay vốn',
        'requiredHeaders' => ['Tổ chức', 'Tên ấp', 'Tên tổ vay vốn', 'Tên tổ trưởng'],
        'notes' => [
            'Tổ chức' => 'Nhập hội/tổ chức quản lý tổ vay vốn.',
            'Tên tổ vay vốn' => 'Tên này dùng để liên kết khi nhập thành viên vay vốn.',
            'Số khách hàng' => 'Nhập tổng số khách hàng/thành viên trong tổ nếu chưa nhập danh sách chi tiết.',
            'Nguồn vốn' => 'Ví dụ: Ngân hàng Chính sách xã hội.',
            'Dư nợ' => 'Nhập số dư nợ hiện tại của tổ (VNĐ).',
            'Tiền gửi' => 'Nhập tổng tiền gửi của tổ (VNĐ).',
            'Nợ quá hạn' => 'Nhập tổng nợ quá hạn của tổ (VNĐ), có thể để 0.',
            'Xếp loại tổ' => 'Ví dụ: Tốt, Khá, Trung bình, Yếu.',
        ],
        'widths' => [24, 16, 34, 28, 22, 14, 30, 18, 18, 18, 16, 34],
        'numericColumns' => [5 => 6, 7 => 6, 8 => 6, 9 => 6],
        'rows' => [
            ['Tổ chức', 'Tên ấp', 'Tên tổ vay vốn', 'Tên tổ trưởng', 'Điện thoại tổ trưởng', 'Số khách hàng', 'Nguồn vốn', 'Dư nợ', 'Tiền gửi', 'Nợ quá hạn', 'Xếp loại tổ', 'Ghi chú'],
            ['Hội ND', 'Ấp 1', 'Tổ vay vốn Ấp 1 - Mẫu', 'Nguyễn Văn E', '0903 000 001', 48, 'Ngân hàng Chính sách xã hội', 2600000000, 102000000, 0, 'Tốt', 'Mẫu nhập liệu'],
            ['Hội Phụ nữ', 'Ấp 2', 'Tổ vay vốn Ấp 2 - Mẫu', 'Trần Thị F', '0903 000 002', 42, 'Nguồn vốn quay vòng', 1765000000, 50000000, 30000000, 'Khá', ''],
        ],
    ],
    'mau_thanh_vien_to_vay_von.xlsx' => [
        'title' => 'Danh sách thành viên tổ vay vốn',
        'requiredHeaders' => ['Tổ vay vốn', 'Họ và tên'],
        'notes' => [
            'Tổ vay vốn' => 'Nhập ID hoặc đúng tên tổ vay vốn đã có trong hệ thống.',
            'Số tiền vay' => 'Nhập số, không cần ký hiệu tiền tệ.',
            'Dư nợ' => 'Nhập số dư nợ hiện tại (VNĐ). Nếu để trống sẽ lấy theo Số tiền vay.',
            'Nợ quá hạn' => 'Nhập số tiền nợ quá hạn của thành viên (VNĐ), có thể để 0.',
            'Thứ tự' => 'Số nhỏ hiển thị trước.',
        ],
        'widths' => [34, 28, 18, 18, 18, 18, 18, 28, 34, 12],
        'numericColumns' => [4 => 6, 5 => 6, 6 => 6, 9 => 6],
        'rows' => [
            ['Tổ vay vốn', 'Họ và tên', 'Vai trò', 'Điện thoại', 'Số tiền vay ban đầu', 'Dư nợ', 'Nợ quá hạn', 'Mục đích vay', 'Ghi chú', 'Thứ tự'],
            ['Tổ vay vốn Ấp 1 - Mẫu', 'Võ Văn G', 'Thành viên', '0904 000 001', 30000000, 28500000, 0, 'Chăn nuôi', 'Mẫu nhập liệu', 1],
            ['Tổ vay vốn Ấp 1 - Mẫu', 'Đặng Thị H', 'Thành viên', '0904 000 002', 25000000, 25000000, 150000, 'Mua bán nhỏ', '', 2],
        ],
    ],
    ];
}

$publicDir = dirname(__DIR__, 2) . '/public/assets/templates';
if (!is_dir($publicDir)) {
    mkdir($publicDir, 0775, true);
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $templates = templateDefinitions();
    foreach ($templates as $file => $config) {
        createXlsx(__DIR__ . '/' . $file, $config);
        copy(__DIR__ . '/' . $file, $publicDir . '/' . $file);
        echo $file . PHP_EOL;
    }
}
