<?php
if (!extension_loaded('mbstring')) {
    die('The mbstring PHP extension is required. Please contact your hosting provider to enable it in your PHP configuration.');
}
ob_start();
require_once '../../vendor/autoload.php';
include("../../configs.php");
include("../../functions/min_sub_functions.php");
include("../../functions/utilities_functions.php");

$conn = openConn();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

$spreadsheet = new Spreadsheet();

// Get all min sub items
$items = selectAllMinSub($conn);

$types = [
    ['name' => 'GRN',      'label' => 'Goods Received Note'],
    ['name' => 'GDN',      'label' => 'Goods Delivery Note'],
    ['name' => 'Transfer', 'label' => 'Transfer'],
];

foreach($types as $index => $typeData){

    if($index == 0){
        $sheet = $spreadsheet->getActiveSheet();
    } else {
        $sheet = $spreadsheet->createSheet();
    }

    $sheet->setTitle($typeData['name']);

    // --- Headers ---
    $columns = ['S/N', 'Item Name', 'Quantity', 'Date'];
    $colLetter = 'A';
    foreach($columns as $header){
        $sheet->setCellValue($colLetter . '1', $header);
        $sheet->getStyle($colLetter . '1')
              ->getFill()
              ->setFillType(Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FF162171');
        $sheet->getStyle($colLetter . '1')
              ->getFont()
              ->setBold(true)
              ->setColor(new Color(Color::COLOR_WHITE));

        // Column widths
        if($colLetter == 'A'){
            $sheet->getColumnDimension($colLetter)->setWidth(8);
        } elseif($colLetter == 'B'){
            $sheet->getColumnDimension($colLetter)->setWidth(45);
        } elseif($colLetter == 'C'){
            $sheet->getColumnDimension($colLetter)->setWidth(20);
        } elseif($colLetter == 'D'){
            $sheet->getColumnDimension($colLetter)->setWidth(20);
        }

        $colLetter++;
    }

    // --- Rows: one per item ---
    $row = 2;
    if(is_array($items) && count($items) > 0){
        foreach($items as $item){

            // Get base unit name
            $baseUnitData = selectUnitById($conn, (int)$item['base_unit']);
            $baseUnitName = $baseUnitData['name'] ?? '-';

            // Item name with base unit in brackets e.g. "Cement 50kg (KG)"
            $itemLabel = $item['name'] . ' (' . $baseUnitName . ')';

            $sheet->setCellValue('A' . $row, $row - 1);
            $sheet->setCellValue('B' . $row, $itemLabel);
            $sheet->setCellValue('C' . $row, '');  // quantity — user fills
            $sheet->setCellValue('D' . $row, '');  // date — user fills

            // Light row striping
            if($row % 2 == 0){
                $sheet->getStyle('A' . $row . ':D' . $row)
                      ->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFE8F4FD');
            }

            $row++;
        }
    }

    // --- Date format hint in header comment ---
    $sheet->getComment('D1')->getText()->createTextRun('Format: YYYY-MM-DD e.g. 2025-04-01');
}

// --- Output ---
$filename = 'stock_upload_template.xlsx';
$tempFile = sys_get_temp_dir() . '/' . uniqid() . '.xlsx';
$writer   = new Xlsx($spreadsheet);
$writer->save($tempFile);
ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Content-Length: ' . filesize($tempFile));
header('Cache-Control: max-age=0');
header('Pragma: public');
readfile($tempFile);
unlink($tempFile);
exit;
?>