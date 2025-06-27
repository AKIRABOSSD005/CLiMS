<?php
session_start();
if (!isset($_SESSION['email']) && !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

require '../conn/dbcon.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function send_notification_email($fname, $mname, $lname, $email) {
    $mail = new PHPMailer(true);

    try {
        $member_name = trim("$fname $mname $lname");

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'coopbasc@gmail.com'; 
        $mail->Password = 'vuqy qqqj tovy aqyk'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = 587; 

        $mail->setFrom('coopbasc@gmail.com');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Loan Update Notification';
        $email_template = "
            <h2 style='color: black;'>Hello $member_name!</h2>
            <h3 style='color: black;'>Your loan has been added to the system successfully</h3>
            <p style='color: black;'>Login to your account to monitor your balance.</p>
            <a href='https://bascpcc.com/index.php' style='color: blue; text-decoration: none !important;'>Click Me</a>
        ";
        $mail->Body = $email_template;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $e->getMessage(); 
    }
}

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

if (isset($_POST['addBalanceButton'])) {
    $member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
    $loan_term_value = mysqli_real_escape_string($conn, $_POST['loan_term']);
    $loan_duration = mysqli_real_escape_string($conn, $_POST['loan_duration']);
    $share_capital_value = str_replace(',', '', mysqli_real_escape_string($conn, $_POST['share_capital']));
    $principal_amount = str_replace(',', '', mysqli_real_escape_string($conn, $_POST['principal_amount']));
    $processing_fee_value = str_replace(',', '', mysqli_real_escape_string($conn, $_POST['processing_fee']));
    $interest = str_replace(',', '', mysqli_real_escape_string($conn, $_POST['interest']));
    $net_proceeds = str_replace(',', '', mysqli_real_escape_string($conn, $_POST['net_proceeds']));
    $updated_balance = str_replace(',', '', mysqli_real_escape_string($conn, $_POST['updated_balance']));
    $comaker_1 = mysqli_real_escape_string($conn, $_POST['comaker_1']);
    $comaker_2 = mysqli_real_escape_string($conn, $_POST['comaker_2']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $errors = [];
    $numberPattern = '/^\d+(\.\d{1,2})?$/';

    if (empty($share_capital_value) || empty($principal_amount) || empty($updated_balance)) {
        $errors[] = "Please fill in all required fields.";
    }
    if (!preg_match($numberPattern, $share_capital_value)) {
        $errors[] = "Share capital should contain valid numbers only.";
    }
    if (!preg_match($numberPattern, $principal_amount)) {
        $errors[] = "Principal amount should contain valid numbers only.";
    }
    if (!preg_match($numberPattern, $updated_balance)) {
        $errors[] = "Existing balance should contain valid numbers only.";
    }
    if (empty($comaker_1) || empty($comaker_2)) {
        echo "<script>alert('Both Co-Maker#1 and Co-Maker#2 must be provided.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
        echo "<script>window.history.back();</script>";
        exit;
    }

    mysqli_begin_transaction($conn);

    $check_member_query = "SELECT member_id FROM member WHERE member_id = '$member_id'";
    $result_member = mysqli_query($conn, $check_member_query);

    if (mysqli_num_rows($result_member) == 0) {
        echo "<script>alert('Member ID does not exist.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }

    $check_loan_query = "SELECT * FROM loan WHERE member_id = '$member_id'";
    $result_loan = mysqli_query($conn, $check_loan_query);

    $loan_term_formatted = date('F j, Y', strtotime($loan_term_value));
    $updated_balance_history = date('Y-m-d');


    if (mysqli_num_rows($result_loan) > 0) {
        // Existing loan, update the record
        $row = mysqli_fetch_assoc($result_loan);
        $current_share_capital = $row['share_capital'];
        $updated_share_capital = $current_share_capital + $share_capital_value;
        $updated_loanable_amount = 3 * $updated_share_capital;
    
        // Cap the loanable amount at 100,000 for updates
        if ($updated_loanable_amount > 100000) {
            $updated_loanable_amount = 100000;
        }
    
        // Update the loan record with the new values
        $update_query = "UPDATE loan SET 
                            loan_term = '$loan_term_formatted', 
                            loan_duration = '$loan_duration', 
                            updated_balance_history = '$loan_term_value', 
                            share_capital = '$updated_share_capital', 
                            loanable_amount = '$updated_loanable_amount', 
                            principal_amount = '$principal_amount', 
                            processing_fee = '$processing_fee_value', 
                            updated_balance = '$updated_balance',
                            loan_status = '1', -- Set loan_status to 1 (active)
                            balance_update_status = '1'
                        WHERE member_id = '$member_id'";
        $result_update = mysqli_query($conn, $update_query);
    
        if (!$result_update) {
            mysqli_rollback($conn);
            echo "<script>alert('Failed to update balance.');</script>";
            echo "<script>window.history.back();</script>";
            exit;
        }
    
        // Log the activity after update
        log_activity($member_id, 'Loan Updated for', $conn);
    } else {
        // No existing loan, insert a new record
        $loanable_amount_value = 3 * $share_capital_value;
    
        // Cap the loanable amount at 100,000 for new loans
        if ($loanable_amount_value > 100000) {
            $loanable_amount_value = 100000;
        }
    
        // Insert a new loan record
        $insert_query = "INSERT INTO loan (member_id, loan_term, loan_duration, updated_balance_history, share_capital, loanable_amount, principal_amount, interest, processing_fee, updated_balance, loan_status, balance_update_status)
                        VALUES ('$member_id', '$loan_term_formatted', '$loan_duration', '$loan_term_value', '$share_capital_value', '$loanable_amount_value', '$principal_amount', '$interest', '$processing_fee_value', '$updated_balance', '1', '1')";
        $result_insert = mysqli_query($conn, $insert_query);
    
        if (!$result_insert) {
            mysqli_rollback($conn);
            echo "<script>alert('Failed to insert balance.');</script>";
            echo "<script>window.history.back();</script>";
            exit;
        }
    
        // Log the activity after insert
        log_activity($member_id, 'New Loan Added for', $conn);
    }
    
    // Get the loan ID
    $get_loan_id_query = "SELECT loan_id FROM loan WHERE member_id = '$member_id'";
    $result_loan_id = mysqli_query($conn, $get_loan_id_query);

    if (!$result_loan_id || mysqli_num_rows($result_loan_id) == 0) {
        mysqli_rollback($conn);
        echo "<script>alert('Failed to retrieve loan ID.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }

    $row = mysqli_fetch_assoc($result_loan_id);
    $loan_id = $row['loan_id'];

    // Insert into loan_history
    $insert_history_query = "INSERT INTO loan_history (loan_id, member_id, updated_balance, updated_balance_history, principal_amount)
                            VALUES ('$loan_id', '$member_id', '$updated_balance', '$loan_term_value', '$principal_amount')";
    $result_history = mysqli_query($conn, $insert_history_query);

    if (!$result_history) {
        mysqli_rollback($conn);
        echo "<script>alert('Failed to insert into loan history.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }

    // Insert co-makers
    $insert_comakers_query = "INSERT INTO comaker_name (loan_id, member_id, comaker_1, comaker_2)
                             VALUES ('$loan_id', '$member_id', '$comaker_1', '$comaker_2')";
    $result_comakers = mysqli_query($conn, $insert_comakers_query);

    if (!$result_comakers) {
        mysqli_rollback($conn);
        echo "<script>alert('Failed to insert comakers.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }

    // Inserting the annual report into the table of loan summary report
    $insert_annual_query = "INSERT INTO loan_summary_report (loan_id, member_id,annual_principal_amount,net_proceeds, annual_processing_fee, annual_loan_interest, report_history)
                            VALUES ('$loan_id', '$member_id','$principal_amount', '$net_proceeds', '$processing_fee_value', '$interest', '$loan_term_value')";
    $result_annual = mysqli_query($conn, $insert_annual_query);

    if (!$result_annual) {
        mysqli_rollback($conn);
        echo "<script>alert('Failed to insert into loan_summary_report.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }

    // Commit the transaction after all queries succeed
    mysqli_commit($conn);

    // Send notification email
    $memberQuery = "SELECT * FROM member WHERE member_id = '$member_id'";
    $memberResult = mysqli_query($conn, $memberQuery);

    if ($memberRow = mysqli_fetch_assoc($memberResult)) {
        $fname = $memberRow['fname'];
        $mname = $memberRow['mname'];
        $lname = $memberRow['lname'];
        $email = $memberRow['email'];

        send_notification_email($fname, $mname, $lname, $email);
    }

    echo "<script>alert('Balance updated and notification sent successfully.');</script>";
    echo "<script>window.location.href = '../page/viewMembers.php?id=$member_id';</script>";
    exit;
}
?>
