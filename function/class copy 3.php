<?php

define("DATABASE", "u400182149_coop_db");

class DataImport
{
    // private $server = "localhost";
    // private $username = "u400182149_coopbascpcc";
    // private $password = "Te@mAre@CoopB@SCPCC2024";
    // private $dbname = "u400182149_coop_db";
    // private $option = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
    // protected $conn;

    private $server = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "u400182149_coop_db";
    private $option = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
    protected $conn;

    public function connect()
    {
        try {
            // Construct the DSN
            $dsn = "mysql:host={$this->server};dbname={$this->dbname}";
            $this->conn = new PDO($dsn, $this->username, $this->password, $this->option);
            return $this->conn;
        } catch (PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
            exit;
        }
    }

    public function insert_data($array)
    {
        $notifications = []; // Initialize notifications array

        // Trim and clean input data
        $first_name = trim($array[0]);
        $middle_name = trim($array[1]);
        $last_name = trim($array[2]);
        $minus_wage = str_replace(',', '', trim($array[3]));
        $updated_balance = str_replace(',', '', trim($array[4]));
        $principal_amount = str_replace(',', '', trim($array[5]));

        // Handle date conversion
        $dateString = str_replace(',', '', trim($array[6])); // Clean date string
        $dateObject = DateTime::createFromFormat('F j Y', $dateString); // Create DateTime object
        if ($dateObject === false) {
            $notifications[] = "Invalid date format for $first_name $middle_name $last_name.";
            return $notifications; // Exit if date format is invalid
        }
        $updated_balance_history = $dateObject->format('Y-m-d'); // Convert to YYYY-MM-DD format

        try {
            $connection = $this->connect();

            // Step 1: Validate Member Information (fname, mname, lname)
            $stmt = $connection->prepare("SELECT member_id FROM member WHERE fname = ? AND mname = ? AND lname = ?");
            $stmt->execute([$first_name, $middle_name, $last_name]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            $loan_id = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$member) {
                // If member validation fails, log error and update balance_update_status for the current loan
                $stmt_update_fail_status = $connection->prepare("UPDATE loan SET balance_update_status = 0 WHERE loan_id = ?");
                $stmt_update_fail_status->execute([$loan_id]);

                $notifications[] = "Member not found or names do not match for $first_name $middle_name $last_name.";
                return $notifications; // Stop processing this row but continue for others
            }

            $member_id = $member['member_id'];

            // Step 2: Check Loan Existence
            $stmt = $connection->prepare("SELECT loan_id, updated_balance FROM loan WHERE member_id = ?");
            $stmt->execute([$member_id]);
            $loan_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$loan_data) {
                // Log error and set balance_update_status to 0 for missing loan
                $stmt_update_fail_status = $connection->prepare("UPDATE loan SET balance_update_status = 0 WHERE member_id = ?");
                $stmt_update_fail_status->execute([$member_id]);

                $notifications[] = "Loan not found for $first_name $middle_name $last_name.";
                return $notifications; // Stop processing this row but continue for others
            }

            $loan_id = $loan_data['loan_id'];

            // Step 3: Check for duplicate entry in loan_history
            $stmt_check = $connection->prepare("SELECT COUNT(*) FROM loan_history WHERE loan_id = ? AND updated_balance_history = ?");
            $stmt_check->execute([$loan_id, $updated_balance_history]);
            $existing_record = $stmt_check->fetchColumn();

            if ($existing_record > 0) {
                // Log duplicate entry error and set balance_update_status to 0
                $stmt_update_fail_status = $connection->prepare("UPDATE loan SET balance_update_status = 0 WHERE loan_id = ?");
                $stmt_update_fail_status->execute([$loan_id]);

                $notifications[] = "Duplicate entry for $first_name $middle_name $last_name with loan_id $loan_id on $updated_balance_history.";
                return $notifications; // Stop processing this row but continue for others
            }

            // Step 4: Update Loan Information
            if ($updated_balance == 0) {
                // If updated_balance is 0, set loan status to inactive (0)
                $stmt = $connection->prepare("UPDATE loan SET loan_status = 0 WHERE loan_id = ?");
                $stmt->execute([$loan_id]);
            }

            // Update loan table with new balance, balance history, and wage
            $stmt_update = $connection->prepare("UPDATE loan SET updated_balance_history = ?, updated_balance = ?, minus_wage = ?, balance_update_status = 1 WHERE loan_id = ?");
            $stmt_update->execute([$updated_balance_history, $updated_balance, $minus_wage, $loan_id]);

            if ($stmt_update->rowCount() > 0) {
                // Insert into loan_history table
                $stmt_insert = $connection->prepare("INSERT INTO loan_history (loan_id, member_id, updated_balance_history, updated_balance, minus_wage, principal_amount) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_insert->execute([$loan_id, $member_id, $updated_balance_history, $updated_balance, $minus_wage, $principal_amount]);

                // Log activity
                $this->log_activity($member_id, "The loan balance was updated via Excel for", $connection);

                $notifications[] = "Balance updated successfully for $first_name $middle_name $last_name.";
            } else {
                // Log error and set balance_update_status to 0 if update fails
                $stmt_update_fail_status = $connection->prepare("UPDATE loan SET balance_update_status = 0 WHERE loan_id = ?");
                $stmt_update_fail_status->execute([$loan_id]);

                $this->log_activity($member_id, "Failed to update loan balance", $connection);
                $notifications[] = "Failed to update the loan balance for $first_name $middle_name $last_name.";
            }
        } catch (PDOException $e) {
            // Handle database errors and set balance_update_status to 0
            if (isset($loan_id)) {
                $stmt_update_fail_status = $connection->prepare("UPDATE loan SET balance_update_status = 0 WHERE loan_id = ?");
                $stmt_update_fail_status->execute([$loan_id]);
            }

            $notifications[] = "Database error: " . $e->getMessage();
        }

        return $notifications; // Return notifications for processing results
    }


    private function log_activity($member_id, $activity_type, $conn)
    {
        $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
        $user_agent = $_SERVER['HTTP_USER_AGENT']; // Get the user agent

        // Insert the activity log into the database
        $query = "INSERT INTO activity_log (member_id, activity_type, ip_address, user_agent)
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$member_id, $activity_type, $ip_address, $user_agent]);
    }





    public function archive_zero_balance()
    {
        try {
            $connection = $this->connect();

            // Start transaction
            $connection->beginTransaction();

            // Select rows where updated_balance is zero
            $stmt_select = $connection->prepare("SELECT * FROM loan WHERE updated_balance = 0");
            $stmt_select->execute();
            $rows = $stmt_select->fetchAll();

            // Insert selected rows into loan_archive table
            foreach ($rows as $row) {
                $loan_id = $row['loan_id'];
                $member_id = $row['member_id'];

                // Check if loan_id and member_id exist in loan table
                $stmt_check_loan = $connection->prepare("SELECT COUNT(*) FROM loan WHERE loan_id = ?");
                $stmt_check_loan->execute([$loan_id]);
                $loan_exists = $stmt_check_loan->fetchColumn();

                $stmt_check_member = $connection->prepare("SELECT COUNT(*) FROM member WHERE member_id = ?");
                $stmt_check_member->execute([$member_id]);
                $member_exists = $stmt_check_member->fetchColumn();

                // If both loan_id and member_id exist, insert into loan_archive table
                if ($loan_exists && $member_exists) {
                    $updated_balance_history = date('Y-m-d');
                    $principal_amount = $row['principal_amount'];
                    $updated_balance = $row['updated_balance'];

                    $stmt_insert = $connection->prepare("INSERT INTO loan_archive (loan_id, member_id, updated_balance_history, principal_amount, updated_balance) VALUES (?, ?, ?, ?, ?)");
                    $stmt_insert->execute([$loan_id, $member_id, $updated_balance_history, $principal_amount, $updated_balance]);
                }
            }

            // Delete selected rows from loan table
            $stmt_delete = $connection->prepare("DELETE FROM loan WHERE updated_balance = 0");
            $stmt_delete->execute();

            // Commit transaction
            $connection->commit();

            echo "<script>alert('Zero balance records archived successfully');</script>";
        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($connection) {
                $connection->rollBack();
            }
            echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
        }
    }
}

// Handle file upload and data import
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    if (!isset($_SESSION['file_uploaded']) || $_SESSION['file_uploaded'] !== true) {
        $_SESSION['file_uploaded'] = true; // Set flag to prevent reprocessing
        $dataImport = new DataImport();

        // Read CSV file
        $file = $_FILES["file"]["tmp_name"];
        $handle = fopen($file, "r");
        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            $dataImport->insert_data($row);
        }
        fclose($handle);

        // Archive zero balance records
        $dataImport->archive_zero_balance();

        // Redirect to loan_data.php after processing
        header("Location: loan_data.php");
        exit;
    }
}
