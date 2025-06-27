<?php


require '../conn/dbcon.php'; // Include your database connection file

// Retrieve member ID from URL parameter
if (isset($_GET['member_id'])) {
    $member_id = $_GET['member_id'];

    // Fetch member information based on member ID
    $query = "SELECT * FROM member WHERE member_id = '$member_id'";
    $result_member = mysqli_query($conn, $query);

    // Check if member exists
    if (mysqli_num_rows($result_member) == 1) {
        $member_info = mysqli_fetch_assoc($result_member);
    }

    // Fetch loan information based on member ID
    $query_loan = "SELECT * FROM loan WHERE member_id = '$member_id'";
    $result_loan = mysqli_query($conn, $query_loan);

    if (mysqli_num_rows($result_loan) > 0) {
        $loan_info = mysqli_fetch_array($result_loan);
    }

    

    // Fetch institute information based on member ID
    $query_institute = "SELECT institute.institute_name FROM institute INNER JOIN member_institute ON institute.institute_id = member_institute.institute_id WHERE member_institute.member_id = '$member_id'";
    $result_institute = mysqli_query($conn, $query_institute);

    // Check if member is associated with any institute
    if (mysqli_num_rows($result_institute) > 0) {
        $institute_info = mysqli_fetch_array($result_institute);
    }

    // Fetch computation_loan data
    $sql = "SELECT * FROM computation_loan";
    $result = $conn->query($sql);

    // Fetching computation_loan data into an associative array
    $computation_loan_data = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $computation_loan_data[] = $row;
        }
    }
}
?>