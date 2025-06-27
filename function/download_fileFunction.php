<?php
require '../conn/dbcon.php';
require 'encryption.php'; // Ensure the file with encryptData and decryptData is included

// Check if id is set in the URL
if (isset($_GET['id'])) {
    // Get the encrypted member ID from the URL and sanitize
    $encrypted_member_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Decrypt the member ID
    $id = decryptData($encrypted_member_id, ENCRYPTION_KEY);

    // Check if decryption was successful
    if ($id === false) {
        echo "<script>alert('Invalid member ID. Decryption failed.');</script>";
        echo "<script>window.location.href = '../page/user_dashboard.php';</script>";
        exit;
    }

    // Fetch principal amount and updated balance for the member
    $query_loan_info = "SELECT principal_amount, updated_balance FROM loan WHERE member_id = '$id'";
    $result_loan_info = mysqli_query($conn, $query_loan_info);

    // Check if loan data is available
    if ($result_loan_info) {
        // Fetch loan data
        $loan_data = mysqli_fetch_assoc($result_loan_info);

        // Check if the member has an active loan
        if ($loan_data) {
            $principal_amount = $loan_data['principal_amount'];
            $updated_balance = $loan_data['updated_balance'];

            // Calculate the remaining balance (updated balance)
            $remaining_balance = $principal_amount - $updated_balance;

            // Check if the remaining balance is 50% or below of the principal amount or equals 0
            if ($remaining_balance > ($principal_amount * 0.5) && $remaining_balance !== 0) {
                // Fetch files data
                $query_files = "SELECT * FROM download_file";
                $result_files = mysqli_query($conn, $query_files);

                // Display the download buttons for each file
                while ($row_files = mysqli_fetch_assoc($result_files)) {
                    $download_link = "../downloads/" . $row_files['name'];
                    // Trigger the download automatically using PHP header
                    header("Location: $download_link");
                    exit; // Ensure script execution stops here to prevent further output
                }

                // Redirect to user_dashboard.php after executing the script
                // Encrypt the member ID for redirection
                $encrypted_member_id = encryptData($id, ENCRYPTION_KEY);
                echo "<script>window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "';</script>";
                exit; // Ensure script execution stops here to prevent further output
            } else {
                // Display a message if the remaining balance is not sufficient
                echo '<script>alert("Your Balance was not 50% of your principal amount.");</script>';
                // Encrypt the member ID for redirection
                $encrypted_member_id = encryptData($id, ENCRYPTION_KEY);
                echo '<script>window.location.href = "../page/user_dashboard.php?member_id=' . urlencode($encrypted_member_id) . '";</script>';
                exit;
            }
        } else {
            // Display a message if the member does not have an active loan
            echo '<script>alert("You don\'t have an active loan.");</script>';
            // Encrypt the member ID for redirection
            $encrypted_member_id = encryptData($id, ENCRYPTION_KEY);
            echo '<script>window.location.href = "../page/user_dashboard.php?member_id=' . urlencode($encrypted_member_id) . '";</script>';
            exit;
        }
    } else {
        // Handle database error if query fails
        echo '<script>alert("Error fetching loan information: ' . mysqli_error($conn) . '");</script>';
        // Encrypt the member ID for redirection
        $encrypted_member_id = encryptData($id, ENCRYPTION_KEY);
        echo '<script>window.location.href = "../page/user_dashboard.php?member_id=' . urlencode($encrypted_member_id) . '";</script>';
        exit;
    }
} else {
    // If id is not set in the URL
    echo '<script>alert("No Member ID specified.");</script>';
    echo '<script>window.location.href = "../page/user_dashboard.php";</script>';
    exit;
}
?>
