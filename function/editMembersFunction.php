<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../conn/dbcon.php';
include 'encryption.php';

// Activity log function
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

// Update function for admin 
if (isset($_POST['updateButton'])) {

    // Assuming you have sanitized inputs for security
    $member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $mname = mysqli_real_escape_string($conn, $_POST['mname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $membership_date = mysqli_real_escape_string($conn, $_POST['membersDate']);
    $tin_number = mysqli_real_escape_string($conn, $_POST['tinNumber']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contactNumber']);

    // Corrected query for update (removed the extra comma before WHERE)
    $query = "UPDATE member SET 
        fname = '$fname',
        mname = '$mname',
        lname = '$lname',
        membership_date = '$membership_date',
        tin_number = '$tin_number',
        contact_number = '$contact_number'
        WHERE member_id = '$member_id'";

    $result = mysqli_query($conn, $query);
    if ($result) {
        log_activity($member_id, 'Updated member details for', $conn); // Log the activity
        // Display JavaScript alert for the success message
        echo "<script>alert('Member details updated successfully.');</script>";
        // Redirect to viewMembers.php after a delay
        echo "<script>setTimeout(function() { window.location.href = '../page/viewMembers.php?id=" . $member_id . "'; }, 100);</script>";
        exit; // Ensure no further output is sent after the redirect
    } else {
        // Display JavaScript alert for the error message
        echo "<script>alert('Error updating member details: " . mysqli_error($conn) . "');</script>";
        // Redirect to viewMembers.php after a delay
        echo "<script>setTimeout(function() { window.location.href = '../page/viewMembers.php?id=" . $member_id . "'; }, 100);</script>";
        exit; // Ensure no further output is sent after the redirect
    }
}




// Update function for editing using members account
if (isset($_POST['updatepassword'])) {
    // Get data from POST request
    $encrypted_member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
    $old_password = mysqli_real_escape_string($conn, $_POST['oldpassword']);
    $new_password = mysqli_real_escape_string($conn, $_POST['newpassword']);
    $re_enter_new_password = mysqli_real_escape_string($conn, $_POST['reEnternewpassword']);

    // Decrypt the member ID
    $member_id = decryptData($encrypted_member_id, ENCRYPTION_KEY);

    if ($member_id === false) {
        echo "<script>alert('Invalid member ID. Decryption failed.');</script>";
        exit;
    }

    // Validate new password and re-entered password match
    if ($new_password !== $re_enter_new_password) {
        echo "<script>alert('New password and Re-entered password do not match. Please try again.');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; }, 100);</script>";
        exit;
    }

    // Password complexity validation
    $password_pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';
    if (!preg_match($password_pattern, $new_password)) {
        echo "<script>alert('Password must contain at least 8 characters, including one uppercase letter, one lowercase letter, one number, and one special character.');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; }, 100);</script>";
        exit;
    }

    // Check if the old password matches the one in the database
    $old_password_hash = hash('sha256', $old_password);
    $query = "SELECT * FROM member WHERE member_id = '$member_id' AND password = '$old_password_hash'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {
        // Old password doesn't match
        echo "<script>alert('Old password does not match. Please try again.');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; }, 100);</script>";
        exit;
    }

    // Hash the new password
    $new_password_hash = hash('sha256', $new_password);

    // Update the password in the database
    $query = "UPDATE member SET password = '$new_password_hash' WHERE member_id = '$member_id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        // Log the activity
        log_activity($member_id, 'Updated Member Password (Member)', $conn);

        // Display success message and redirect
        echo "<script>alert('Your password has been updated successfully.');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; }, 100);</script>";
        exit;
    } else {
        // Handle errors during the update
        echo "<script>alert('Error updating password: " . mysqli_error($conn) . "');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; }, 100);</script>";
        exit;
    }
}



// Update function for editing using members account
if (isset($_POST['updateMembersInfo'])) {
    // Get the encrypted member ID from the POST request
    $encrypted_member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contactNumber']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Decrypt the member ID
    $member_id = decryptData($encrypted_member_id, ENCRYPTION_KEY);

    if ($member_id === false) {
        echo "<script>alert('Invalid member ID. Decryption failed.');</script>";
        exit;
    }

    // Prepare the update query
    $query = "UPDATE member SET 
        contact_number = '$contact_number',
        username = '$username',
        email = '$email'
        WHERE member_id = '$member_id'";

    // Execute the query
    if (mysqli_query($conn, $query)) {

        log_activity($member_id, 'Updated members details (Member)', $conn); // Log the activity
        // Encrypt the member ID for redirection
        $encrypted_member_id = encryptData($member_id, ENCRYPTION_KEY);

        echo "<script>alert('Member details updated successfully.');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; }, 100);</script>";
        exit(); // Ensure the script terminates after redirection
    } else {
        echo "<script>alert('Error updating member details: " . mysqli_error($conn) . "');</script>";

        // Encrypt the member ID for redirection
        $encrypted_member_id = encryptData($member_id, ENCRYPTION_KEY);

        // Redirect to the dashboard on error as well
        echo "<script>setTimeout(function() { window.location.href = '../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id) . "'; }, 100);</script>";
        exit(); // Ensure the script terminates after redirection
    }
}



// Update function for editing member's account information
if (isset($_POST['updateAdminInfo'])) {
    // Assuming you have sanitized inputs for security
    $member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $mname = mysqli_real_escape_string($conn, $_POST['mname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contactNumber']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Corrected query to update member information
    $query = "UPDATE member SET 
        fname = '$fname',
        mname = '$mname',
        lname = '$lname',
        contact_number = '$contact_number',
        username = '$username',
        email = '$email'
        WHERE member_id = '$member_id'"; // Removed trailing comma

    $result = mysqli_query($conn, $query);
    if ($result) {
        log_activity($member_id, 'Updated admin profile info', $conn); // Log the activity
        // Success message
        echo "<script>alert('Member details updated successfully.');</script>";
        // Redirect after success
        echo "<script>setTimeout(function() { window.location.href = '../page/admin_profile.php?id=" . $member_id . "'; }, 100);</script>";
        exit; // Ensure no further output is sent after the redirect
    } else {
        // Error message
        echo "<script>alert('Error updating member details: " . mysqli_error($conn) . "');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/admin_profile.php?id=" . $member_id . "'; }, 100);</script>";
        exit;
    }
}

// Update function for editing admin's password
if (isset($_POST['updateAdminPassword'])) {
    $member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
    $old_password = mysqli_real_escape_string($conn, $_POST['oldpassword']);
    $new_password = mysqli_real_escape_string($conn, $_POST['newpassword']);
    $re_enter_new_password = mysqli_real_escape_string($conn, $_POST['ReEnternewpassword']);

    // Validate new password and re-entered password match
    if ($new_password !== $re_enter_new_password) {
        echo "<script>alert('New password and Re-entered password do not match. Please try again.');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/admin_dashboard.php?member_id=" . $member_id . "'; }, 100);</script>";
        exit;
    }

    // Password complexity validation
    $password_pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';
    if (!preg_match($password_pattern, $new_password)) {
        echo "<script>alert('Password must contain at least 8 characters, including one uppercase letter, one lowercase letter, one number, and one special character.');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/admin_dashboard.php?member_id=" . $member_id . "'; }, 100);</script>";
        exit;
    }

    // Check if the old password matches the one in the database
    $old_password_hash = hash('sha256', $old_password);
    $query = "SELECT * FROM member WHERE member_id = '$member_id' AND password = '$old_password_hash'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {
        // Old password doesn't match
        echo "<script>alert('Old password does not match. Please try again.');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/admin_dashboard.php?member_id=" . $member_id . "'; }, 100);</script>";
        exit;
    }

    // Hash the new password
    $new_password_hash = hash('sha256', $new_password);

    // Query to update the password
    $query = "UPDATE member SET password = '$new_password_hash' WHERE member_id = '$member_id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        log_activity($member_id, 'Updated admin password', $conn);
        // Success message
        echo "<script>alert('Password updated successfully.');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/admin_dashboard.php?member_id=" . $member_id . "'; }, 100);</script>";
        exit;
    } else {
        // Error message
        echo "<script>alert('Error updating password: " . mysqli_error($conn) . "');</script>";
        echo "<script>setTimeout(function() { window.location.href = '../page/admin_dashboard.php?member_id=" . $member_id . "'; }, 100);</script>";
        exit;
    }
}

