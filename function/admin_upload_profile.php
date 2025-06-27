<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require("../conn/dbcon.php");

function log_activity($member_id, $activity_type, $conn) {
    $ip_address = $_SERVER['REMOTE_ADDR']; 
    $user_agent = $_SERVER['HTTP_USER_AGENT']; 

    $member_id = mysqli_real_escape_string($conn, $member_id);
    $activity_type = mysqli_real_escape_string($conn, $activity_type);
    $ip_address = mysqli_real_escape_string($conn, $ip_address);
    $user_agent = mysqli_real_escape_string($conn, $user_agent);

    $query = "INSERT INTO activity_log (member_id, activity_type, ip_address, user_agent)
              VALUES ('$member_id', '$activity_type', '$ip_address', '$user_agent')";
    mysqli_query($conn, $query);
}

if (isset($_POST['uploadAdminProfile'])) {
    $member_id = $_SESSION['member_id'];

    $targetDir = "../assets/pictures/memberPictures/";
    $fileName = basename($_FILES["profilePicture"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    if (in_array($fileType, $allowTypes)) {
        if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $targetFilePath)) {
            $query = "UPDATE member SET pictures = '$fileName' WHERE member_id = '$member_id'";
            $result_query = mysqli_query($conn, $query);
            if ($result_query) {

                log_activity($member_id, "Profile Picture Updated", $conn);
                echo "<script>alert('Profile picture uploaded successfully.');</script>";
            } else {
                $error_message = "Error updating profile picture path: " . mysqli_error($conn);
                echo "<script>alert('$error_message');</script>";
            }
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
        }
    } else {
        echo "<script>alert('Sorry, only JPG, JPEG, PNG, and GIF files are allowed.');</script>";
    }
    echo "<script>setTimeout(function() { window.location.href = '../page/admin_profile.php?id=$member_id'; }, 100);</script>";
    exit; 
}
?>