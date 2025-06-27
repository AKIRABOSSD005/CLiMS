<?php
// Start the session at the top of the script
session_start();

require_once '../conn/dbcon.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form inputs
    $member_id = isset($_POST['memberid']) ? $_POST['memberid'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Sanitize input
    $password = mysqli_real_escape_string($conn, $password);

    // Check if member ID and password are provided
    if (empty($member_id) || empty($password)) {
        echo 'missing_input'; // No member ID or password provided
        exit;
    }

    // Retrieve stored password from the database
    $query = "SELECT password FROM member WHERE member_id = '$member_id'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("Database query failed: " . mysqli_error($conn)); // Log database query error
        echo 'db_error'; // Indicate a database error
        exit;
    }

    // Check if user exists
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $stored_hashed_password = $user['password'];

        // Verify password (assuming it's hashed with SHA-256)
        if (hash('sha256', $password) === $stored_hashed_password) {
            // Set session variable for successful verification
            $_SESSION['password_verified'] = true;
            unset($_SESSION['password_error']); // Clear previous error
            echo 'success'; // Return success to indicate password verification
        } else {
            // Incorrect password
            $_SESSION['password_verified'] = false;
            $_SESSION['password_error'] = 'Incorrect password. Please try again.';
            echo 'incorrect_password'; // Indicate incorrect password
        }
    } else {
        // User not found
        $_SESSION['password_verified'] = false;
        $_SESSION['password_error'] = 'User not found.';
        echo 'user_not_found'; // Indicate user not found
    }
}
?>
