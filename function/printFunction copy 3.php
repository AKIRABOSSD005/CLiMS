<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="test_file.xlsx"');
header('Cache-Control: max-age=0');

// Create a new Spreadsheet and write data
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Fill in some data to test
$sheet->setCellValue('A1', 'Hello World');

// Output the file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
