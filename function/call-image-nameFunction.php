<?php
// Assuming you have a database connection established
require("../conn/dbcon.php");

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query_member_info = "SELECT fname, mname, lname, pictures FROM member WHERE member_id = '$id'";
    $result_member_info = mysqli_query($conn, $query_member_info);

    // Check if query execution was successful and if data is fetched
    if ($result_member_info && mysqli_num_rows($result_member_info) > 0) {
        // Fetch the data and populate $member_info
        $member_info = mysqli_fetch_assoc($result_member_info);
    } else {
        // No data found, handle this scenario accordingly
        // For example, you can redirect the user to an error page or display a message
        echo "No member found with the provided ID.";
        exit; // Terminate script execution
    }
} else {
    // ID parameter is not set, handle this scenario accordingly
    // For example, you can redirect the user to an error page or display a message
    echo "Member ID is not provided.";
    exit; // Terminate script execution
}
?>
