<?php
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['email']) || !isset($_SESSION['username']) || $_SESSION['role_id'] != 1) {
    // If not logged in or not an admin, redirect to the login page
    header("Location: user_dashboard.php");
    exit();
}

require '../conn/dbcon.php';
// Query to count reports with report_status = 1 (unarchived reports)
$newReportsQuery = "SELECT COUNT(*) AS new_report_count FROM reports WHERE report_status = 1";
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
            FROM members m
            INNER JOIN member_institutes mi ON m.member_id = mi.member_id
            INNER JOIN institutes i ON mi.institute_id = i.institute_id
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
            } else{
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
                                $query_admin = "SELECT * FROM members WHERE member_id = '$member_id'";

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
                                                <?= $admin_info['mname'] ?? ''; ?>         <?= $admin_info['lname'] ?? ''; ?>
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
                                                $loan_status == 'INACTIVE' ? 'red' :
                                                ($loan_status == 'ACTIVE' ? 'green' :
                                                    ($loan_status == 'No Loan Found' ? 'orange' : 'gray'));
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

                                            <button id="addBalance" name="addBalance" class="btn btn-primary mt-2"
                                                data-bs-toggle="modal" data-bs-target="#addLoanModal">Loan Application</button>
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
                        if (isset($_GET['id'])) {
                            $id = mysqli_real_escape_string($conn, $_GET['id']);
                            $query = "SELECT m.member_id, l.updated_balance, l.share_capital, l.loan_duration, l.loanable_amount
                              FROM members m
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
                                <!-- Hidden field for processing fee -->

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

                                <!-- Share Capital and Loanable Amount -->
                                <div class="row mt-3" style="padding: 10px 0;">
                                    <div class="col-md-6">
                                        <label for="share_capital" class="form-label">Share Capital</label>
                                        <input type="text" class="form-control" id="share_capital" name="share_capital"
                                            placeholder="₱<?= number_format($data['share_capital'], 2); ?>" required
                                            oninput="calculateLoanableAmount()"
                                            style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="loanable_amount" class="form-label">Loanable Amount</label>
                                        <input type="text" class="form-control" id="loanable_amount" name="loanable_amount"
                                            value="₱<?= number_format($data['loanable_amount'], 2); ?>" required readonly
                                            style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                    </div>
                                </div>

                                <!-- Principal Amount, Processing Fee, and Interest -->
                                <div class="row mt-3" style="padding: 10px 0;">
                                    <div class="col-md-6">
                                        <label for="principal_amount" class="form-label">Principal Amount</label>
                                        <input type="text" class="form-control" id="principal_amount"
                                            name="principal_amount" placeholder="Enter Principal Amount" required
                                            oninput="calculateLoanableAmount()"
                                            style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
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
                                            placeholder="Enter interest" required
                                            style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                    </div>
                                    <div class="col-md-6 mt-3">
                                        <label for="net_proceeds" class="form-label">Net Proceeds</label>
                                        <input type="text" class="form-control" id="net_proceeds" name="net_proceeds"
                                            placeholder=" Enter Net Proceeds" required
                                            style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                    </div>
                                    <div class="col-md-6 mt-3">
                                        <label for="updated_balance" class="form-label">Balance</label>
                                        <input type="text" class="form-control" id="updated_balance" name="updated_balance"
                                            placeholder="₱<?= number_format($data['updated_balance'], 2); ?>" required
                                            style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                    </div>
                                </div>

                                <!-- Balance and Comakers -->
                                <div class="row mt-3" style="padding: 10px 0;">

                                    <div class="col-md-6 mt-3">
                                        <label for="comaker_1" class="form-label">Comaker 1</label>
                                        <input type="text" class="form-control" id="comaker_1" name="comaker_1" required
                                            style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">

                                    </div>
                                    <div class="col-md-6 mt-3">
                                        <label for="comaker_2" class="form-label">Comaker 2</label>
                                        <input type="text" class="form-control" id="comaker_2" name="comaker_2" required
                                            style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="mt-4">
                                    <button type="submit" id="addBalanceButton-ID" name="addBalanceButton" class="btn"
                                        style="background-color: #2e7d32; border: none; color: white; width: 100%; padding: 12px;">Submit</button>
                                </div>

                                <script>
                                    function calculateLoanableAmount() {
                                        var shareCapital = parseFloat(document.getElementById("share_capital").value.replace(/[^0-9.-]+/g, "")) || 0;
                                        var loanableAmount = shareCapital * 3;

                                        if (loanableAmount > 100000) {
                                            loanableAmount = 100000;
                                        }

                                        document.getElementById("loanable_amount").value = "₱" + loanableAmount.toLocaleString();

                                        var principalAmount = parseFloat(document.getElementById("principal_amount").value.replace(/[^0-9.-]+/g, "")) || 0;

                                        var processingFee = 0;
                                        var feeRanges = [{
                                            min: 5000,
                                            max: 25000,
                                            fee: 200
                                        },
                                        {
                                            min: 26000,
                                            max: 50000,
                                            fee: 300
                                        },
                                        {
                                            min: 51000,
                                            max: 75000,
                                            fee: 400
                                        },
                                        {
                                            min: 76000,
                                            max: 100000,
                                            fee: 500
                                        }
                                        ];

                                        for (var i = 0; i < feeRanges.length; i++) {
                                            var range = feeRanges[i];
                                            if (principalAmount >= range.min && principalAmount <= range.max) {
                                                processingFee = range.fee;
                                                break;
                                            }
                                        }

                                        // Set both visible and hidden fields with processing fee
                                        document.getElementById("processing_fee").value = "₱" + processingFee.toLocaleString();
                                        document.getElementById("processing_fee_hidden").value = processingFee;
                                    }
                                </script>



                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>





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

                            $query = "SELECT * FROM members WHERE member_id='$id'";

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
                              FROM members m
                              INNER JOIN member_institutes mi ON m.member_id = mi.member_id
                              INNER JOIN institutes i ON mi.institute_id = i.institute_id
                              LEFT JOIN loan l ON m.member_id = l.member_id
                              LEFT JOIN comakers_name c ON l.loan_id = c.loan_id
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


    <!-- for submit button preventing multiple entry bug -->
    <script>
        document.getElementById("addBalanceButton-ID").addEventListener("click", function () {
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



    <script>
        // Search Function
        function searchTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.querySelector(".table"); // Assign the value of the table element directly
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[3]; // Change this index to the appropriate column index for updated_balance_history
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>

</html>