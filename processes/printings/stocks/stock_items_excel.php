<?php
session_start();
if (!isset($_SESSION['userid'])) {
    echo "<script>alert('Unauthorized'); window.close();</script>";
    exit();
}
if (!extension_loaded('mbstring')) {
    die('mbstring PHP extension is required.');
}
ob_start();

require_once '../../../vendor/autoload.php';
require_once '../../../configs.php';
require_once '../../../functions/min_sub_functions.php';
require_once '../../../functions/utilities_functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$conn  = openConn();
$items = selectAllMinSub($conn);

$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();
$sheet->setTitle('Stock Items');

// ── BRAND COLOURS ──────────────────────────────
$NAVY       = 'FF162171';   // logo navy
$NAVY_DARK  = 'FF0E1650';   // darker navy for accents
$GOLD       = 'FFFFC107';   // logo gold
$GOLD_LIGHT = 'FFFFF3CD';   // light gold stripe
$WHITE      = 'FFFFFFFF';
$LIGHT_NAVY = 'FFE8ECFF';   // even row stripe

// ── COLUMNS: A=#  B=Image  C=Code  D=Name  E=Price ─
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(20);   // image — wide
$sheet->getColumnDimension('C')->setWidth(18);
$sheet->getColumnDimension('D')->setWidth(42);
$sheet->getColumnDimension('E')->setWidth(20);

// ── ROW 1: full navy bar — logo left, company name centred ──
$sheet->getRowDimension(1)->setRowHeight(90);
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1:E1')->applyFromArray([
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $NAVY]],
]);
$sheet->setCellValue('A1', 'MANGOWO TRADING AND SUPPLIES');
$sheet->getStyle('A1')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 20, 'color' => ['argb' => $WHITE], 'name' => 'Arial'],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER],
]);

// Logo image
$logoPath = __DIR__ . '/../../../dist/img/logo.png';
if (file_exists($logoPath)) {
    $logo = new Drawing();
    $logo->setName('Logo');
    $logo->setPath($logoPath);
    $logo->setCoordinates('A1');
    $logo->setHeight(78);
    $logo->setOffsetX(5);
    $logo->setOffsetY(6);
    $logo->setWorksheet($sheet);
}

// ── ROW 2: gold accent bar ──────────────────────
$sheet->getRowDimension(2)->setRowHeight(6);
$sheet->mergeCells('A2:E2');
$sheet->getStyle('A2:E2')->applyFromArray([
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $GOLD]],
]);

// ── ROW 3: address ──────────────────────────────
$sheet->getRowDimension(3)->setRowHeight(16);
$sheet->mergeCells('A3:E3');
$sheet->setCellValue('A3', 'Kariakoo, Narung\'ombe Na Sikukuu | P.O. Box xxx, Dar es Salaam, Tanzania | Tel: +255 785 182 121 / +255 759 266 699 / +255 655 606 676');
$sheet->getStyle('A3')->applyFromArray([
    'font'      => ['size' => 8, 'italic' => true, 'color' => ['argb' => 'FF444444'], 'name' => 'Arial'],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F7FF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);

// ── ROW 4: report title (navy bg, gold text) ────
$sheet->getRowDimension(4)->setRowHeight(26);
$sheet->mergeCells('A4:E4');
$sheet->setCellValue('A4', 'STOCK ITEMS LIST');
$sheet->getStyle('A4')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => $GOLD], 'name' => 'Arial'],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $NAVY_DARK]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);

// ── ROW 5: generated date ───────────────────────
$sheet->getRowDimension(5)->setRowHeight(13);
$sheet->mergeCells('A5:E5');
$sheet->setCellValue('A5', 'Generated: ' . date('d-M-Y   H:i'));
$sheet->getStyle('A5')->applyFromArray([
    'font'      => ['italic' => true, 'size' => 8, 'color' => ['argb' => 'FF888888'], 'name' => 'Arial'],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
]);

// ── ROW 6: spacer ───────────────────────────────
$sheet->getRowDimension(6)->setRowHeight(5);

// ── ROW 7: column headers ───────────────────────
$sheet->getRowDimension(7)->setRowHeight(24);
$headers = ['#', 'Image', 'Code', 'Item Name', 'Price (TZS)'];
$cols    = ['A', 'B', 'C', 'D', 'E'];
foreach ($headers as $i => $h) {
    $cell = $cols[$i] . '7';
    $sheet->setCellValue($cell, $h);
    $sheet->getStyle($cell)->applyFromArray([
        'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => $WHITE], 'name' => 'Arial'],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $NAVY]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                         'color'       => ['argb' => $GOLD]]],
    ]);
}

// ── DATA ROWS from row 8 ────────────────────────
$dataRow = 8;
$num     = 0;

if (is_array($items) && count($items) > 0) {
    foreach ($items as $item) {
        $num++;
        // Alternate: white vs light-navy
        $bg = ($num % 2 === 0) ? $LIGHT_NAVY : $WHITE;

        $sheet->getRowDimension($dataRow)->setRowHeight(70);   // tall for big image

        $code  = !empty($item['code']) ? $item['code'] : '—';
        $price = (isset($item['price']) && $item['price'] !== '')
                 ? number_format((float)$item['price'], 2) : '—';

        $sheet->setCellValue('A' . $dataRow, $num);
        $sheet->setCellValue('C' . $dataRow, $code);
        $sheet->setCellValue('D' . $dataRow, $item['name']);
        $sheet->setCellValue('E' . $dataRow, $price);

        // Base row style
        $sheet->getStyle('A' . $dataRow . ':E' . $dataRow)->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
            'font'      => ['size' => 11, 'name' => 'Arial', 'color' => ['argb' => 'FF111111']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                             'color'       => ['argb' => 'FFCCCCCC']]],
        ]);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $dataRow)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => $NAVY]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('E' . $dataRow)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FF0a6e0a']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
        $sheet->getStyle('D' . $dataRow)->applyFromArray([
            'font'      => ['size' => 11, 'bold' => true, 'color' => ['argb' => 'FF1a1a2e']],
            'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Gold left border accent on row
        $sheet->getStyle('A' . $dataRow)->getBorders()->getLeft()
              ->setBorderStyle(Border::BORDER_MEDIUM)
              ->getColor()->setARGB($GOLD);

        // Product image — big & attractive
        $imgFile = __DIR__ . '/../../../uploads/items/' . ($item['image_url'] ?? '');
        if (!empty($item['image_url']) && file_exists($imgFile)) {
            $prod = new Drawing();
            $prod->setPath($imgFile);
            $prod->setCoordinates('B' . $dataRow);
            $prod->setHeight(62);          // big image
            $prod->setOffsetX(6);
            $prod->setOffsetY(4);
            $prod->setWorksheet($sheet);
            $sheet->setCellValue('B' . $dataRow, '');
        } else {
            $sheet->setCellValue('B' . $dataRow, 'No Image');
            $sheet->getStyle('B' . $dataRow)->applyFromArray([
                'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FFAAAAAA']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        $dataRow++;
    }
}

// ── TOTAL ROW ───────────────────────────────────
$sheet->getRowDimension($dataRow)->setRowHeight(22);
$sheet->mergeCells('A' . $dataRow . ':E' . $dataRow);
$sheet->setCellValue('A' . $dataRow, 'TOTAL ITEMS: ' . $num);
$sheet->getStyle('A' . $dataRow)->applyFromArray([
    'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => $NAVY], 'name' => 'Arial'],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $GOLD]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);

// ── GOLD DIVIDER ────────────────────────────────
$divRow = $dataRow + 1;
$sheet->getRowDimension($divRow)->setRowHeight(5);
$sheet->mergeCells('A' . $divRow . ':E' . $divRow);
$sheet->getStyle('A' . $divRow . ':E' . $divRow)->applyFromArray([
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $GOLD]],
]);

// ── FOOTER ──────────────────────────────────────
$footerRow = $divRow + 1;
$sheet->getRowDimension($footerRow)->setRowHeight(22);
$sheet->mergeCells('A' . $footerRow . ':E' . $footerRow);
$sheet->setCellValue('A' . $footerRow, 'MANGOWO TRADING AND SUPPLIES  |  Dar es Salaam, Tanzania  |  Tel: +255 785 182 121 / +255 759 266 699 / +255 655 606 676');
$sheet->getStyle('A' . $footerRow)->applyFromArray([
    'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => $WHITE], 'name' => 'Arial'],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $NAVY]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);

// ── PRINT SETTINGS ──────────────────────────────
$sheet->getPageSetup()
      ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
      ->setFitToWidth(1)
      ->setFitToHeight(0);
$sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.4)->setRight(0.4);

// ── OUTPUT ──────────────────────────────────────
$filename = 'stock_items_' . date('Ymd_His') . '.xlsx';
$tempFile = sys_get_temp_dir() . '/' . uniqid() . '.xlsx';
(new Xlsx($spreadsheet))->save($tempFile);
ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Content-Length: ' . filesize($tempFile));
header('Cache-Control: max-age=0');
header('Pragma: public');
readfile($tempFile);
unlink($tempFile);
exit;