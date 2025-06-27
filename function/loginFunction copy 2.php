<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../conn/dbcon.php'; // Include your database connection file
require_once("encryption.php"); // Include the encryption functions

define('ENCRYPTION_KEY', 'XDT-YUGHH-GYGF-YUTY-GHRGFR'); // Keep the encryption key

function login($emailOrUsername, $password, $conn) {
    // Sanitize input to prevent SQL injection
    $emailOrUsername = mysqli_real_escape_string($conn, $emailOrUsername);
    $hashed_password = hash('sha256', $password); // Hash the input password using SHA256

    // Query to retrieve user information based on email or username
    $query = "SELECT * FROM members WHERE email = '$emailOrUsername' OR BINARY username = '$emailOrUsername'";  
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        // User found, verify password
        $user = mysqli_fetch_assoc($result);
        $stored_hashed_password = $user['password'];

        // Verify password
        if ($hashed_password === $stored_hashed_password) {
            // Password is correct, set session variables
            $_SESSION['member_id'] = $user['member_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role_id'] = $user['role_id'];

            // Encrypt the member_id using the function from encryption.php
            $encrypted_member_id = encryptData($user['member_id'], ENCRYPTION_KEY);

            if ($user['role_id'] == 1) {
                // Redirect to admin dashboard with encrypted member ID
                header("Location: ../page/admin_dashboard.php?member_id=" . urlencode($encrypted_member_id));
            } else {
                // Redirect to user dashboard with encrypted member ID
                header("Location: ../page/user_dashboard.php?member_id=" . urlencode($encrypted_member_id));
            }
            exit(); // Ensure no further code is executed
        } else {
            // Password is incorrect
            $_SESSION['password_error'] = 'Incorrect password.'; // Set session error for password
            $_SESSION['emailOrUsername'] = $emailOrUsername; // Keep the email/username in the session
            header('Location: ../index.php'); // Redirect to index
            exit();
        }
    } else {
        // User not found
        $_SESSION['username_error'] = 'Invalid email or username.'; // Set session error for username
        $_SESSION['emailOrUsername'] = $emailOrUsername; // Keep the email/username in the session
        header('Location: ../index.php'); // Redirect to index
        exit();
    }
}

// Check if the login form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $emailOrUsername = $_POST["emailOrUsername"];
    $password = $_POST["password"];

    login($emailOrUsername, $password, $conn); // Call the login function
}

// Handle member ID decryption after login
if (isset($_GET['member_id'])) {
    $encrypted_member_id = urldecode($_GET['member_id']);
    $member_id = decryptData($encrypted_member_id, ENCRYPTION_KEY);

    if ($member_id === false) {
        echo "Invalid member ID.";
        exit();
    }

    // For debugging purposes
    echo "Decrypted member ID: " . htmlspecialchars($member_id);

    $query = "SELECT * FROM members WHERE member_id = '$member_id'";
    $result_query = $conn->query($query);

    if (!$result_query) {
        echo "Error: " . $conn->error;
        exit();
    }

    if ($result_query->num_rows == 0) {
        echo "No member found with ID: " . htmlspecialchars($member_id);
        exit();
    }

    $result_query->close();
}
?>
