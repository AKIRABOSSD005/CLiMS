<?php
require_once 'class.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$class = new DataImport();

if(isset($_FILES['data_upload']) && $_FILES['data_upload']['error'] == 0){
    $tmp_name = $_FILES['data_upload']['tmp_name'];
    $name = basename($_FILES['data_upload']['name']);
    move_uploaded_file($tmp_name, '../uploads/' . $name);

    $uploadedFilePath = '../uploads/' . $name;

    $fileType = IOFactory::identify($uploadedFilePath);
    $reader = IOFactory::createReader($fileType);

    $spreadsheet = $reader->load($uploadedFilePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    $header = true; 
    foreach ($rows as $row) {
        if ($header) {
            $header = false;
            continue; 
        }
        $class->insert_data($row);
    }

    // Update and archive zero balance records
    $class->archive_zero_balance();

    // Redirect to loan_data.php after processing
    header("Location: ../page/loan_data.php");
    exit;
} else {
    echo "Error uploading files: Error number " . $_FILES['data_upload']['error'];
}
?>
