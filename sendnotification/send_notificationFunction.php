<?php
session_start();
// Check if user is logged in; if not, redirect to the login page
if (!isset($_SESSION['email']) && !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
include '../conn/dbcon.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

// Function to send notification email
function send_notification_email($fname, $mname, $lname, $email, $loan_duration, $updated_balance_history, $minus_wage, $updated_balance, $loan_start_date, $loan_end_date)
{
    $mail = new PHPMailer(true);

    try {
        $member_name = trim("$fname $mname $lname");

        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'coopbasc@gmail.com';
        $mail->Password   = 'vuqy qqqj tovy aqyk';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient
        $mail->setFrom('coopbasc@gmail.com');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Loan Update Notification';

        // Format the updated_balance_history if it is a valid date
        $formatted_updated_balance_history = (strtotime($updated_balance_history)) ? date('F j, Y', strtotime($updated_balance_history)) : 'Invalid Date';

        $email_template = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        margin: 0;
                        padding: 0;
                    }
                    .email-container {
                        width: 100%;
                        max-width: 600px;
                        margin: 0 auto;
                        padding: 20px;
                        background-color: #ffffff;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    }
                    .email-header {
                        text-align: center;
                        color: #2C3E50;
                    }
                    .email-header h2 {
                        margin: 0;
                        font-size: 24px;
                        font-weight: bold;
                    }
                    .email-header h3 {
                        margin-top: 5px;
                        font-size: 18px;
                        color: #3498DB;
                    }
                    .email-body {
                        font-size: 16px;
                        color: #555;
                        margin-top: 20px;
                    }
                    .email-body p {
                        margin: 10px 0;
                    }
                    .email-body strong {
                        color: #2C3E50;
                    }
                    .email-footer {
                        margin-top: 20px;
                        text-align: center;
                        font-size: 14px;
                    }
                    .email-footer a {
                        color: #3498DB;
                        text-decoration: none;
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='email-header'>
                        <h2>Hello $member_name,</h2>
                        <h3>We are notifying you of an update to your loan details.</h3>
                    </div>
                    <div class='email-body'>
                        <p><strong>Loan Duration:</strong> $loan_duration months</p>
                        <p><strong>Loan Start Date:</strong> $loan_start_date</p>
                        <p><strong>Loan End Date:</strong> $loan_end_date</p>
                        <p><strong>Updated Balance History:</strong> $formatted_updated_balance_history</p>
                        <p><strong>Minus Wage:</strong> ₱ $minus_wage</p>
                        <p><strong>Updated Balance:</strong> ₱ $updated_balance</p>
                    </div>
                    <div class='email-footer'>
                        <p>If you wish to view more details, please <a href='https://bascpcc.com/index.php'>click here</a>.</p>
                    </div>
                </div>
            </body>
        </html>
    ";
    
        $mail->Body = $email_template;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

// Check if the form was submitted
if (isset($_POST['sendEmail'])) {
    $member_id = $_POST['member_id']; // Get the member ID passed from the form

    // SQL query to fetch loan and loan summary data
    $loanDataQuery = "
        SELECT 
            loan.loan_duration,
            loan.updated_balance_history,
            loan.minus_wage,
            loan.updated_balance,
            loan_summary_report.report_history
        FROM loan
        JOIN loan_summary_report ON loan.member_id = loan_summary_report.member_id
        WHERE loan.member_id = ?
    ";
    $loanDataStatement = mysqli_prepare($conn, $loanDataQuery);

    // Bind the member ID parameter
    mysqli_stmt_bind_param($loanDataStatement, "i", $member_id);
    mysqli_stmt_execute($loanDataStatement);
    $loanDataResult = mysqli_stmt_get_result($loanDataStatement);

    // Retrieve member data
    $memberQuery = "SELECT * FROM member WHERE member_id = ?";
    $memberStatement = mysqli_prepare($conn, $memberQuery);
    mysqli_stmt_bind_param($memberStatement, "i", $member_id);
    mysqli_stmt_execute($memberStatement);
    $memberResult = mysqli_stmt_get_result($memberStatement);

    if ($memberRow = mysqli_fetch_assoc($memberResult)) {
        $fname = $memberRow['fname'];
        $mname = $memberRow['mname'];
        $lname = $memberRow['lname'];
        $email = $memberRow['email'];

        // Process loan data
        if ($loanDataRow = mysqli_fetch_assoc($loanDataResult)) {
            $loan_duration = $loanDataRow['loan_duration'];
            $updated_balance_history = $loanDataRow['updated_balance_history'];
            $minus_wage = $loanDataRow['minus_wage'];
            $updated_balance = $loanDataRow['updated_balance'];

            // Extract and parse the original loan date
            $report_history = $loanDataRow['report_history'];

            // Ensure report_history is valid
            if (!empty($report_history) && strtotime($report_history)) {
                // Format the loan start date as "Month Day, Year"
                $loan_start_date = date('F j, Y', strtotime($report_history));

                // Calculate loan end date based on loan duration in months
                $loan_end_date = date('F j, Y', strtotime("+$loan_duration", strtotime($report_history)));
            } else {
                // Handle invalid or missing dates
                $loan_start_date = "Invalid Date";
                $loan_end_date = "Invalid Date";
            }

            // Send email
            $sendResult = send_notification_email(
                $fname,
                $mname,
                $lname,
                $email,
                $loan_duration,
                $updated_balance_history,
                $minus_wage,
                $updated_balance,
                $loan_start_date,
                $loan_end_date
            );
        }
    }

    // Close statements
    mysqli_stmt_close($loanDataStatement);
    mysqli_stmt_close($memberStatement);

    // Redirect after processing
    header("Location: ../page/sendNotification.php");
    exit();
}

// Send to all members
if (isset($_POST['sendEmailToAll'])) {
    // Get all members
    $membersQuery = "SELECT * FROM member";
    $membersResult = mysqli_query($conn, $membersQuery);

    while ($memberRow = mysqli_fetch_assoc($membersResult)) {
        $member_id = $memberRow['member_id'];
        $fname = $memberRow['fname'];
        $mname = $memberRow['mname'];
        $lname = $memberRow['lname'];
        $email = $memberRow['email'];

        // Fetch loan data for each member
        $loanDataQuery = "
            SELECT 
                loan.loan_duration,
                loan.updated_balance_history,
                loan.minus_wage,
                loan.updated_balance,
                loan_summary_report.report_history
            FROM loan
            JOIN loan_summary_report ON loan.member_id = loan_summary_report.member_id
            WHERE loan.member_id = ?
        ";
        $loanDataStatement = mysqli_prepare($conn, $loanDataQuery);
        mysqli_stmt_bind_param($loanDataStatement, "i", $member_id);
        mysqli_stmt_execute($loanDataStatement);
        $loanDataResult = mysqli_stmt_get_result($loanDataStatement);

        if ($loanDataRow = mysqli_fetch_assoc($loanDataResult)) {
            $loan_duration = $loanDataRow['loan_duration'];
            $updated_balance_history = $loanDataRow['updated_balance_history'];
            $minus_wage = $loanDataRow['minus_wage'];
            $updated_balance = $loanDataRow['updated_balance'];
            $report_history = $loanDataRow['report_history'];

            if (!empty($report_history) && strtotime($report_history)) {
                $loan_start_date = date('F j, Y', strtotime($report_history));
                $loan_end_date = date('F j, Y', strtotime("+$loan_duration months", strtotime($report_history)));
            } else {
                $loan_start_date = "Invalid Date";
                $loan_end_date = "Invalid Date";
            }

            // Send email
            send_notification_email(
                $fname,
                $mname,
                $lname,
                $email,
                $loan_duration,
                $updated_balance_history,
                $minus_wage,
                $updated_balance,
                $loan_start_date,
                $loan_end_date
            );
        }

        mysqli_stmt_close($loanDataStatement);
    }

    // Redirect after sending
    header("Location: ../page/sendNotification.php?status=success");
    exit();
}
