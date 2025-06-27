<?php

define("DATABASE", "coop_db");

class DataImport
{
    private $server = "mysql:host=localhost;dbname=" . DATABASE;
    private $username = "root";
    private $password = "";
    private $option = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
    protected $conn;

    public function connect()
    {
        try {
            $this->conn = new PDO($this->server, $this->username, $this->password, $this->option);
            return $this->conn;
        } catch (PDOException $e) {
            echo "Connection Failed." . $e->getMessage();
        }
    }

    public function insert_data($array)
    {
        $first_name = trim($array[0]);
        $middle_name = trim($array[1]);
        $last_name = trim($array[2]);
        $minus_wage = str_replace(',', '', trim($array[3])); // Assuming minus wage is in the 3rd column
        $updated_balance = str_replace(',', '', trim($array[4])); // Assuming updated balance is in the 4th column

        try {
            $connection = $this->connect();

            $stmt = $connection->prepare("SELECT member_id FROM members WHERE fname = ? AND mname = ? AND lname = ?");
            $stmt->execute([$first_name, $middle_name, $last_name]);
            $member_id = $stmt->fetchColumn();

            if ($member_id) {
                $stmt = $connection->prepare("SELECT loan_id, updated_balance FROM loan WHERE member_id = ?");
                $stmt->execute([$member_id]);
                $loan_data = $stmt->fetch();

                if ($loan_data) {
                    $loan_id = $loan_data['loan_id'];
                    $loan_updated_balance = $loan_data['updated_balance'];

                    if ($loan_updated_balance == 0) {
                        // Move record to loan_archive table
                        $stmt = $connection->prepare("INSERT INTO loan_archive SELECT * FROM loan WHERE loan_id = ?");
                        $stmt->execute([$loan_id]);

                        // Delete record from loan table
                        $stmt = $connection->prepare("DELETE FROM loan WHERE loan_id = ?");
                        $stmt->execute([$loan_id]);

                        echo "<script>alert('Loan with ID $loan_id moved to loan_archive table.');</script>";
                    } else {
                        // Update loan table
                        $stmt = $connection->prepare("UPDATE loan SET updated_balance_history = DATE_FORMAT(NOW(), '%M %e, %Y'), updated_balance = ?, minus_wage = ? WHERE member_id = ?");
                        $stmt->execute([$updated_balance, $minus_wage, $member_id]);
                        $loan_id = $loan_data['loan_id']; // Fetching loan_id here

                        $stmt = $connection->prepare("INSERT INTO loan_history (loan_id, member_id, updated_balance_history, updated_balance, minus_wage) VALUES (?, ?, DATE_FORMAT(NOW(), '%M %e, %Y'), ?, ?)");
                        $stmt->execute([$loan_id, $member_id, $updated_balance, $minus_wage]);

                        echo "<script>alert('Balance successfully updated for $first_name $middle_name $last_name');</script>";
                    }
                } else {
                    echo "<script>alert('Loan ID not found in the loan table for $first_name $middle_name $last_name');</script>";
                }
            } else {
                echo "<script>alert('Member not found in the database for $first_name $middle_name $last_name');</script>";
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
                    $updated_balance_history = date('Y-m-d'); // Or however you want to format the date
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
            $connection->rollBack();
            echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
        }
    }
}

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
