<?php
    require '../conn/dbcon.php';
    include 'encryption.php';

    // Set timezone to Philippines (Asia/Manila)
    date_default_timezone_set('Asia/Manila');

    if (isset($_POST['sendReport'])) {
        // Get the encrypted member ID from the POST request
        $encrypted_member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
        $loan_id = mysqli_real_escape_string($conn, $_POST['loan_id']);
        $reportType = mysqli_real_escape_string($conn, $_POST['reportType']);
        $subject = mysqli_real_escape_string($conn, $_POST['subject']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        $report_status = mysqli_real_escape_string($conn, $_POST['report_status']);
    
        // Decrypt the member ID
        $member_id = decryptData($encrypted_member_id, ENCRYPTION_KEY);
    
        if ($member_id === false) {
            echo "<script>alert('Invalid member ID. Decryption failed.');</script>";
            exit;
        }
    
        // Create a formatted message
        $formattedMessage = "$reportType: $message";

        // Get the current date and time in the specified format
        $currentDateTime = date('Y-m-d H:i:s'); // Format: YYYY-MM-DD HH:MM:SS
    
        // Insert query with the concatenated message and formatted date/time
        $query = "INSERT INTO report (member_id, loan_id, report_type, subject, message, report_status, time_date) 
                  VALUES ('$member_id', '$loan_id', '$reportType', '$subject', '$formattedMessage', '$report_status', '$currentDateTime')";
    
        // Execute query and handle result
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Report sent successfully!');</script>";
            echo "<script>
                    setTimeout(function() {
                        window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; 
                    }, 100);
                  </script>";
            exit();
        } else {
            echo "<script>alert('Failed to send report: " . mysqli_error($conn) . "');</script>";
            echo "<script>
                    setTimeout(function() {
                        window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; 
                    }, 100);
                  </script>";
            exit();
        }
    }
?>
g