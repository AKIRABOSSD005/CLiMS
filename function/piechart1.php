<?php
require_once '../conn/dbcon.php'; // Include your database connection file

// Define the balance ranges
$balanceRanges = array(
    "0-20k" => array("min" => 0, "max" => 20000),
    "21k-40k" => array("min" => 21000, "max" => 40000),
    "41k-60k" => array("min" => 41000, "max" => 60000),
    "61k-80k" => array("min" => 61000, "max" => 80000),
    "81k-100k" => array("min" => 81000, "max" => 100000)
);

// Initialize an array to hold the counts for each range
$balanceCounts = array_fill_keys(array_keys($balanceRanges), 0);

// SQL query to select the updated_balance column from the loan table
$query = "SELECT updated_balance FROM loan";

// Execute the query
$result = mysqli_query($conn, $query);

// Check if the query was successful
if ($result) {
    // Fetch associative array of the result
    while ($row = mysqli_fetch_assoc($result)) {
        // Check if the updated_balance column exists before accessing it
        if (isset($row['updated_balance'])) {
            $balance = $row['updated_balance'];
            // Determine which range the balance falls into and increment the corresponding count
            foreach ($balanceRanges as $range => $limits) {
                if ($balance >= $limits['min'] && $balance <= $limits['max']) {
                    $balanceCounts[$range]++;
                    break;
                }
            }
        }
    }

    // Free the result set
    mysqli_free_result($result);
} else {
    // Handle query error
    echo "Error: " . mysqli_error($conn);
}



// Prepare data points based on the counts for each range
$dataPoints = array();
foreach ($balanceCounts as $range => $count) {
    $dataPoints[] = array("y" => $count, "name" => $range, "exploded" => true);
}

// Now $dataPoints array contains the dynamically generated data points based on the balance ranges
?>
