<?php
    session_start();
    // Check if user is logged in, if not redirect to login page
    if (!isset($_SESSION['email']) && !isset($_SESSION['username'])) {
        header("Location: ../index.php");
        exit();
    }



// Include database connection
include '../conn/dbcon.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

// Function to send notification email
function send_notification_email($fname, $mname, $lname, $email,)
{
    // Create a PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Concatenate first name, middle name, and last name
        $member_name = trim("$fname $mname $lname");

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
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Loan Update Notification';
        $email_template = "
            <h2 style='color: black;'>Hello $member_name!</h2>
            <h3 style='color: black;'>Your Account has been created!</h3>
            <h3 style='color: black;'>Thank you for trusting us. Open your account and update your information.</h3>
            <p style='color: black;'>Your email is: <span style= 'font-weight: bold';>$email </span></p>
            <p style='color: black;'>Your password is:  <span style= 'font-weight: bold';> bascpcc2024 </span> </p>
            <a href='https://bascpcc.com/index.php' style='color: blue; text-decoration: none !important;'>Click this to view your account</a>

        ";
        $mail->Body = $email_template;


        // Send the email
        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        return $e->getMessage(); // Return error message
    }
}


