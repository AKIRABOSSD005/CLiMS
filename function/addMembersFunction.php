<?php
require '../conn/dbcon.php'; // Adjust the path as needed
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['email']) && !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

// Function to send notification email
function send_notification_email($fname, $mname, $lname, $email)
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
        <h2 style='color: #333;'>Hello $member_name,</h2>
        <h3 style='color: #333;'>Thank you for joining and trusting BASCPCC!</h3>
        <p style='color: #333;'>We are excited to have you as a member. To get started, please log into your account and update your personal information.</p>
        <p style='color: #333;'>Below are your login credentials:</p>
        <ul style='color: #333;'>
            <li><strong>Email:</strong> $email</li>
            <li><strong>Password:</strong> bascpcc2024</li>
        </ul>
        <p style='color: #333;'>For your security, we recommend updating your password after logging in.</p>
        <p style='color: #333;'>To access your account, click the link below:</p>
        <p><a href='https://bascpcc.com/index.php' style='color: #0066cc; text-decoration: none;'>Click here to login</a></p>
        <p style='color: #333;'>Best regards,</p>
        <p style='color: #333;'>BASCPCC</p>
    ";

        $mail->Body = $email_template;

        // Send the email
        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        return $e->getMessage(); // Return error message
    }
}

// Function to log activity
function log_activity($member_id, $activity_type, $conn)
{
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

if (isset($_POST['addButton'])) {
    // Retrieve form data
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $mname = mysqli_real_escape_string($conn, $_POST['mname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $membersDate = date('Y-m-d', strtotime($_POST['membersDate'])); // Format the date properly
    $membership_fee = mysqli_real_escape_string($conn, $_POST['membership_fee']);
    $tinNumber = mysqli_real_escape_string($conn, $_POST['tinNumber']);
    $contactNumber = mysqli_real_escape_string($conn, $_POST['contactNumber']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = hash('sha256', 'bascpcc2024');
    $instiName = mysqli_real_escape_string($conn, $_POST['instiName']);

    // Check if TIN number already exists
    $check_tin_query = "SELECT COUNT(*) AS count FROM member WHERE tin_number='$tinNumber'";
    $check_tin_result = $conn->query($check_tin_query);
    $row_tin = $check_tin_result->fetch_assoc();
    $existing_tin_count = $row_tin['count'];

    if ($existing_tin_count > 0) {
        // Display JavaScript alert for the error message
        echo "<script>alert('Member with the same TIN number already exists. Please enter a different TIN number.');</script>";
        // Redirect to addMembers.php after a delay
        echo "<script>setTimeout(function() { window.location.href = '../page/addMembers.php'; }, 100);</script>";
        exit;
    }

    // Check if member already exists
    $check_member_query = "SELECT COUNT(*) AS count FROM member WHERE fname='$fname' AND mname='$mname' AND lname='$lname'";
    $check_result = $conn->query($check_member_query);
    $row = $check_result->fetch_assoc();
    $existing_members_count = $row['count'];

    if ($existing_members_count > 0) {
        // Display JavaScript alert for the error message
        echo "<script>alert('Member with the same full name already exists. Please enter a different full name.');</script>";
        // Redirect to addMembers.php after a delay
        echo "<script>setTimeout(function() { window.location.href = '../page/addMembers.php'; }, 100);</script>";
        exit;
    } else {
        // Insert into members table
        $insert_member_query = "INSERT INTO member (membership_date, fname, mname, lname, age, gender, tin_number, membership_fee, contact_number, username, email, password, role_id) 
                                VALUES ('$membersDate', '$fname', '$mname', '$lname', '$age', '$gender', '$tinNumber', '$membership_fee', '$contactNumber', '$username', '$email', '$password','2')";

        if ($conn->query($insert_member_query) === TRUE) {
            // Retrieve the newly inserted member's ID
            $new_member_id = $conn->insert_id;

            // Insert into member_institute table
            $insert_member_institute_query = "INSERT INTO member_institute (member_id, institute_id) 
                                              SELECT $new_member_id, institute_id FROM institute WHERE institute_name = '$instiName'";

            if ($conn->query($insert_member_institute_query) === TRUE) {
                // Log the activity
                log_activity($new_member_id, 'A new account has been added to the system named', $conn);

                // Send email notification after account creation
                if (send_notification_email($fname, $mname, $lname, $email)) {
                    // Display success message and redirect
                    echo "<script>alert('New account added and email notification sent successfully.');</script>";
                } else {
                    echo "<script>alert('New account added, but email notification failed to send.');</script>";
                }
                echo "<script>setTimeout(function() { window.location.href = '../page/addMembers.php'; }, 100);</script>";
                exit;
            } else {
                echo "Error: " . $insert_member_institute_query . "<br>" . $conn->error;
            }
        } else {
            echo "Error: " . $insert_member_query . "<br>" . $conn->error;
        }
    }

    // Close connection
    $conn->close();
}
