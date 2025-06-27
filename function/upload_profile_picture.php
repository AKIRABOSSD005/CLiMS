<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../conn/dbcon.php';
require 'encryption.php';

function log_activity($member_id, $activity_type, $conn) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $user_agent = $_SERVER['HTTP_USER_AGENT']; // Get the user agent

    // Sanitize inputs to prevent SQL injection
    $member_id = mysqli_real_escape_string($conn, $member_id);
    $activity_type = mysqli_real_escape_string($conn, $activity_type);
    $ip_address = mysqli_real_escape_string($conn, $ip_address);
    $user_agent = mysqli_real_escape_string($conn, $user_agent);

    // Insert the activity log into the database
    $query = "INSERT INTO activity_log (member_id, activity_type, ip_address, user_agent)
              VALUES ('$member_id', '$activity_type', '$ip_address', '$user_agent')";
    mysqli_query($conn, $query);
}

if (isset($_POST['uploadPicture'])) {
    // Get the encrypted member ID from the POST request
    $encrypted_member_id = $_POST['member_id'];

    // Decrypt the member ID
    $member_id = decryptData($encrypted_member_id, ENCRYPTION_KEY);
    if ($member_id === false) {
        echo "<script>alert('Invalid member ID. Decryption failed.');</script>";
        exit;
    }

    // File upload path
    $targetDir = "../assets/pictures/memberPictures/"; // Directory where uploaded files will be saved
    $fileName = basename($_FILES["profilePicture"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Create the directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Allow certain file formats
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    if (in_array($fileType, $allowTypes)) {
        // Upload file to server
        if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $targetFilePath)) {
            // Sanitize file name before inserting into the database
            $fileName = mysqli_real_escape_string($conn, $fileName);

            // Update member's profile picture path in the database
            $query = "UPDATE member SET pictures = '$fileName' WHERE member_id = '$member_id'";
            $result = mysqli_query($conn, $query);
            if ($result) {

                log_activity($member_id, "Profile Picture Updated for", $conn);
                // Encrypt the member ID for the redirect
                $encrypted_member_id = encryptData($member_id, ENCRYPTION_KEY);
                echo "<script>alert('Profile picture uploaded successfully.');</script>";
                // Redirect back to user dashboard
                echo "<script>setTimeout(function() { window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; }, 100);</script>";
                exit;
            } else {
                // Error updating profile picture path in the database
                echo "<script>alert('Error updating profile picture path: " . mysqli_error($conn) . "');</script>";
                // Encrypt the member ID for the redirect
                $encrypted_member_id = encryptData($member_id, ENCRYPTION_KEY);
                // Redirect back to user dashboard
                echo "<script>setTimeout(function() { window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; }, 100);</script>";
                exit;
            }
        } else {
            // Error uploading file
            echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
            // Encrypt the member ID for the redirect
            $encrypted_member_id = encryptData($member_id, ENCRYPTION_KEY);
            // Redirect back to user dashboard
            echo "<script>setTimeout(function() { window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; }, 100);</script>";
            exit;
        }
    } else {
        // Invalid file type
        echo "<script>alert('Sorry, only JPG, JPEG, PNG, and GIF files are allowed.');</script>";
        // Encrypt the member ID for the redirect
        $encrypted_member_id = encryptData($member_id, ENCRYPTION_KEY);
        // Redirect back to user dashboard
        echo "<script>setTimeout(function() { window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; }, 100);</script>";
    }
}

// Display profile pictures in HTML
$query = "SELECT * FROM member";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    if (!empty($row['pictures'])) {
        $picturePath = "../assets/pictures/memberPictures/" . $row['pictures'];
        echo "<img src='$picturePath' alt='Profile Picture'>";
    }
}


?>
