<?php
require '../conn/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $reportId = intval($input['id']);
    $newStatus = intval($input['status']);

    // Update the report status in the database
    $query = "UPDATE report SET report_status = $newStatus WHERE report_id = $reportId";
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
}
?>
