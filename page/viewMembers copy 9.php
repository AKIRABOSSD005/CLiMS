<?php
session_start();

// Check if user is logged in and has admin role (role_id == 1)
if (!isset($_SESSION['email']) || !isset($_SESSION['username']) || $_SESSION['role_id'] != 1) {
    // If not logged in or not an admin, check if the user is logged in at all
    if (!isset($_SESSION['email']) || !isset($_SESSION['username'])) {
        // If user is not logged in at all, redirect to index.php
        header("Location: ../index.php");
    } else {
        // If logged in but not an admin, redirect to user_dashboard.php
        header("Location: user_dashboard.php");
    }
    exit();
}

require '../conn/dbcon.php';
$newReportsQuery = "SELECT COUNT(*) AS new_report_count FROM report WHERE report_status = 1";
$newReportsResult = mysqli_query($conn, $newReportsQuery);
$newReportCount = 0;

if ($newReportsResult && $row = mysqli_fetch_assoc($newReportsResult)) {
    $newReportCount = $row['new_report_count'];
}



?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BASCPCC</title>
    <link rel="stylesheet" href="../assets/bootstrap/font/lineicons.css">
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/style.css">

    <link rel="icon" href="../assets/pictures/cooplogo.jpg" type="image/x-icon"> <!-- Adjust the path accordingly -->
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"> <!-- Adjust the path accordingly -->
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/pictures/cooplogo.jpg">
    <!-- Adjust the path accordingly -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/pictures/cooplogo.jpg">
</head>

<body>
    <?php
    require("../conn/dbcon.php");

    if (isset($_GET['id'])) {
        $id = mysqli_real_escape_string($conn, $_GET['id']);
        $query = "SELECT m.member_id, m.fname, m.mname, m.lname, m.membership_date, m.tin_number, m.username, m.email, m.contact_number, m.pictures, i.institute_name,l.loan_term, l.minus_wage, l.loan_duration, l.share_capital,l.loanable_amount,l.principal_amount, l.updated_balance, l.loan_status
            FROM member m
            INNER JOIN member_institute mi ON m.member_id = mi.member_id
            INNER JOIN institute i ON mi.institute_id = i.institute_id
            LEFT JOIN loan l ON m.member_id = l.member_id
            WHERE m.member_id = '$id'";


        $result_query = $conn->query($query);


        if ($result_query && mysqli_num_rows($result_query) > 0) {
            $data = mysqli_fetch_array($result_query);

            // Determine loan status
            $loan_query = "SELECT loan_status FROM loan WHERE member_id = '$id' ORDER BY loan_id DESC LIMIT 1";

            $loan_result = $conn->query($loan_query);

            if ($loan_result && $loan_result->num_rows > 0) {
                $loan_row = $loan_result->fetch_assoc();
                $loan_status = $loan_row['loan_status'];


                // Check if the loan status is '1' for ACTIVE
                if ($loan_status == '1') {
                    $loan_status = 'ACTIVE';
                } elseif ($loan_status == '0') { // Corrected else condition using elseif
                    $loan_status = 'INACTIVE';
                }
            } else {
                $loan_status = "No Loan Found";
            }
            //  echo "Profile Picture Path: " . $data['pictures'];
    ?>
            <div class="wrapper">
                <aside id="sidebar" class="sticky-sidebar">
                    <div class="d-flex">
                        <button class="toggle-btn" type="button">
                            <i class="lni lni-grid-alt"></i>
                        </button>
                        <div class="sidebar-logo">
                            <a href="admin_dashboard.php">BASCPCC</a>
                        </div>
                    </div>
                    <ul class="sidebar-nav">

                        <li class="sidebar-item">
                            <a href="admin_dashboard.php" class="sidebar-link" title="Dashboard">
                                <i class="lni lni-bar-chart"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="addMembers.php" class="sidebar-link active" title="Add Members">
                                <i class="lni lni-user"></i>
                                <span>Add Members</span>
                            </a>
                        </li>


                        <li class="sidebar-item">
                            <a href="loan_data.php" class="sidebar-link" title="Send Notification"
                                onclick="loan_data(); return false;">
                                <i class="lni lni-layout"></i>
                                <span>Update Balance</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="sendNotification.php" class="sidebar-link" title="Send Notification">
                                <i class="lni lni-popup"></i>
                                <span>Send Notification</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="viewReports.php" class="sidebar-link" title="Reports">
                                <i class="lni lni-envelope"></i>
                                <span>Reports</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="activity_log.php" class="sidebar-link" title="Reports">
                                <i class="lni lni-notepad"></i>
                                <span>Activity Log</span>
                            </a>
                        </li>


                    </ul>

                </aside>


                <div class="main">

                    <nav class="navbar navbar-expand px-3 py-3 custom-navbar">
                        <div class="container-fluid">
                            <!-- Logo and Text -->
                            <a class="navbar-brand d-flex align-items-center" href="#">
                                <img src="../assets/pictures/basclogo.png" alt="Logo" width="50" height="50" class="me-2">
                                <!-- Update path to your logo -->
                                <span class="d-none d-md-inline text-white"
                                    style="font-family: 'Times New Roman', Times, serif;">BULACAN AGRICULTURAL STATE
                                    COLLEGE</span> <!-- Hide text on mobile -->
                            </a>

                            <?php
                            require '../conn/dbcon.php';

                            // Check if the member_id is set in the session
                            if (isset($_SESSION['member_id'])) {
                                $member_id = $_SESSION['member_id'];
                                $query_admin = "SELECT * FROM member WHERE member_id = '$member_id'";

                                $admin_result = mysqli_query($conn, $query_admin);

                                if ($admin_result && mysqli_num_rows($admin_result) > 0) {
                                    $admin_info = mysqli_fetch_assoc($admin_result);
                                }
                            }
                            ?>

                            <div class="navbar-collapse collapse">
                                <ul class="navbar-nav ms-auto">
                                    <li class="nav-item dropdown">
                                        <a href="admin_profile.php?id=<?php echo $admin_info['member_id']; ?>"
                                            class="nav-icon pe-md-0 dropdown-toggle d-flex align-items-center"
                                            id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">

                                            <span class="name"> <?= $admin_info['fname'] ?? ''; ?>
                                                <?= $admin_info['mname'] ?? ''; ?> <?= $admin_info['lname'] ?? ''; ?>
                                            </span>

                                            <img src="<?= !empty($admin_info['pictures']) ? '../assets/pictures/memberPictures/' . $admin_info['pictures'] : '../assets/pictures/account.png'; ?>"
                                                class="avatar img-fluid rounded-circle ms-2" alt="">

                                            <!-- Notification bell with count badge -->
                                            <i class="lni lni-alarm"
                                                style="font-size: 1.5rem; position: relative; margin-left: 10px;">
                                                <?php if ($newReportCount > 0): ?>
                                                    <span class="badge bg-danger"
                                                        style="position: absolute; top: -5px; right: -10px; font-size: 0.8rem;">
                                                        <?= $newReportCount; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end mt-2" aria-labelledby="navbarDropdown">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="viewReports.php?id=<?php echo $admin_info['member_id']; ?>">
                                                    Notification
                                                    <?php if ($newReportCount > 0): ?>
                                                        <span class="badge bg-danger"><?= $newReportCount; ?></span>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                            <hr class="dropdown-divider">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="admin_profile.php?id=<?php echo $admin_info['member_id']; ?>">Profile</a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><a class="dropdown-item" href="../function/logoutFunction.php">Logout</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </nav>



                    <!-- Content Wrapper -->
                    <div id="content-wrapper" class="d-flex flex-column">
                        <style>
                            /* Custom CSS to remove border from container, row, and column */
                            .custom-container,
                            .custom-row,
                            .custom-col {
                                border: none;
                            }
                        </style>
                        <div class="container mt-0">
                            <div class="row" style="border: none !important;">
                                <div class="col-md-4 mt-0">
                                    <!-- Profile Info Card -->
                                    <div class="card mb-1"> <!-- Adjust the height as needed -->
                                        <div class="card-body text-center">
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#changeProfilePictureModal">
                                                <img src="<?= !empty($data['pictures']) ? '../assets/pictures/memberPictures/' . $data['pictures'] : '../assets/pictures/account.png'; ?>"
                                                    class="avatar img-fluid rounded-circle" alt="image"
                                                    style="width: 200px; height: 200px; object-fit: cover;">
                                                <!-- Set width and let height adjust automatically -->
                                            </a>

                                            <br><br>
                                            <p class="card-text" style="background-color:<?=
                                                                                            $loan_status == 'INACTIVE' ? 'red' : ($loan_status == 'ACTIVE' ? 'green' : ($loan_status == 'No Loan Found' ? 'orange' : 'gray'));
                                                                                            ?>
                                            ; color: white; padding: 5px; border-radius: 20px;">
                                                Loan Status: <?= $loan_status; ?>
                                            </p>

                                            <p class="card-text">First Name: <?= $data['fname']; ?></p>
                                            <p class="card-text">Middle Name: <?= $data['mname']; ?></p>
                                            <p class="card-text">Last Name: <?= $data['lname']; ?></p>

                                            <p class="card-text">Institute: <?= $data['institute_name']; ?></p>
                                        </div>
                                        <div class="button-loan my-2 py-2 text-center">

                                            <!-- Hidden inputs to pass values to JavaScript -->
                                            <input type="hidden" id="principalAmount" value="<?= isset($data['principal_amount']) ? $data['principal_amount'] : 0; ?>">
                                            <input type="hidden" id="updatedBalance" value="<?= isset($data['updated_balance']) ? $data['updated_balance'] : 0; ?>">
                                            <!-- Button -->
                                            <button id="addBalance" name="addBalance" class="btn btn-primary mt-2"
                                                data-bs-toggle="modal" data-bs-target="#addLoanModal" disabled>Loan Application</button>

                                            <a href="#" class="btn btn-warning mt-2" data-bs-toggle="modal"
                                                data-bs-target="#editMemberModal"
                                                data-member-id="<?= $data['member_id']; ?>">Edit</a>
                                            <a href="#" class="btn btn-success mt-2" data-bs-toggle="modal"
                                                data-bs-target="#viewMemberModal"
                                                data-member-id="<?= $data['member_id']; ?>">View</a>
                                            <br><br>
                                        </div>
                                    </div>



                                </div>




                                <div class="col-md-8">

                                    <div class="row custom-row-Cards">
                                        <div class="col-sm-12 col-md-3 mb-2">
                                            <!-- Membership Date Card -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="font-weight: bold;">Membership Date</h6>
                                                    <h5 class="text-center">
                                                        <?= date('F j, Y', strtotime($data['membership_date'])); ?>
                                                    </h5>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-3 mb-2">
                                            <!-- Capital Card -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="font-weight: bold;">Share Capital Amount</h6>
                                                    <h5 class="text-center">₱
                                                        <?= isset($data['share_capital']) ? number_format($data['share_capital'], 2) : '0.00'; ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-3 mb-2">
                                            <!-- Capital Card -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="font-weight: bold;">Principal Amount</h6>
                                                    <h5 class="text-center">₱
                                                        <?= isset($data['principal_amount']) ? number_format($data['principal_amount'], 2) : '0.00'; ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-3 mb-2">
                                            <!-- Capital Card -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="font-weight: bold;">Loanable Amount</h6>
                                                    <h5 class="text-center">₱
                                                        <?= isset($data['loanable_amount']) ? number_format($data['loanable_amount'], 2) : '0.00'; ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="row custom-row-Cards">


                                        <style>
                                            /* Custom CSS for auto-fitting text */
                                            .card-body h5 {
                                                white-space: normal;
                                                word-wrap: break-word;
                                            }
                                        </style>
                                        <div class="col-sm-12 col-md-3 mb-2">
                                            <!-- Membership Date Card -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="font-weight: bold;">Loan Application Start
                                                    </h6>
                                                    <h5 class="text-center">
                                                        <?php
                                                        // Check if loan start date is available
                                                        if (!empty($data['loan_term'])) {
                                                            echo date('F j, Y', strtotime($data['loan_term']));
                                                        } else {
                                                            echo ""; // Display default value if loan start date is not available
                                                        }
                                                        ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                        <style>
                                            /* Custom CSS for adjusting text wrapping */
                                            .long-month {
                                                white-space: nowrap;
                                                /* Prevents wrapping */
                                                overflow: hidden;
                                                /* Hides overflow */
                                                text-overflow: ellipsis;
                                                /* Displays an ellipsis (...) to indicate overflow */
                                            }
                                        </style>



                                        <div class="col-sm-12 col-md-3 mb-2">
                                            <!-- Membership Date Card -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="font-weight: bold;">Loan Application End</h6>
                                                    <div class="text-center">
                                                        <h5 class="long-month">
                                                            <?php
                                                            // Check if loan start date and loan duration are available
                                                            if (!empty($data['loan_term']) && !empty($data['loan_duration'])) {
                                                                // Function to extract numeric part from the string
                                                                function extractNumericPart($string)
                                                                {
                                                                    // Extract numeric part using regular expression
                                                                    preg_match('/(\d+)/', $string, $matches);
                                                                    // If numeric part found, return it, else return 0
                                                                    return isset($matches[0]) ? intval($matches[0]) : 0;
                                                                }

                                                                // Convert loan duration to months
                                                                if (is_numeric($data['loan_duration'])) {
                                                                    $loan_duration_months = intval($data['loan_duration']);
                                                                } else {
                                                                    $loan_duration_months = extractNumericPart($data['loan_duration']);
                                                                }

                                                                // Calculate loan end date
                                                                $loan_end_timestamp = strtotime("+" . $loan_duration_months . " months", strtotime($data['loan_term']));
                                                                $loan_end_date = date('F j, Y', $loan_end_timestamp);
                                                                echo $loan_end_date;
                                                            } else {
                                                                echo ""; // Display default value if loan start date or loan duration is not available
                                                            }
                                                            ?>
                                                        </h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>




                                        <div class="col-sm-12 col-md-3 mb-2">
                                            <!-- Capital Card -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="font-weight: bold;">Existing Balance Amount
                                                    </h6>
                                                    <h5 class="text-center">₱
                                                        <?= isset($data['updated_balance']) ? number_format($data['updated_balance'], 2) : '0.00'; ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-3 mb-2">
                                            <!-- Capital Card -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="font-weight: bold;">Wage / Salary Deduction
                                                    </h6>
                                                    <h5 class="text-center">₱
                                                        <?= isset($data['minus_wage']) ? number_format($data['minus_wage'], 2) : '0.00'; ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Table -->
                                    <div class="table-responsive" style="max-height: 395px; overflow-y: auto;">
                                        <div class="d-flex justify-content-between sticky-fix-top">
                                            <input type="text" id="searchInput" onkeyup="searchTable()"
                                                placeholder="Search for names..." class="form-control mb-3"
                                                style="font-size: 14px; padding: 5px;">
                                            <span class="input-group-btn">
                                                <button class="btn btn-success" type="button"
                                                    style="font-size: 12px; padding: 5px 10px;">
                                                    <i class="lni lni-magnifier"></i>
                                                </button>
                                            </span>

                                        </div>
                                        <table class="table">
                                            <thead class="sticky-top">
                                                <tr>
                                                    <th>Principal Amount</th>
                                                    <th>Wage Deduction</th>
                                                    <th>Existing Balance</th>
                                                    <th>Updated Date History</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query_loan_history = "SELECT lh.updated_balance, lh.updated_balance_history, lh.principal_amount, lh.minus_wage, l.loan_duration
                                                FROM loan_history lh
                                                INNER JOIN loan l ON lh.loan_id = l.loan_id
                                                WHERE lh.member_id = '$id'
                                                ORDER BY lh.loan_history_id DESC";

                                                $result_loan_history = mysqli_query($conn, $query_loan_history);

                                                if ($result_loan_history && mysqli_num_rows($result_loan_history) > 0) {
                                                    while ($loan_details = mysqli_fetch_assoc($result_loan_history)) {
                                                        $dateObject = DateTime::createFromFormat('Y-m-d', $loan_details['updated_balance_history']); // Change data to loan_details
                                                        $formatted_date = $dateObject ? $dateObject->format('F j, Y') : 'Invalid date'; // Handle invalid date
                                                ?>
                                                        <tr>
                                                            <td>₱ <?= number_format($loan_details['principal_amount'], 2); ?></td>
                                                            <td>₱ <?= number_format($loan_details['minus_wage'], 2); ?></td>
                                                            <td>₱ <?= number_format($loan_details['updated_balance'], 2); ?></td>
                                                            <td><?= htmlspecialchars($formatted_date); ?></td>
                                                            <!-- Use formatted date here -->
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='4' style='text-align: center;'>No loan history found.</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>


                                </div>
                            </div>

                    <?php
                } else {
                    echo "No data found.";
                }
            }
                    ?>

                        </div>
                    </div>
                    <div class="footer-right d-flex justify-content-center align-items-end mt-2 pt-2 fw-bold"
                        style="position: relative; background-color: #1d6325;">
                        <pre
                            class="sidebar-footer-text text-light fw-bold text-center"> A capstone project designed and developed by <a href="developer.php" class="text-decoration-none text-light">TEAM AREA</a></pre>
                    </div>
                </div>



                <!-- Modal for Adding Loan -->
                <div class="modal fade" id="addLoanModal" tabindex="-1" aria-labelledby="addLoanModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #2e7d32; color: white;">
                                <h5 class="modal-title" id="addLoanModalLabel">Add Balance</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">


                                <?php
                                // Fetch the updated_balance from the database
                                $updated_balance = isset($data['updated_balance']) ? $data['updated_balance'] : 0.00; ?>
                                <?php
                                if (isset($_GET['id'])) {
                                    $id = mysqli_real_escape_string($conn, $_GET['id']);
                                    $query = "SELECT m.member_id, l.updated_balance, l.share_capital, l.loan_duration, l.loanable_amount
                              FROM member m
                              LEFT JOIN loan l ON m.member_id = l.member_id
                              WHERE m.member_id = '$id'";
                                    $result_query = $conn->query($query);

                                    if ($result_query && mysqli_num_rows($result_query) > 0) {
                                        $data = mysqli_fetch_array($result_query);
                                    }
                                ?>
                                    <form action="../function/addBalanceFunction.php" method="POST">

                                        <input type="hidden" name="member_id" value="<?= $data['member_id']; ?>">
                                        <input type="hidden" id="processing_fee_hidden" name="processing_fee">
                                        
                                        <input type="hidden" name="status" value="1">

                                        <!-- Loan Application Date and Loan Duration -->
                                        <div class="row" style="padding: 10px 0;">
                                            <div class="col-md-6">
                                                <label for="loan_term" class="form-label">Loan Application Date</label>
                                                <input type="date" class="form-control" id="loan_term" name="loan_term" required
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="loan_duration" class="form-label">Loan Duration</label>
                                                <select class="form-control" id="loan_duration" name="loan_duration" required
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                    <option value="5 Months">5 Months</option>
                                                    <option value="10 Months">10 Months</option>
                                                    <option value="15 Months">15 Months</option>
                                                    <option value="20 Months">20 Months</option>
                                                </select>
                                            </div>
                                        </div>


                                        <?php
// Ensure `member_id` is passed in the URL
if (isset($_GET['id'])) {
    // Sanitize and assign `member_id`
    $member_id = mysqli_real_escape_string($conn, trim($_GET['id']));

    // Query to check for existing loan records
    $query = "SELECT * FROM loan WHERE member_id = '$member_id'";
    $result = mysqli_query($conn, $query);

    // Variables to store data
    $loan_data_exists = false;
    $loan_data = null;

    // Check if any loan records exist
    if ($result && mysqli_num_rows($result) > 0) {
        $loan_data = mysqli_fetch_assoc($result);
        $loan_data_exists = true;
    }
} else {
    // Handle case where member_id is not set in the URL
    $loan_data_exists = false;
    $loan_data = null;
}
?>






                                        <!-- Share Capital and Loanable Amount -->
                                        <div class="row mt-3" style="padding: 10px 0;">
                                            <div class="col-md-6">
                                                <label for="share_capital" class="form-label">Share Capital</label>
                                                <input type="text" class="form-control" id="share_capital" name="share_capital"
                                                    placeholder="₱<?= number_format($data['share_capital'], 2); ?>"
                                                    value=" <?= number_format($data['share_capital'], 2); ?> "
                                                    required
                                                    oninput="calculateLoanableAmount()"
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="capitalBuildUP" class="form-label">Capital Build Up</label>
                                                <input type="text" id="capitalBuildUp" name="capitalBuildUp" style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                            </div>



                                            <div class="col-md-6">
                                                <label for="loanable_amount" class="form-label">Loanable Amount</label>
                                                <input type="hidden" class="form-control" id="loanable_amount"
                                                    value="₱<?= number_format($data['loanable_amount'], 2); ?>" readonly
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                <input type="text" class="form-control" id="loanable_amount" name="loanable_amount"
                                                    value="₱<?= number_format($data['loanable_amount'], 2); ?>" readonly
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                            </div>
                                        </div>

                                        <!-- Principal Amount, Processing Fee, and Interest -->
                                        <div class="row mt-3" style="padding: 10px 0;">
                                            <div class="col-md-6">
                                                <label for="principal_amount" class="form-label">Principal Amount</label>
                                                <select class="form-control" id="principal_amount" name="principal_amount" required
                                                    onchange="updateInterest()"
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                    <option value="5000.00">₱5000.00</option>
                                                    <option value="10000.00">₱10000.00</option>
                                                    <option value="15000.00">₱15000.00</option>
                                                    <option value="20000.00">₱20000.00</option>
                                                    <option value="25000.00">₱25000.00</option>
                                                    <option value="30000.00">₱30000.00</option>
                                                    <option value="35000.00">₱35000.00</option>
                                                    <option value="40000.00">₱40000.00</option>
                                                    <option value="45000.00">₱45000.00</option>
                                                    <option value="50000.00">₱50000.00</option>
                                                    <option value="55000.00">₱55000.00</option>
                                                    <option value="60000.00">₱60000.00</option>
                                                    <option value="65000.00">₱65000.00</option>
                                                    <option value="70000.00">₱70000.00</option>
                                                    <option value="75000.00">₱75000.00</option>
                                                    <option value="80000.00">₱80000.00</option>
                                                    <option value="85000.00">₱85000.00</option>
                                                    <option value="90000.00">₱90000.00</option>
                                                    <option value="95000.00">₱95000.00</option>
                                                    <option value="100000.00">₱100000.00</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="processing_fee" class="form-label">Processing Fee</label>
                                                <input type="text" class="form-control" id="processing_fee"
                                                    placeholder=" Enter Processing Fee" readonly
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="interest" class="form-label">Interest</label>
                                                <input type="text" class="form-control" id="interest" name="interest"
                                                    placeholder="Enter interest" readonly
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="net_proceeds" class="form-label">Net Proceeds</label>
                                                <input type="text" class="form-control" id="totalAmount" name="net_proceeds"
                                                    placeholder=" Enter Net Proceeds" readonly
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">


                                            </div>

                                            <!-- balance input field -->
                                            <?php
                                            $existing_loan_records = false;

                                            // Check if member_id is set in GET parameters
                                            if (isset($_GET['member_id'])) {
                                                $member_id = mysqli_real_escape_string($conn, $_GET['member_id']);

                                                // Query to check for existing loan records
                                                $existing_loan = "SELECT * FROM loan WHERE member_id = '$member_id'";
                                                $existing_result = mysqli_query($conn, $existing_loan);

                                                // If there are records, set $existing_loan_records to true
                                                if ($existing_result && mysqli_num_rows($existing_result) > 0) {
                                                    $existing_loan_records = true;
                                                }
                                            }

                                            // Check if updated_balance exists in $data
                                            $has_updated_balance = isset($data['updated_balance']) && $data['updated_balance'] != 0;

                                            ?>

                                            <div class="col-md-6 mt-3">
                                                <label for="updated_balance" class="form-label">Balance</label>
                                                <input type="text"
                                                    class="form-control"
                                                    id="updated_balance"
                                                    name="updated_balance"
                                                    placeholder="₱<?= $has_updated_balance ? number_format($data['updated_balance'], 2) : '0.00'; ?>"
                                                    required
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;"
                                                    value="<?= $has_updated_balance ? number_format($data['updated_balance'], 2) : ''; ?>"
                                                    <?= ($existing_loan_records || $data['updated_balance'] > 0) ? 'readonly' : ''; ?>>
                                            </div>


                                            <div class="col-md-6 mt-3">
                                                <label for="existing_balance" class="form-label">Existing Balance</label>
                                                <input type="text" id="existing_balance" name="existing_balance" class="form-control" style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;"
                                                    value="<?php echo isset($data['updated_balance']) && $data['updated_balance'] !== null ? number_format($data['updated_balance'], 2) : '0.00'; ?>" readonly>

                                            </div>


                                        </div>

                                        <!-- Balance and Comakers -->
                                        <div class="row mt-3" style="padding: 10px 0;">
                                            <div class="col-md-6 mt-3" style="position: relative;">
                                                <label for="comaker_1" class="form-label">Comaker 1</label>
                                                <input type="text" class="form-control" id="comaker_1" name="comaker_1" required
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;"
                                                    onkeyup="searchMember(this.value, 'memberDropdown1', 'comaker_1', 'comaker_1_hidden')"
                                                    autocomplete="off">
                                                <ul id="memberDropdown1" class="dropdown-menu"
                                                    style="display: none; position: absolute; width: 100%;"></ul>
                                                <input type="hidden" id="comaker_1_hidden" name="comaker_1_hidden" required>
                                            </div>

                                            <div class="col-md-6 mt-3" style="position: relative;">
                                                <label for="comaker_2" class="form-label">Comaker 2</label>
                                                <input type="text" class="form-control" id="comaker_2" name="comaker_2" required
                                                    style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;"
                                                    onkeyup="searchMember(this.value, 'memberDropdown2', 'comaker_2', 'comaker_2_hidden')"
                                                    autocomplete="off">
                                                <ul id="memberDropdown2" class="dropdown-menu"
                                                    style="display: none; position: absolute; width: 100%;"></ul>
                                                <input type="hidden" id="comaker_2_hidden" name="comaker_2_hidden" required>
                                            </div>
                                        </div>

                                        <!-- Modal Footer -->
                                        <div class="mt-4">
                                            <button type="submit" id="addBalanceButton-ID" name="addBalanceButton" class="btn"
                                                style="background-color: #2e7d32; border: none; color: white; width: 100%; padding: 12px;">Submit</button>
                                        </div>
                                    </form>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
const loanDataExists = <?php echo json_encode($loan_data_exists); ?>;
const loanData = <?php echo json_encode($loan_data); ?>;

console.log('Loan Data Exists:', loanDataExists);
console.log('Loan Data:', loanData);

document.addEventListener('DOMContentLoaded', function () {
    const shareCapitalField = document.getElementById('share_capital');
    const capitalBuildUpField = document.getElementById('capitalBuildUp');

    if (loanDataExists && loanData) {
        // Loan data exists - make share_capital read-only and populate fields
        const formattedCapital = `₱${parseFloat(loanData.capitalBuildUp).toFixed(2)}`;
        shareCapitalField.readOnly = true;
        shareCapitalField.value = formattedCapital;
        capitalBuildUpField.value = formattedCapital;
    } else {
        // No loan data - allow input
        shareCapitalField.readOnly = false;
        shareCapitalField.value = ''; // Clear for user input
        capitalBuildUpField.value = ''; // Clear for user input
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Add listeners to trigger recalculations
    const shareCapitalField = document.getElementById('share_capital');
    const principalAmountField = document.getElementById('principal_amount');
    const loanDurationField = document.getElementById('loan_duration');

    ['input', 'change'].forEach(eventType => {
        shareCapitalField.addEventListener(eventType, calculateLoanableAmount);
        principalAmountField.addEventListener(eventType, calculateLoanableAmount);
        loanDurationField.addEventListener(eventType, calculateLoanableAmount);
    });

    // Initial calculation on page load
    calculateLoanableAmount();
});

function calculateLoanableAmount() {
    const shareCapitalField = document.getElementById('share_capital');
    const principalAmountField = document.getElementById('principal_amount');
    const loanDurationField = document.getElementById('loan_duration');
    const existingBalanceField = document.getElementById('existing_balance');
    const processingFeeField = document.getElementById('processing_fee');
    const totalAmountField = document.getElementById('totalAmount');
    const capitalBuildUpField = document.getElementById('capitalBuildUp');
    const interestField = document.getElementById('interest');

    // Get and sanitize values
    let shareCapital = parseFloat(shareCapitalField.value.replace(/[^0-9.-]+/g, '')) || 0;
    let principalAmount = parseFloat(principalAmountField.value.replace(/[^0-9.-]+/g, '')) || 0;
    let existingBalance = parseFloat(existingBalanceField.value.replace(/[^0-9.-]+/g, '')) || 0;
    let loanDuration = loanDurationField.value;

    console.log('Share Capital:', shareCapital);
    console.log('Principal Amount:', principalAmount);

    // Calculate loanable amount
    let loanableAmount = shareCapital * 3;
    loanableAmount = Math.min(loanableAmount, 100000);
    document.getElementById('loanable_amount').value = `₱${loanableAmount.toLocaleString()}`;

    // Calculate processing fee
    let processingFee = calculateProcessingFee(principalAmount);
    processingFeeField.value = `₱${processingFee.toLocaleString()}`;

    // Calculate interest
    let interest = updateInterest(principalAmount, loanDuration);
    interestField.value = `₱${interest.toLocaleString()}`;

    // Calculate capital build-up and net proceeds
    let capitalBuildUp = (principalAmount - existingBalance - interest - processingFee) * 0.05;
    capitalBuildUpField.value = `₱${Math.max(capitalBuildUp, 0).toLocaleString()}`;

    let totalAmount = principalAmount - existingBalance - interest - processingFee - capitalBuildUp;
    totalAmountField.value = `₱${Math.max(totalAmount, 0).toLocaleString()}`;

    console.log('Processing Fee:', processingFee);
    console.log('Capital Build-Up:', capitalBuildUp);
    console.log('Total Amount:', totalAmount);
}

function calculateProcessingFee(principalAmount) {
    const feeRanges = [
        { min: 5000, max: 25000, fee: 200 },
        { min: 26000, max: 50000, fee: 300 },
        { min: 51000, max: 75000, fee: 400 },
        { min: 76000, max: 100000, fee: 500 }
    ];

    for (const range of feeRanges) {
        if (principalAmount >= range.min && principalAmount <= range.max) {
            return range.fee;
        }
    }
    return 0; // Default fee if not in range
}

function updateInterest(principalAmount, loanDuration) {
    const interestRates = {
                                '5 Months': {
                                    5000: 225.00,
                                    10000: 450.00,
                                    15000: 675.00,
                                    20000: 900.00,
                                    25000: 1125.00,
                                    30000: 1350.00,
                                    35000: 1575.00,
                                    40000: 1800.00,
                                    45000: 2025.00,
                                    50000: 2250.00,
                                    55000: 2475.00,
                                    60000: 2700.00,
                                    65000: 2925.00,
                                    70000: 3150.00,
                                    75000: 3375.00,
                                    80000: 3600.00,
                                    85000: 3825.00,
                                    90000: 4050.00,
                                    95000: 4275.00,
                                    100000: 4500.00
                                },
                                '10 Months': {
                                    5000: 413.00,
                                    10000: 825.00,
                                    15000: 1238.00,
                                    20000: 1650.00,
                                    25000: 2063.00,
                                    30000: 2475.00,
                                    35000: 2888.00,
                                    40000: 3300.00,
                                    45000: 3713.00,
                                    50000: 4125.00,
                                    55000: 4538.00,
                                    60000: 4950.00,
                                    65000: 5363.00,
                                    70000: 5775.00,
                                    75000: 6188.00,
                                    80000: 6600.00,
                                    85000: 7013.00,
                                    90000: 7425.00,
                                    95000: 7838.00,
                                    100000: 8250.00
                                },
                                '15 Months': {
                                    5000: 600.00,
                                    10000: 1200.00,
                                    15000: 1800.00,
                                    20000: 2400.00,
                                    25000: 3000.00,
                                    30000: 3600.00,
                                    35000: 4200.00,
                                    40000: 4800.00,
                                    45000: 5400.00,
                                    50000: 6000.00,
                                    55000: 6600.00,
                                    60000: 7200.00,
                                    65000: 7800.00,
                                    70000: 7800.00,
                                    75000: 9000.00,
                                    80000: 9600.00,
                                    85000: 10200.00,
                                    90000: 10800.00,
                                    95000: 11400.00,
                                    100000: 12000.00
                                },
                                '20 Months': {
                                    5000: 788.00,
                                    10000: 1575.00,
                                    15000: 2363.00,
                                    20000: 3150.00,
                                    25000: 3938.00,
                                    30000: 4725.00,
                                    35000: 5513.00,
                                    40000: 6300.00,
                                    45000: 7088.00,
                                    50000: 7875.00,
                                    55000: 8663.00,
                                    60000: 9450.00,
                                    65000: 10238.00,
                                    70000: 11025.00,
                                    75000: 11813.00,
                                    80000: 12600.00,
                                    85000: 13388.00,
                                    90000: 14175.00,
                                    95000: 14963.00,
                                    100000: 15750.00
                                }
                            };

    const interest = (interestRates[loanDuration] || {})[principalAmount] || 0;
    return interest;
}






          
                    let validNames = {}; // Store valid names for each input
                    let isDropdownClick = false; // Flag to track dropdown interaction

                    function searchMember(query, dropdownId, inputId, hiddenId) {
                        const dropdown = document.getElementById(dropdownId);
                        if (query.length < 2) {
                            dropdown.style.display = 'none';
                            return;
                        }

                        fetch(`../function/search_member.php?query=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                dropdown.innerHTML = '';
                                validNames[inputId] = []; // Reset valid names for the specific input

                                if (data.length > 0) {
                                    data.forEach(member => {
                                        validNames[inputId].push(member.full_name); // Store valid names
                                        const item = document.createElement('li');
                                        item.textContent = member.full_name; // Display the member's name
                                        item.style.cursor = 'pointer';
                                        item.addEventListener('mousedown', () => {
                                            // Handle dropdown click without triggering blur
                                            isDropdownClick = true;
                                            document.getElementById(inputId).value = member.full_name;
                                            document.getElementById(hiddenId).value = member.full_name;
                                            dropdown.style.display = 'none';
                                            checkDuplicateNames(inputId, member.full_name);
                                        });
                                        dropdown.appendChild(item);
                                    });
                                    dropdown.style.display = 'block';
                                } else {
                                    dropdown.style.display = 'none';
                                }
                            })
                            .catch(error => console.error('Error fetching members:', error));
                    }

                    function validateInput(inputId, hiddenId) {
                        if (isDropdownClick) {
                            isDropdownClick = false; // Reset the flag
                            return;
                        }

                        const inputField = document.getElementById(inputId);
                        const hiddenField = document.getElementById(hiddenId);

                        if (!validNames[inputId] || !validNames[inputId].includes(inputField.value)) {
                            inputField.value = ''; // Clear the field if invalid
                            hiddenField.value = ''; // Clear the hidden field
                            alert('Please select a valid name from the dropdown.');
                        } else {
                            hiddenField.value = inputField.value; // Set hidden field if valid
                            checkDuplicateNames(inputId, inputField.value);
                        }
                    }

                    function checkDuplicateNames(currentInputId, selectedName) {
                        const otherInputId = currentInputId === 'comaker_1' ? 'comaker_2' : 'comaker_1';
                        const otherInputValue = document.getElementById(otherInputId).value;

                        if (selectedName === otherInputValue) {
                            alert('You cannot select the same name for both Comaker 1 and Comaker 2.');
                            document.getElementById(currentInputId).value = '';
                            document.getElementById(`${currentInputId}_hidden`).value = '';
                        }
                    }

                    // Hide dropdown when input is cleared
                    function handleClearInput(inputId) {
                        const inputField = document.getElementById(inputId);
                        if (inputField.value === '') {
                            document.getElementById('memberDropdown').style.display = 'none';
                        }
                    }

                    // Add event listeners to hide dropdown if focus moves away
                    document.getElementById('comaker_1').addEventListener('blur', () => {
                        validateInput('comaker_1', 'comaker_1_hidden');
                        document.getElementById('memberDropdown').style.display = 'none'; // Hide the dropdown on blur
                    });

                    document.getElementById('comaker_2').addEventListener('blur', () => {
                        validateInput('comaker_2', 'comaker_2_hidden');
                        document.getElementById('memberDropdown').style.display = 'none'; // Hide the dropdown on blur
                    });

                    // Hide dropdown when clicking on the other input field
                    document.getElementById('comaker_1').addEventListener('focus', () => {
                        // Hide the dropdown if it's open, and allow typing
                        document.getElementById('memberDropdown').style.display = 'none';
                    });

                    document.getElementById('comaker_2').addEventListener('focus', () => {
                        // Hide the dropdown if it's open, and allow typing
                        document.getElementById('memberDropdown').style.display = 'none';
                    });

                    // Track input clearing and hide dropdown
                    document.getElementById('comaker_1').addEventListener('input', () => handleClearInput('comaker_1'));
                    document.getElementById('comaker_2').addEventListener('input', () => handleClearInput('comaker_2'));

                    // Reset dropdown when switching focus between inputs
                    document.getElementById('comaker_2').addEventListener('focus', () => {
                        // Hide the dropdown of comaker_1 if it is open
                        document.getElementById('memberDropdown').style.display = 'none';
                    });
                </script>









         

                <!-- This is for the modal of edit Info of the members -->


                <!-- Modal for Edit Member Information -->
                <div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #2e7d32; color: white;">
                                <h5 class="modal-title" id="editMemberModalLabel">Edit Member Information</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php
                                require("../conn/dbcon.php");

                                if (isset($_GET['id'])) {
                                    $id = mysqli_real_escape_string($conn, $_GET['id']);

                                    $query = "SELECT * FROM member WHERE member_id='$id'";

                                    $result_query = $conn->query($query);

                                    if ($result_query && mysqli_num_rows($result_query) > 0) {
                                        $data = mysqli_fetch_array($result_query);
                                ?>
                                        <form action="../function/editMembersFunction.php" method="POST">
                                            <input type="hidden" name="member_id" value="<?= $data['member_id']; ?>">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="fname" class="form-label">First Name</label>
                                                    <input type="text" class="form-control" id="fname" name="fname"
                                                        value="<?= $data['fname']; ?>" required
                                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="mname" class="form-label">Middle Name</label>
                                                    <input type="text" class="form-control" id="mname" name="mname"
                                                        value="<?= $data['mname']; ?>" required
                                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="lname" class="form-label">Last Name</label>
                                                    <input type="text" class="form-control" id="lname" name="lname"
                                                        value="<?= $data['lname']; ?>" required
                                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="membersDate" class="form-label">Membership Date</label>
                                                    <input type="date" class="form-control" id="membersDate" name="membersDate"
                                                        value="<?= $data['membership_date']; ?>"
                                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="tinNumber" class="form-label">Tin Number</label>
                                                    <input type="text" class="form-control" id="tinNumber" name="tinNumber"
                                                        value="<?= $data['tin_number']; ?>" required
                                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="contactNumber" class="form-label">Contact Number</label>
                                                    <input type="text" class="form-control" id="contactNumber" name="contactNumber"
                                                        value="<?= $data['contact_number']; ?>" required
                                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                </div>

                                            </div>
                                            <button type="submit" name="updateButton" class="btn"
                                                style="background-color: #2e7d32; border: none; color: white; width: 100%; padding: 12px; margin-top: 20px;">
                                                Submit
                                            </button>
                                        </form>
                                <?php
                                    } else {
                                        echo "No data found.";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal fade" id="viewMemberModal" tabindex="-1" aria-labelledby="viewMemberModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- Use modal-lg for larger modal width -->
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white"> <!-- Add background color for the header -->
                                <h5 class="modal-title" id="viewMemberModalLabel">Member Information</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php
                                require("../conn/dbcon.php");

                                if (isset($_GET['id'])) {
                                    $id = mysqli_real_escape_string($conn, $_GET['id']);

                                    // Query to fetch data
                                    $query = "SELECT m.member_id, m.fname, m.mname, m.lname, m.membership_date, m.tin_number, m.username, m.email, m.contact_number, m.pictures, 
                                i.institute_name, l.loan_term, l.minus_wage, l.loan_duration, l.share_capital, l.loanable_amount, l.principal_amount, 
                                l.updated_balance, c.comaker_1, c.comaker_2
                              FROM member m
                              INNER JOIN member_institute mi ON m.member_id = mi.member_id
                              INNER JOIN institute i ON mi.institute_id = i.institute_id
                              LEFT JOIN loan l ON m.member_id = l.member_id
                              LEFT JOIN comaker_name c ON l.loan_id = c.loan_id
                              WHERE m.member_id = '$id'";

                                    $result_query = $conn->query($query);

                                    if ($result_query && mysqli_num_rows($result_query) > 0) {
                                        $data = mysqli_fetch_array($result_query);
                                ?>

                                        <!-- Member details -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <p><strong>First Name:</strong> <?= $data['fname']; ?></p>
                                                <p><strong>Middle Name:</strong> <?= $data['mname']; ?></p>
                                                <p><strong>Last Name:</strong> <?= $data['lname']; ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Contact Number:</strong> <?= $data['contact_number']; ?></p>
                                                <p><strong>Email Address:</strong> <?= $data['email']; ?></p>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <p><strong>Username:</strong> <?= $data['username']; ?></p>
                                                <p><strong>Institute:</strong> <?= $data['institute_name']; ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Comaker 1:</strong> <?= $data['comaker_1']; ?></p>
                                                <p><strong>Comaker 2:</strong> <?= $data['comaker_2']; ?></p>
                                            </div>
                                        </div>



                                <?php
                                    } else {
                                        echo "<p class='text-center text-muted'>No data found.</p>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            </div>
            </div>
            <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
            <script src="../assets/script.js"></script>
            <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const balanceField = document.getElementById('updated_balance');

                        // Check if the field is not read-only before adding the event listener
                        if (!balanceField.hasAttribute('readonly')) {
                            balanceField.addEventListener('input', function() {
                                // Allow only digits and a single decimal point
                                this.value = this.value.replace(/[^0-9.]/g, '');

                                // Prevent multiple decimal points
                                if ((this.value.match(/\./g) || []).length > 1) {
                                    this.value = this.value.substring(0, this.value.lastIndexOf('.'));
                                }
                            });
                        }
                    });


                    document.addEventListener('DOMContentLoaded', function() {
                        const principalAmountSelect = document.getElementById('principal_amount');
                        const updatedBalanceField = document.getElementById('updated_balance');

                        // Add event listener to the dropdown
                        principalAmountSelect.addEventListener('change', function() {
                            // Update the input field with the selected value
                            updatedBalanceField.value = this.value;
                        });
                    });
                </script>
            <script>
                
                document.addEventListener('DOMContentLoaded', () => {
                    // Get the button and input values
                    const addBalanceButton = document.getElementById('addBalance');
                    const principalAmount = parseFloat(document.getElementById('principalAmount').value);
                    const updatedBalance = parseFloat(document.getElementById('updatedBalance').value);

                    // Perform the condition check
                    if (updatedBalance <= (principalAmount * 0.5)) {
                        addBalanceButton.disabled = false; // Enable the button if condition is met
                    } else {
                        addBalanceButton.disabled = true; // Keep the button disabled if condition is not met
                    }
                });
            </script>


            <!-- for submit button preventing multiple entry bug -->
            <script>
                document.getElementById("addBalanceButton-ID").addEventListener("click", function() {
                    const addButton = this;

                    // Disable the button to prevent multiple clicks
                    if (!addButton.disabled) {

                        addButton.textContent = "Submitting Loan..."; // Change button text

                        // Ensure the button stays disabled even during form submission
                        setTimeout(() => {
                            addButton.style.pointerEvents = "none"; // Prevent any further interaction
                        }, 10);
                    }
                });
            </script>




</body>

</html>