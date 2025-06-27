<?php
session_start();
include '../conn/dbcon.php'; // Assuming this file contains database connection details

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

function send_password_reset($get_name, $get_email, $token)
{
    // Create a PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Assuming you are using Gmail SMTP, replace with your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = 'coopbasc@gmail.com'; // Replace with your SMTP username (your email address)
        $mail->Password   = 'vuqy qqqj tovy aqyk'; // Replace with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use STARTTLS encryption
        $mail->Port       = 587; // Port for STARTTLS encryption

        // Sender and recipient
        $mail->setFrom('coopbasc@gmail.com'); // Replace with your sender email
        $mail->addAddress($get_email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Password Notification';
        $email_template = "
        <h2 style='color: #333;'>Hello $get_name,</h2>
        <h3 style='color: #333;'>We received a request to reset the password for your account.</h3>
        <p style='color: #333;'>If you did not make this request, please ignore this email. If you did, you can reset your password using the link below:</p>
        <p style='color: #333;'><a href='https://bascpcc.com/forgot_password/pwc.php?token=$token&email=$get_email' style='color: #0066cc; text-decoration: none;'>Click here to reset your password</a></p>
        <br>
        <p style='color: #333;'>For security reasons, this link will expire in 10 minutes. Please reset your password as soon as possible.</p>
        <br>
        <p style='color: #333;'>Best regards,</p>
        <p style='color: #333;'>The BASCPCC Team</p>
    ";
    
        $mail->Body    = $email_template;

        // Send the email
        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        return $e->getMessage(); // Return error message
    }
}

if (isset($_POST['password_reset_link'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $token = md5(rand());

    $check_email = "SELECT * FROM member WHERE email='$email' LIMIT 1";
    $check_email_run = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($check_email_run) > 0) {
        $row = mysqli_fetch_array($check_email_run);
        $get_name = $row['fname'];
        $get_email = $row['email'];

        // Update the token in the database
        $update_token = "UPDATE member SET verify_token = '$token' WHERE email='$get_email' LIMIT 1";
        $update_token_run = mysqli_query($conn, $update_token);

        if ($update_token_run) {
            $send_result = send_password_reset($get_name, $get_email, $token);
            if ($send_result === true) {
                $_SESSION['status'] = "We sent you the email reset link";
            } else {
                $_SESSION['status'] = "Failed to send reset email: " . $send_result;
            }
            header("Location: pr.php");
            exit(0);
        } else {
            $_SESSION['status'] = "Something went wrong. #1";
        }
    } else {
        $_SESSION['status'] = "No email found";
    }
    header("Location: pr.php");
    exit(0);
}

if (isset($_POST['update_password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $token = mysqli_real_escape_string($conn, $_POST['password_token']);

    // Log input values for debugging
    error_log("Email: $email, Token: $token");

    // Hashing passwords
    $new_password_hashed = hash('sha256', $new_password);
    $confirm_password_hashed = hash('sha256', $confirm_password);

    if (!empty($token)) {
        if (!empty($email) && !empty($new_password) && !empty($confirm_password)) {
            // Checking if the token is valid
            $check_token = "SELECT verify_token FROM member WHERE verify_token = '$token' LIMIT 1";
            $check_token_run = mysqli_query($conn, $check_token);

            if (!$check_token_run) {
                error_log("Token query failed: " . mysqli_error($conn));
            }

            if (mysqli_num_rows($check_token_run) > 0) {
                // Token is valid
                if ($new_password_hashed == $confirm_password_hashed) {
                    $update_password = "UPDATE member SET password='$new_password_hashed' WHERE verify_token='$token' LIMIT 1";
                    $update_password_run = mysqli_query($conn, $update_password);
            
                    if ($update_password_run) {
                        $_SESSION['status'] = "New password successfully updated";
                        header("Location: ../index.php");
                        exit(0);
                    } else {
                        error_log("Password update failed: " . mysqli_error($conn));
                        $_SESSION['status'] = "Did not update password, something went wrong";
                        header("Location: pwc.php?token=$token&email=$email");
                        exit(0);
                    }
                } else {
                    $_SESSION['status'] = "Password and Confirm password do not match";
                    header("Location: pwc.php?token=$token&email=$email");
                    exit(0);
                }
            } else {
                $_SESSION['status'] = "Invalid Token";
                header("Location: pwc.php?token=$token&email=$email");
                exit(0);
            }
        } else {
            $_SESSION['status'] = "All fields are mandatory";
            header("Location: pwc.php?token=$token&email=$email");
            exit(0);
        }
    } else {
        $_SESSION['status'] = "No token available";
        header("Location: pwc.php");
        exit(0);
    }
}