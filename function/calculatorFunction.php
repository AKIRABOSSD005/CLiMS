<?php

require '../conn/dbcon.php';

// Query to fetch values from computation_loan table
$sql = "SELECT * FROM computation_loan";
$result = $conn->query($sql);

// Fetching computation_loan data into an associative array
$computation_loan_data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $computation_loan_data[] = $row;
    }
}

// Output JSON data
header('Content-Type: application/json');
echo json_encode($computation_loan_data);

// Close the connection
$conn->close();
?>
