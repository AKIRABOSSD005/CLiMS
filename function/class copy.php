<?php

define("DATABASE", "coop_db");

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
            echo "<script>alert('Invalid date format for $first_name $middle_name $last_name');</script>";
            return; // Exit if date format is invalid
        }
        $updated_balance_history = $dateObject->format('Y-m-d'); // Convert to YYYY-MM-DD format

        try {
            $connection = $this->connect();

            // Step 1: Check if `fname` exists in the database
            $stmt = $connection->prepare("SELECT member_id, fname FROM members WHERE fname = ?");
            $stmt->execute([$first_name]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($member) {
                $member_id = $member['member_id'];

                // Step 2: Check if `mname` matches for the member
                $stmt = $connection->prepare("SELECT mname FROM members WHERE member_id = ? AND mname = ?");
                $stmt->execute([$member_id, $middle_name]);
                $mname_result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($mname_result) {
                    // Step 3: Check if `lname` matches for the member
                    $stmt = $connection->prepare("SELECT lname FROM members WHERE member_id = ? AND lname = ?");
                    $stmt->execute([$member_id, $last_name]);
                    $lname_result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($lname_result) {
                        // All checks passed: Proceed to loan update

                        // Fetch loan data
                        $stmt = $connection->prepare("SELECT loan_id, updated_balance FROM loan WHERE member_id = ?");
                        $stmt->execute([$member_id]);
                        $loan_data = $stmt->fetch();

                        if ($loan_data) {
                            $loan_id = $loan_data['loan_id'];
                            $loan_updated_balance = $loan_data['updated_balance'];

                            // Update loan status to 0 if updated_balance is 0
                            if ($updated_balance == 0) {
                                $stmt = $connection->prepare("UPDATE loan SET loan_status = 0 WHERE loan_id = ?");
                                $stmt->execute([$loan_id]);
                            }

                            // Update loan table with new balance, balance history, and wage
                            $stmt = $connection->prepare("UPDATE loan SET updated_balance_history = ?, updated_balance = ?, minus_wage = ? WHERE loan_id = ?");
                            $stmt->execute([$updated_balance_history, $updated_balance, $minus_wage, $loan_id]);

                            // Insert into loan_history table
                            $stmt = $connection->prepare("INSERT INTO loan_history (loan_id, member_id, updated_balance_history, updated_balance, minus_wage, principal_amount) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$loan_id, $member_id, $updated_balance_history, $updated_balance, $minus_wage, $principal_amount]);

                            echo "<script>alert('Balance successfully updated for $first_name $middle_name $last_name');</script>";
                        } else {
                            echo "<script>alert('Loan ID not found in the loan table for $first_name $middle_name $last_name');</script>";
                        }
                    } else {
                        echo "<script>alert('Last name does not match for $first_name $middle_name');</script>";
                    }
                } else {
                    echo "<script>alert('Middle name does not match for $first_name');</script>";
                }
            } else {
                echo "<script>alert('First name not found in the database for $first_name');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
        }
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

                $stmt_check_member = $connection->prepare("SELECT COUNT(*) FROM members WHERE member_id = ?");
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
?>
