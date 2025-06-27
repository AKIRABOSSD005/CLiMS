<?php
session_start(); // Start the session first

require_once 'class.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$class = new DataImport();

if(isset($_FILES['data_upload']) && $_FILES['data_upload']['error'] == 0){
    $tmp_name = $_FILES['data_upload']['tmp_name'];
    $name = basename($_FILES['data_upload']['name']);

    // Directly use the tmp file without moving it
    $uploadedFilePath = $tmp_name;

    $fileType = IOFactory::identify($uploadedFilePath);
    $reader = IOFactory::createReader($fileType);

    $spreadsheet = $reader->load($uploadedFilePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    $header = true;

    // Initialize a notifications array
    $all_notifications = [];

    foreach ($rows as $row) {
        if ($header) {
            $header = false;
            continue;
        }

        // Insert data and get notifications
        $notifications = $class->insert_data($row);
        if (!is_array($notifications)) {
            $notifications = [];
        }

        // Merge the notifications with the main array
        $all_notifications = array_merge($all_notifications, $notifications);
    }

    // Store notifications in session
    $_SESSION['notifications'] = $all_notifications;

    // Update and archive zero balance records
    $class->archive_zero_balance();

    // Redirect to loan_data.php after processing
    header("Location: ../page/loan_data.php");
    exit; // Ensure no further code is executed
} else {
    echo "Error uploading files: Error number " . $_FILES['data_upload']['error'];
}
?>
