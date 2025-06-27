<?php
require '../conn/dbcon.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

// Function to send notification email
function send_notification_email($fname, $mname, $lname, $email) {
    $mail = new PHPMailer(true);

    try {
        // Concatenate first name, middle name, and last name
        $member_name = trim("$fname $mname $lname");

        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'coopbasc@gmail.com'; 
        $mail->Password = 'vuqy qqqj tovy aqyk'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender and recipient
        $mail->setFrom('coopbasc@gmail.com'); 
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Report Archived Notification';
        $email_template = "
            <h2>Hello $member_name!</h2>
            <h3>Your report request is being processed for review.</h3>
            <p>Thank you for your submission.</p>
            <a href='httpS://bascpcc.com/index.php'>Click here to review</a>
        ";
        $mail->Body = $email_template;

        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return $e->getMessage(); 
    }
}

// Fetch reports by status
if (isset($_GET['status'])) {
    $status = intval($_GET['status']);

    $query = "SELECT r.report_id, CONCAT(m.fname, ' ', m.lname) AS sender_name, r.subject, r.message, r.time_date 
              FROM report r
              LEFT JOIN member m ON r.member_id = m.member_id
              WHERE r.report_status = $status
              ORDER BY r.report_id DESC";

    $result = mysqli_query($conn, $query);

    $reports = [];
    while ($report = mysqli_fetch_assoc($result)) {
        $reports[] = $report;
    }

    echo json_encode($reports);
    exit;
}

// Fetch a specific report by ID
if (isset($_GET['id'])) {
    $report_id = intval($_GET['id']);
    
$query = "SELECT r.report_id, CONCAT(m.fname, ' ', m.lname) AS sender_name, r.subject, r.message, 
          DATE_FORMAT(r.time_date, '%M %d, %Y at %h:%i %p') AS formatted_date 
          FROM report r
          LEFT JOIN member m ON r.member_id = m.member_id
          WHERE r.report_id = $report_id";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $report = mysqli_fetch_assoc($result);
        echo json_encode($report);
    } else {
        echo json_encode(['error' => 'Report not found']);
    }
    exit;
}

// Update report status and send email (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $reportId = intval($input['id']);
    $newStatus = intval($input['status']);

    // Update report status in the database
    $query = "UPDATE report SET report_status = $newStatus WHERE report_id = $reportId";
    
    if (mysqli_query($conn, $query)) {
        // Fetch the member details to send email
        $memberQuery = "SELECT m.fname, m.mname, m.lname, m.email FROM member m
                        JOIN report r ON r.member_id = m.member_id
                        WHERE r.report_id = $reportId";
        $memberResult = mysqli_query($conn, $memberQuery);
        
        if ($memberResult && mysqli_num_rows($memberResult) > 0) {
            $member = mysqli_fetch_assoc($memberResult);
            // Send email notification for Archived status
            $emailSent = send_notification_email($member['fname'], $member['mname'], $member['lname'], $member['email']);
            echo json_encode(['success' => true, 'emailStatus' => $emailSent]);
        } else {
            echo json_encode(['success' => true, 'emailStatus' => 'Member not found']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
    exit;
}

echo json_encode(['error' => 'No valid action specified']);
?>
