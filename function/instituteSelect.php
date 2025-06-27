<?php
// Include your database connection script
require '../conn/dbcon.php';

// SQL query to fetch institute names
$sql = "SELECT * FROM institute";
$result = $conn->query($sql);

// Initialize an empty array to store institute names
$instituteNames = [];

// Check if there are any results
if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        // Store institute name in the array
        $instituteNames[$row["institute_id"]] = $row["institute_name"];
    }
} else {
    // Handle case where no institute are found
    $instituteNames[] = "No institutes found";
}

// Close connection
$conn->close();
?>
