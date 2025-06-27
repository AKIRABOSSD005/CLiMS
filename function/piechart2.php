<?php
require_once '../conn/dbcon.php'; // Include your database connection file

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// SQL query to count Male and Female members
$query = "SELECT gender, COUNT(*) AS count FROM member WHERE role_id='2' GROUP BY gender";
// Execute the query
$result = mysqli_query($conn, $query);

// Initialize an array to hold the gender counts with default values
$genderCounts = ['Male' => 0, 'Female' => 0];

// Check if the query was successful
if ($result) {
    // Fetch associative array of the result
    while ($row = mysqli_fetch_assoc($result)) {
        $gender = $row['gender'];
        $genderCounts[$gender] = $row['count'];  // Update the count for each gender
    }
} else {
    // Output the error in JSON format and exit the script
    echo json_encode(["error" => mysqli_error($conn)]);
    exit;
}

// Check if the script is being called directly
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/function/piechart2.php') {
    // Output the gender counts as JSON
    echo json_encode($genderCounts);
}
?>