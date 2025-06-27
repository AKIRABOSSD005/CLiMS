<?php
require '../conn/dbcon.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['email']) && !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Function to log activity
function log_activity($member_id, $activity_type, $conn) {
    $ip_address = $_SERVER['REMOTE_ADDR']; 
    $user_agent = $_SERVER['HTTP_USER_AGENT']; 

    $query = "INSERT INTO activity_log (member_id, activity_type, ip_address, user_agent)
              VALUES ('$member_id', '$activity_type', '$ip_address', '$user_agent')";
    $conn->query($query);
}

// Function to send notification email

function send_notification_email($fname, $mname, $lname, $email, $username, $raw_password) {
    $mail = new PHPMailer(true);

    try {
        $member_name = trim("$fname $mname $lname");
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'coopbasc@gmail.com'; 
        $mail->Password   = 'vuqy qqqj tovy aqyk';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587; 

        $mail->setFrom('coopbasc@gmail.com', 'BASC Cooperative');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Welcome to BASC Cooperative - Admin Account Created';
        $email_template = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f9f9f9;
                        color: #333;
                        margin: 0;
                        padding: 0;
                    }
                    .email-container {
                        max-width: 600px;
                        margin: 20px auto;
                        padding: 20px;
                        background-color: #ffffff;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    }
                    .email-header {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .email-header h2 {
                        color: #2c3e50;
                    }
                    .email-body {
                        line-height: 1.8;
                    }
                    .email-body ul {
                        list-style: none;
                        padding: 0;
                    }
                    .email-body li {
                        margin-bottom: 10px;
                        font-size: 16px;
                    }
                    .email-footer {
                        margin-top: 20px;
                        text-align: center;
                        font-size: 14px;
                        color: #888;
                    }
                    .cta-button {
                        display: inline-block;
                        margin: 20px 0;
                        padding: 10px 20px;
                        background-color: #3498db;
                        color: #ffffff;
                        text-decoration: none;
                        font-weight: bold;
                        border-radius: 5px;
                    }
                    .cta-button:hover {
                        background-color: #2980b9;
                    }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='email-header'>
                        <h2>Welcome to BASC Cooperative, $member_name</h2>
                    </div>
                    <div class='email-body'>
                        <p>We are pleased to inform you that your admin account has been successfully created. Below are your login details:</p>
                        <ul>
                            <li><strong>Username:</strong> $username</li>
                            <li><strong>Email:</strong> $email</li>
                            <li><strong>Password:</strong> $raw_password</li>
                        </ul>
                        <p>You can access your admin dashboard by clicking the button below:</p>
                        <p style='text-align: center;'>
                            <a href='https://bascpcc.com/index.php' class='cta-button'>Login to Admin Dashboard</a>
                        </p>
                        <p><strong>Important:</strong> For your security, we strongly recommend changing your password upon first login.</p>
                    </div>
                    <div class='email-footer'>
                        <p>If you have any questions or need assistance, feel free to contact our support team.</p>
                        <p>Thank you for being part of BASC Cooperative.</p>
                    </div>
                </div>
            </body>
        </html>
        ";
        $mail->Body = $email_template;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}



// Add admin logic
if (isset($_POST['addAdmin'])) {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $mname = mysqli_real_escape_string($conn, $_POST['mname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $contactNumber = mysqli_real_escape_string($conn, $_POST['contactNumber']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $raw_password = $_POST['password']; // Raw password for email
    $hashed_password = hash('sha256', $raw_password); // Hashed password for database storage

    // Check if member already exists
    $check_member_query = "SELECT COUNT(*) AS count FROM member WHERE fname='$fname' AND mname='$mname' AND lname='$lname'";
    $check_result = $conn->query($check_member_query);
    $row = $check_result->fetch_assoc();

    if ($row['count'] > 0) {
        echo "<script>alert('Member with the same full name already exists. Please enter a different full name.');</script>";
        echo "<script>window.location.href = '../page/admin_dashboard.php';</script>";
        exit;
    }

    // Insert new admin
    $insert_member_query = "INSERT INTO member (fname, mname, lname, age, gender, contact_number, username, email, password, role_id) 
                            VALUES ('$fname', '$mname', '$lname', '$age', '$gender', '$contactNumber', '$username', '$email', '$hashed_password', '1')";
    if ($conn->query($insert_member_query) === TRUE) {
        $new_member_id = $conn->insert_id;

        // Log activity
        log_activity($new_member_id, "New Admin Added: $fname $lname", $conn);

        // Fetch member data for email
        $memberQuery = "SELECT * FROM member WHERE member_id = '$new_member_id'";
        $memberResult = $conn->query($memberQuery);

        if ($memberRow = $memberResult->fetch_assoc()) {
            $email_to_send = $memberRow['email'];
            send_notification_email(
                $fname,
                $mname,
                $lname,
                $email_to_send,
                $username,
                $raw_password
            );
        }

        echo "<script>alert('Admin added successfully! Notification email sent.');</script>";
        echo "<script>window.location.href = '../page/admin_dashboard.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
