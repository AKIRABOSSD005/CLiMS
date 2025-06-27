<?php
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['email']) || !isset($_SESSION['username']) || $_SESSION['role_id'] != 2) {
    // If not logged in or not an admin, redirect to the login page
    header("Location: admin_dashboard.php");
    exit();
}

include '../function/encryption.php'; // Include the encryption functions
require '../conn/dbcon.php'; // Ensure your database connection file is included
require '../function/user_dashboardFunction.php'; // Include any additional functions as needed

// Check if member_id is set in the URL
if (!isset($_GET['member_id'])) {
    header("Location: ../index.php");
    exit();
}

// Sanitize and decode member_id from the URL
$encrypted_member_id = urldecode($_GET['member_id']); // Decode it first
$member_id = decryptData($encrypted_member_id, ENCRYPTION_KEY); // Decrypt the member ID

if ($member_id === false) {
    echo "Invalid member ID. Decryption failed.";
    exit();
}

// Fetch member information based on member ID
$query = "SELECT * FROM member WHERE member_id = '" . mysqli_real_escape_string($conn, $member_id) . "'";
$result_query = $conn->query($query);

// Check if query execution was successful
if (!$result_query) {
    echo "Error: " . $conn->error;
    exit();
}

// Check if a member is found
if ($result_query->num_rows == 0) {
    echo "No member found with ID: " . htmlspecialchars($member_id);
    exit();
}

$query_loan = "SELECT * FROM loan WHERE member_id = '$member_id'";
$result_loan = mysqli_query($conn, $query_loan);


// Check if any member was found
if ($result_query->num_rows > 0) {
    // Fetch member data
    while ($member_info = $result_query->fetch_assoc()) {

        $loan_query = "SELECT loan_status FROM loan WHERE member_id = '$member_id' ORDER BY loan_id DESC LIMIT 1";
        $loan_result = $conn->query($loan_query);

        if ($loan_result && $loan_result->num_rows > 0) {
            $loan_row = $loan_result->fetch_assoc();
            $loan_status = $loan_row['loan_status'];

            // Check if the loan status is '1' for ACTIVE
            if ($loan_status == '1') {
                $loan_status = 'ACTIVE';
            } else {
                $loan_status = 'INACTIVE';
            }
        } else {
            $loan_status = "No Loan Found";
        }
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>BASCPCC</title>
            <link rel="stylesheet" href="../assets/bootstrap/font/lineicons.css">
            <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
            <link rel="stylesheet" href="../assets/style.css">

            <link rel="icon" href="../assets/pictures/cooplogo.jpg" type="image/x-icon"> <!-- Adjust the path accordingly -->
            <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"> <!-- Adjust the path accordingly -->
            <link rel="icon" type="image/png" sizes="32x32" href="../assets/pictures/cooplogo.jpg">
            <!-- Adjust the path accordingly -->
            <link rel="icon" type="image/png" sizes="16x16" href="../assets/pictures/cooplogo.jpg">
        </head>

        <body>
            <?php
            if (isset($_GET['loan_status'])) {
                $loan_status = mysqli_real_escape_string($conn, $_GET['loan_status']);


                $result_query = $conn->query($query);


                if ($result_query && mysqli_num_rows($result_query) > 0) {
                    $data = mysqli_fetch_array($result_query);

                    // Determine loan status
                    $loan_status = $data['loan_status'] == 0 ? "INACTIVE" : "ACTIVE";

                    //  echo "Profile Picture Path: " . $data['pictures'];
                }
            }
            ?>
            <div class="wrapper">
                <aside id="sidebar">
                    <div class="d-flex">
                        <button class="toggle-btn" type="button" title="COOP">
                            <i class="lni lni-grid-alt"></i>
                        </button>
                        <div class="sidebar-logo">
                            <a href="#">BASCPCC</a>
                        </div>
                    </div>
                    <ul class="sidebar-nav">
                        <li class="sidebar-item">
                            <?php $encryptedMemberId = encryptData($member_info['member_id'], ENCRYPTION_KEY); ?>
                            <a href="user_dashboard.php?member_id=<?php echo $encryptedMemberId; ?>" class="sidebar-link"
                                title="Dashboard" onclick="window.location.reload();" class="sidebar-link" title="Dashboard">
                                <i class="lni lni-bar-chart"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="sidebar-item" data-bs-toggle="modal" data-bs-target="#loanModal">
                            <a href="#" class="sidebar-link">
                                <i class="lni lni-calculator"></i>
                                <span>Calculator</span>
                            </a>
                        </li>

                        <li class="sidebar-item" data-bs-toggle="modal" data-bs-target="#sendReportModal">
                            <a href="#" class="sidebar-link">
                                <i class="lni lni-postcard"></i>
                                <span>Send Report</span>
                            </a>
                        </li>

                        <li class="sidebar-item dropdown">
                            <a class="sidebar-link dropdown-toggle" id="downloadDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">

                                <span>Download Form</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="downloadDropdown">

                                <li>
                                    <?php
                                    // Encrypt the member_id
                                    $encryptedMemberId = encryptData($member_info['member_id'], ENCRYPTION_KEY);
                                    ?>
                                    <!-- Store the encrypted Member ID in a hidden input -->
                                    <input type="hidden" id="member_id" name="member_id" value="<?php echo htmlspecialchars($encryptedMemberId); ?>">

                                    <!-- Use the encrypted member_id in the download link -->
                                    <a class="dropdown-item" href="../../function/download_fileFunction.php?id=<?php echo urlencode($encryptedMemberId); ?>">
                                        <i class="lni lni-download"></i> Application Form
                                    </a>
                                </li>

                                <li>
                                    <!-- Dropdown item link -->
                                    <a href="viewPdf.php?filename=BY-LAWS-OF-BASPCC.pdf" class="dropdown-item"
                                        target="_blank"><i class="lni lni-eye"></i>Terms of Policy</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </aside>

                <!-- responsive of the name in the navbar -->
                <style>
                    .name {
                        font-size: 1em;
                        /* Default font size */
                    }

                    /* Example media queries for different screen sizes */
                    @media (max-width: 600px) {
                        .name {
                            font-size: 0.8em;
                            /* Smaller font size for short names */
                        }
                    }

                    @media (max-width: 400px) {
                        .name {
                            font-size: 0.5em;
                            /* Even smaller font size for longer names */
                        }
                    }
                </style>

                <div class="main">
                    <nav class="navbar navbar-expand px-3 py-1 custom-navbar">
                        <a class="navbar-brand d-flex align-items-center" href="#">
                            <img src="../assets/pictures/basclogo.png" alt="Logo" width="50" height="50" class="me-2">
                            <!-- Update path to your logo -->
                            <span class="d-none d-md-inline text-white"
                                style="font-family: 'Times New Roman', Times, serif;">BULACAN AGRICULTURAL STATE
                                COLLEGE</span> <!-- Hide text on mobile -->
                        </a>
                        <div class="navbar-collapse collapse">
                            <ul class="navbar-nav ms-auto">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" class="d-flex align-items-center">
                                        <span class="name text-dark" style="font-weight: normal;">
                                            <?= $member_info['fname'] ?? ''; ?>
                                            <?= $member_info['mname'] ?? ''; ?>
                                            <?= $member_info['lname'] ?? ''; ?>
                                        </span>
                                        <img src="<?= !empty($member_info['pictures']) ? '../assets/pictures/memberPictures/' . $member_info['pictures'] : '../assets/pictures/account.png'; ?>"
                                            class="avatar img-fluid rounded-circle ms-2" alt="">
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                data-bs-target="#profileModal">Profile</a></li>
                                        <li><a class="dropdown-item" href="#passwordAccountModal" data-bs-toggle="modal">Change
                                                Password</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="../function/logoutFunction.php">Logout</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </nav>
                    <!-- Wrapper -->
                    <style>
                        /* Custom CSS to remove border from container, row, and column */
                        .custom-container,
                        .custom-row,
                        .custom-col {
                            border: none;
                        }
                    </style>
                    <div id="content-wrapper" class="d-flex flex-column">
                        <div class="container">
                            <?php
                            $stmt = $conn->prepare("
                            SELECT institute.institute_name
                            FROM member_institute
                            JOIN institute ON member_institute.institute_id = institute.institute_id
                            WHERE member_institute.member_id = ?");
                            $stmt->bind_param("s", $member_id); // Assuming member_id is a string, change "s" to "i" if it's an integer
                            $stmt->execute();
                            $institute_result = $stmt->get_result();

                            if ($institute_result) {
                                if ($institute_result->num_rows > 0) {
                                    $institute_info = $institute_result->fetch_assoc();

                                    if (isset($institute_info['institute_name'])) {
                                        "Institute Name: " . htmlspecialchars($institute_info['institute_name']);
                                    } else {
                                        echo "No institute name found.";
                                    }
                                } else {
                                    echo "No institute information found for this member.";
                                }
                            } else {
                                echo "Query Error: " . $stmt->error; // Handle any errors
                            }

                            $stmt->close();
                            ?>
                            <div class="row" style="border: none !important;">
                                <div class="col-md-4">
                                    <!-- Profile Info Card -->
                                    <div class="card mb-1"> <!-- Adjust the height as needed -->
                                        <div class="card-body text-center">
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#changeProfilePictureModal">
                                                <img src="<?= !empty($member_info['pictures']) ? '../assets/pictures/memberPictures/' . $member_info['pictures'] : '../assets/pictures/account.png'; ?>"
                                                    class="avatar img-fluid rounded-circle" alt=""
                                                    style="width: 200px; height: 200px; object-fit: cover;">
                                            </a>
                                            <br> <br>
                                            <p class="card-text"
                                                style="background-color: <?= $loan_status == 'INACTIVE' ? 'red' : ($loan_status == 'ACTIVE' ? 'green' : 'orange'); ?>; color: white; padding: 5px; border-radius: 20px;">
                                                Loan Status: <?= $loan_status; ?>
                                            </p>
                                            <p class="card-text">First Name: <?= $member_info['fname']; ?></p>
                                            <p class="card-text">Middle Name: <?= $member_info['mname']; ?></p>
                                            <p class="card-text">Last Name: <?= $member_info['lname']; ?></p>

                                            <p class="card-text">Institute: <?= $institute_info['institute_name']; ?></p>
                                            <a href="#tableReload" onclick="reloadTable(); return false;" class="text-dark"><i
                                                    class="lni lni-eye"></i> Overview</a>

                                        </div>
                                        <div class="infoUpdateSettings text-center mb-1 mt-0"> <!-- Center the links -->

                                        </div>
                                    </div>
                                </div>
                                <style>
                                    .custom-row-Cards {
                                        border: none;
                                    }

                                    .card {
                                        height: 100%;
                                        width: auto;
                                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                                        /* Shadow for the card */
                                        transition: all 0.3s ease;
                                        /* Smooth transition */
                                    }

                                    .card:hover {
                                        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
                                        /* Hover shadow effect */
                                        transform: translateY(-5px);
                                        /* Lift on hover */
                                    }

                                    .card-body {
                                        display: flex;
                                        flex-direction: column;
                                        justify-content: center;
                                        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
                                        /* Shadow for the card body */
                                        transition: all 0.3s ease;
                                        /* Smooth transition */
                                    }

                                    .card-body:hover {
                                        box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.2);
                                        /* Stronger shadow on hover */
                                    }
                                </style>

                                <?php
                                $query_loan = "SELECT * FROM loan WHERE member_id = '$member_id'";
                                $result_loan = mysqli_query($conn, $query_loan);

                                if (mysqli_num_rows($result_loan) > 0) {
                                    // Assuming there's only one loan record for the member
                                    $loan_info = mysqli_fetch_assoc($result_loan);
                                }

                                ?>
                                <div class="col-md-8">

                                    <div class="row custom-row-Cards">
                                        <div class="col-sm-12 col-md-3 mb-2">
                                            <!-- Membership Date Card -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="font-weight: bold;">Membership Date</h6>
                                                    <h5 class="text-center">
                                                        <?= date('F j, Y', strtotime($member_info['membership_date'])); ?>
                                                    </h5>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-3 mb-2">
                                            <!-- Capital Card -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="font-weight: bold;">Share Capital</h6>
                                                    <h5 class="text-center">₱
                                                        <?= isset($loan_info['share_capital']) ? number_format($loan_info['share_capital'], 2) : '0.00'; ?>
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
                                                        <?= isset($loan_info['principal_amount']) ? number_format($loan_info['principal_amount'], 2) : '0.00'; ?>
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
                                                        <?= isset($loan_info['loanable_amount']) ? number_format($loan_info['loanable_amount'], 2) : '0.00'; ?>
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
                                                        if (!empty($loan_info['loan_term'])) {
                                                            echo date('F j, Y', strtotime($loan_info['loan_term']));
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
                                                            if (!empty($loan_info['loan_term']) && !empty($loan_info['loan_duration'])) {
                                                                // Function to extract numeric part from the string
                                                                function extractNumericPart($string)
                                                                {
                                                                    // Extract numeric part using regular expression
                                                                    preg_match('/(\d+)/', $string, $matches);
                                                                    // If numeric part found, return it, else return 0
                                                                    return isset($matches[0]) ? intval($matches[0]) : 0;
                                                                }
                                                                // Convert loan duration to months
                                                                if (is_numeric($loan_info['loan_duration'])) {
                                                                    $loan_duration_months = intval($loan_info['loan_duration']);
                                                                } else {
                                                                    $loan_duration_months = extractNumericPart($loan_info['loan_duration']);
                                                                }
                                                                // Calculate loan end date
                                                                $loan_end_timestamp = strtotime("+" . $loan_duration_months . " months", strtotime($loan_info['loan_term']));
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
                                                    <h6 class="card-title" style="font-weight: bold;">Existing Balance</h6>
                                                    <h5 class="text-center">₱
                                                        <?= isset($loan_info['updated_balance']) ? number_format($loan_info['updated_balance'], 2) : '0.00'; ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-3 mb-2">
                                            <!-- Capital Card -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="font-weight: bold;">Deduction</h6>
                                                    <h5 class="text-center">₱
                                                        <?= isset($loan_info['minus_wage']) ? number_format($loan_info['minus_wage'], 2) : '0.00'; ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Table -->
                                    <div id="tableReload" class="table-responsive" style="max-height: 395px; overflow-y: auto;">
                                        <div class="d-flex justify-content-between sticky-top">
                                            <input type="text" id="searchInput" onkeyup="searchTable()"
                                                placeholder="Search for Transaction Date" class="form-control me-1 pe-1">
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary" type="button">
                                                    <i class="lni lni-magnifier"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <table class="table" style="max-height: 220px; overflow-y: auto;">
                                            <thead class="sticky-header">
                                                <tr>
                                                    <th>Principal Amount</th>
                                                    <th>Wage Deduction</th>
                                                    <th>Updated Balance</th>
                                                    <th>Updated Date History</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query_loan_history = "SELECT lh.updated_balance, lh.updated_balance_history, l.principal_amount, l.loan_term, lh.minus_wage, l.loan_duration
                                                FROM loan_history lh
                                                INNER JOIN loan l ON lh.loan_id = l.loan_id
                                                WHERE lh.member_id = '$member_id'
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
                        </div>
                    </div>

                    <div class="footer">
                        <div class="footer-right d-flex justify-content-center align-items-end mt-2 pt-2 "
                            style="background-color: #1d6325;">
                            <span class="sidebar-footer-text text-light  text-center" style="white-space: nowrap;">
                                A capstone project designed and developed by
                                <?php $encryptedMemberId = encryptData($member_info['member_id'], ENCRYPTION_KEY); ?>
                                <a href="u_developer.php?member_id=<?php echo $encryptedMemberId; ?>"
                                    class="text-decoration-none text-light">TEAM AREA</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for calculator -->
            <div class="modal fade" id="loanModal" tabindex="-1" aria-labelledby="loanModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-start">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="loanModalLabel">Loan Calculator</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body mb-0"> <!-- Add the "mb-0" class here -->
                            <div class="form-container">
                                <div class="form-group-calculator">
                                    <label for="amount">Loan Amount:</label>
                                    <select id="amount" name="amount" class="form-select mb-3">
                                        <!-- Populate options dynamically from computation_loan data -->
                                        <?php foreach ($computation_loan_data as $data): ?>
                                            <option value="<?php echo $data['principal_amount']; ?>">
                                                ₱<?php echo number_format($data['principal_amount'], 2); ?> PHP</option>
                                        <?php endforeach; ?>
                                    </select>

                                    <label for="totalAmount">Existing Balance:</label>
                                    <input type="text" class="form-control" id="existingBalance" name="existingBalance"
                                        value="<?php echo isset($loan_info['updated_balance']) && $loan_info['updated_balance'] !== null ? number_format($loan_info['updated_balance'], 2) : '0.00'; ?>"
                                        optional>

                                    <label for="months">Interest</label>
                                    <select id="months" name="months" class="form-select mb-3">
                                    </select>

                                    <label for="totalAmount">Processing Fee:</label>
                                    <input type="text" class="form-control" id="processingFee" name="processingFee" readonly>

                                    <label for="totalAmount">Capital Build-up:</label>
                                    <input type="text" class="form-control" id="capitalBuildUp" name="capitalBuildUp" readonly>


                                    <label for="totalAmount">Net Proceeds:</label>
                                    <input type="text" class="form-control" id="totalAmount" name="totalAmount" readonly>
                                </div>
                            </div>
                            <div class="mb=auto">
                                <p class="text-danger text-cnter">"Please note that if the Share Capital is below ₱75,000, the
                                    loan duration will be adjusted to accommodate the Capital Build-up process."</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Custom CSS for adjusting dropdown width and size -->
            <style>
                .modal-dialog-scrollable .modal-body .form-group-calculator .form-select {
                    width: 100%;
                    /* Adjust the width as needed */
                    font-size: 12px;
                    /* Adjust the font size as needed */
                }

                .form-control {
                    width: 100%;
                }
            </style>

            <!-- Modal for Account Settings -->
            <div class="modal fade" id="updateAccountModal" tabindex="-1" aria-labelledby="updateAccountModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #1d6325; color: white; padding: 20px;">
                            <h5 class="modal-title" id="editMemberModalLabel">Edit Account Information</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="padding: 20px;">
                            <form action="../function/editMembersFunction.php" method="POST" class="updateInfoMembers">
                                <?php $encryptedMemberId = encryptData($member_info['member_id'], ENCRYPTION_KEY); ?>
                                <input type="hidden" id="member_id" name="member_id" value="<?php echo $encryptedMemberId; ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contactNumber" class="form-label">Contact Number</label>
                                        <input type="text" class="form-control" id="contactNumber" name="contactNumber"
                                            value="<?php echo $member_info['contact_number']; ?>" required
                                            style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="userName" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="userName" name="username"
                                            value="<?php echo $member_info['username']; ?>" required
                                            style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="eMail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="eMail" name="email"
                                            value="<?php echo $member_info['email']; ?>" required
                                            style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                    </div>
                                </div>
                                <!-- Submit Button -->
                                <button type="submit" name="updateMembersInfo" class="btn"
                                    style="background-color: #2e7d32; border: none; color: white; width: 100%; padding: 12px; margin-top: 20px;">
                                    Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for changing password -->
            <div class="modal fade" id="passwordAccountModal" tabindex="-1" aria-labelledby="passwordAccountModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #2e7d32; color: white; padding: 20px;">
                            <h5 class="modal-title" id="passwordAccountModal" style="color: white;">Change Password</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-color: #99cc99; color: #004d00;"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../function/editMembersFunction.php" method="POST" class="updatePassword">
                                <div class="mb-3">
                                    <?php $encryptedMemberId = encryptData($member_info['member_id'], ENCRYPTION_KEY); ?>
                                    <input type="hidden" id="member_id" name="member_id" value="<?php echo $encryptedMemberId; ?>">

                                    <!-- Old Password -->
                                    <label for="oldPassword" class="form-label" style="color: #004d00;">Old Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="oldPassword" name="oldpassword" required style="border: 1px solid #004d00;">
                                        <button class="btn" type="button" id="toggleOldPassword" style="background-color: #99cc99; color: #004d00; border: 1px solid #004d00;">Show</button>
                                    </div>

                                    <!-- New Password -->
                                    <label for="newPassword" class="form-label" style="color: #004d00;">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="newPassword" name="newpassword" required style="border: 1px solid #004d00;">
                                        <button class="btn" type="button" id="toggleNewPassword" style="background-color: #99cc99; color: #004d00; border: 1px solid #004d00;">Show</button>
                                    </div>

                                    <!-- Re-enter New Password -->
                                    <label for="reEnterNewPassword" class="form-label" style="color: #004d00;">Re-enter Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="reEnterNewPassword" name="reEnternewpassword" required style="border: 1px solid #004d00;">
                                        <button class="btn" type="button" id="toggleReEnterNewPassword" style="background-color: #99cc99; color: #004d00; border: 1px solid #004d00;">Show</button>
                                    </div>
                                </div>

                                <button type="submit" name="updatepassword" class="btn" style="background-color: #2e7d32; border: none; color: white; width: 100%; padding: 12px; margin-top: 20px;">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Modal -->
            <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- Add modal-dialog-centered here -->
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header" style="background-color: #1d6325; color: white; border-radius: 5px 5px 0 0;">
                            <h5 class="modal-title" id="profileModalLabel" style="font-weight: bold;">Profile Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <!-- Modal Body with Two-Column Layout -->
                        <div class="modal-body">
                            <div class="row">
                                <!-- First Column: Profile Image and Username -->
                                <div class="col-md-4 text-center">
                                    <img src="<?= !empty($member_info['pictures']) ? '../assets/pictures/memberPictures/' . $member_info['pictures'] : '../assets/pictures/account.png'; ?>"
                                        class="rounded img-fluid" alt="Profile Image"
                                        style="width: 150px; height: 150px; object-fit: cover; border: 2px solid #004d40; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 15px;">
                                    <h6 style="font-weight: bold; color: #004d40;"><?= $member_info['username']; ?></h6>
                                </div>

                                <!-- Second Column: Member Information -->
                                <div class="col-md-8">
                                    <div class="row mb-3">
                                        <div class="col-sm-6"><strong style="color: #004d40;">First Name:</strong></div>
                                        <div class="col-sm-6"><?= $member_info['fname']; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-6"><strong style="color: #004d40;">Middle Name:</strong></div>
                                        <div class="col-sm-6"><?= $member_info['mname']; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-6"><strong style="color: #004d40;">Last Name:</strong></div>
                                        <div class="col-sm-6"><?= $member_info['lname']; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-6"><strong style="color: #004d40;">Contact Number:</strong></div>
                                        <div class="col-sm-6"><?= $member_info['contact_number']; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong style="color: #004d40;">Email Address:</strong></div>
                                        <div class="col-sm-6"><?= $member_info['email']; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-6"><strong style="color: #004d40;">Institute:</strong></div>
                                        <div class="col-sm-6"><?= $institute_info['institute_name']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer" style="border-top: 1px solid #e0e0e0;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="#updateAccountModal" data-bs-toggle="modal" class="btn btn-success">Edit Profile</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for changing profile picture -->
            <div class="modal fade" id="changeProfilePictureModal" tabindex="-1"
                aria-labelledby="changeProfilePictureModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="changeProfilePictureModalLabel">Change Profile Picture</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Add a form for uploading a new profile picture -->
                            <form action="../function/upload_profile_picture.php" method="post" enctype="multipart/form-data">
                                <?php $encryptedMemberId = encryptData($member_info['member_id'], ENCRYPTION_KEY); ?>
                                <input type="hidden" id="member_id" name="member_id" value="<?php echo $encryptedMemberId; ?>">
                                <div class="mb-3">
                                    <label for="profilePicture" class="form-label">Choose a new profile picture:</label>
                                    <input type="file" class="form-control" id="profilePicture" name="profilePicture"
                                        accept="image/*">
                                </div>
                                <button type="submit" name="uploadPicture" class="btn btn-primary">Upload</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Sending Report -->
            <div class="modal fade" id="sendReportModal" tabindex="-1" aria-labelledby="sendReportModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sendReportModalLabel">Send Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Form for sending a report -->
                            <form action="../function/sendreportfunction.php" method="post" class="sendReport">
                                <?php $encryptedMemberId = encryptData($member_info['member_id'], ENCRYPTION_KEY); ?>
                                <input type="hidden" id="member_id" name="member_id" value="<?php echo $encryptedMemberId; ?>">
                                <input type="hidden" class="form-control" id="loan_ID" name="loan_id"
                                    value="<?php echo $loan_info['loan_id']; ?>">
                                <!-- Dropdown -->
                                <div class="mb-3">
                                    <label for="reportType" class="form-label">Report Type</label>
                                    <select class="form-select" id="reportType" name="reportType">
                                        <option value="Bug Report">Bug Report</option>
                                        <option value="Feature Request">Feature Request</option>
                                        <option value="Payment Discrepancy">Payment Discrepancy</option>
                                        <option value="Loan Payment Difficulty">Loan Payment Difficulty</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <!-- Input field -->
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject">
                                </div>
                                <input type="hidden" name="report_status" value="1">
                                <!-- Text area -->
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5"></textarea>
                                </div>
                                <!-- Submit button -->
                                <button type="submit" name="sendReport" class="btn btn-primary">Send Report</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
            <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="../assets/script.js"></script>
            <script src="../assets/viewPdf.js"></script>

            <script>
                // Function to toggle password visibility
                function togglePasswordVisibility(inputFieldId, buttonId) {
                    const inputField = document.getElementById(inputFieldId);
                    const button = document.getElementById(buttonId);
                    if (inputField.type === "password") {
                        inputField.type = "text";
                        button.textContent = "Hide"; // Change button text to Hide
                    } else {
                        inputField.type = "password";
                        button.textContent = "Show"; // Change button text to Show
                    }
                }

                // Add event listeners to the toggle buttons
                document.getElementById('toggleOldPassword').addEventListener('click', function () {
                    togglePasswordVisibility('oldPassword', 'toggleOldPassword'); // Corrected ID
                });

                document.getElementById('toggleNewPassword').addEventListener('click', function () {
                    togglePasswordVisibility('newPassword', 'toggleNewPassword'); // Corrected ID
                });

                document.getElementById('toggleReEnterNewPassword').addEventListener('click', function () {
                    togglePasswordVisibility('reEnterNewPassword', 'toggleReEnterNewPassword'); // Corrected ID
                });
            </script>
            <script>
                // Function to populate the months dropdown based on the selected principal amount
                function populateMonthsDropdown() {
                    var principalAmount = parseFloat(document.getElementById('amount').value);
                    var monthsDropdown = document.getElementById('months');
                    monthsDropdown.innerHTML = ''; // Clear existing options

                    // Find the corresponding row in computation_loan_data
                    var selectedData = <?php echo json_encode($computation_loan_data); ?>;
                    var selectedRow;
                    for (var i = 0; i < selectedData.length; i++) {
                        if (parseFloat(selectedData[i]['principal_amount']) === principalAmount) {
                            selectedRow = selectedData[i];
                            break;
                        }
                    }

                    // Populate options dynamically based on the selected principal amount
                    if (selectedRow) {
                        var options = ['months_5', 'months_10', 'months_15', 'months_20'];
                        for (var i = 0; i < options.length; i++) {
                            var monthsValue = selectedRow[options[i]];
                            var monthsText = options[i].replace('months_', '') + ' months: ' + monthsValue + ' PHP';
                            monthsDropdown.innerHTML += '<option value="' + monthsValue + '">' + monthsText + '</option>';
                        }
                    }
                }

                // Ensure values are updated correctly when the modal is opened
                document.getElementById('loanModal').addEventListener('shown.bs.modal', function() {
                    populateMonthsDropdown(); // Populate the months dropdown automatically

                    // Immediately trigger total calculation to use the pre-filled existing balance value
                    updateTotalAmount();
                });

                // Trigger change event for loan amount dropdown
                document.getElementById('amount').addEventListener('change', function() {
                    populateMonthsDropdown(); // Populate the months dropdown when the loan amount changes
                    updateTotalAmount(); // Ensure calculation is done when amount changes
                });

                // Trigger total amount update when months dropdown changes
                document.getElementById('months').addEventListener('change', function() {
                    updateTotalAmount(); // Update the total amount when months change
                });

                // Trigger total amount calculation when existing balance is typed/changed
                document.getElementById('existingBalance').addEventListener('input', function() {
                    updateTotalAmount(); // Update the total amount when the existing balance is modified
                });

                // Function to update the total amount
                function updateTotalAmount() {
                    // Get and parse the loan amount, default to 0 if invalid
                    var principalAmount = parseFloat(document.getElementById('amount').value) || 0;

                    // Get and parse the existing balance, default to 0 if not provided or invalid
                    var existingBalance = parseFloat(document.getElementById('existingBalance').value.replace(/,/g, '')) || 0; // Ensure commas are removed

                    // Get the selected months value, default to 0 if empty
                    var monthsValue = document.getElementById('months').value;
                    var loanDuration = monthsValue ? parseFloat(monthsValue.split(' ')[0].replace(',', '')) : 0;

                    // Define processing fee ranges
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

                    // Calculate processing fee based on principal amount
                    var processingFee = 0;
                    for (var i = 0; i < feeRanges.length; i++) {
                        var range = feeRanges[i];
                        if (principalAmount >= range.min && principalAmount <= range.max) {
                            processingFee = range.fee;
                            break; // Exit loop once the range is found
                        }
                    }

                    // Compute capital build-up share capital
                    var capitalBuildUp = (principalAmount - existingBalance - loanDuration - processingFee) * 0.05;

                    // Compute total loanable amount
                    var totalAmount = principalAmount - existingBalance - loanDuration - processingFee - capitalBuildUp;

                    // Cap the total amount to a maximum of 100,000 PHP
                    if (totalAmount > 100000) {
                        totalAmount = 100000;
                    }

                    // Update the input fields with computed values (use a currency format)
                    document.getElementById('processingFee').value = processingFee.toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                    document.getElementById('capitalBuildUp').value = capitalBuildUp.toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                    document.getElementById('totalAmount').value = totalAmount.toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                }
            </script>



            <script>
                const loanAmountSelect = document.getElementById('amount');

                // Expand the dropdown on click
                loanAmountSelect.addEventListener('click', function() {
                    this.setAttribute('size', '5'); // Show 5 options
                });

                // Close the dropdown when a selection is made
                loanAmountSelect.addEventListener('change', function() {
                    setTimeout(() => {
                        this.removeAttribute('size'); // Collapse dropdown after selection
                    }, 0); // Timeout to allow for selection to register
                });

                // Close the dropdown when it loses focus
                loanAmountSelect.addEventListener('blur', function() {
                    this.removeAttribute('size'); // Ensure dropdown collapses if user clicks away
                });
            </script>


            <script>
                // Search Function
                function searchTable() {
                    var input, filter, table, tr, td, i, txtValue;
                    input = document.getElementById("searchInput");
                    filter = input.value.toUpperCase();
                    table = document.querySelector(".table"); // Assuming your table has the 'table' class
                    tr = table.getElementsByTagName("tr");
                    for (i = 0; i < tr.length; i++) {
                        td = tr[i].getElementsByTagName("td")[3]; // Assuming the third column (index 2) contains the data you want to search
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


            <script>
                // Function to adjust styles for the calculator based on screen size
                function adjustCalculatorStyles() {
                    var modal = document.getElementById('loanModal');
                    var amountDropdown = document.getElementById('amount');
                    var processingFeeInput = document.getElementById('processingFee');

                    // Check if modal is open
                    if (modal && modal.classList.contains('show')) {
                        // Get the viewport width
                        var viewportWidth = window.innerWidth;

                        // Adjust styles based on screen size
                        if (viewportWidth <= 576) { // Small screens (mobile)
                            amountDropdown.style.fontSize = '14px';
                            amountDropdown.style.padding = '0.375rem 0.75rem';
                            processingFeeInput.style.fontSize = '14px';
                            processingFeeInput.style.padding = '0.375rem 0.75rem';
                            modal.style.padding = '1rem';
                        } else if (viewportWidth <= 768) { // Medium screens (tablets)
                            amountDropdown.style.fontSize = '16px';
                            amountDropdown.style.padding = '0.5rem 1rem';
                            processingFeeInput.style.fontSize = '16px';
                            processingFeeInput.style.padding = '0.5rem 1rem';
                            modal.style.padding = '1.5rem';
                        } else { // Larger screens (desktops)
                            amountDropdown.style.fontSize = '18px';
                            amountDropdown.style.padding = '0.625rem 1.25rem';
                            processingFeeInput.style.fontSize = '18px';
                            processingFeeInput.style.padding = '0.625rem 1.25rem';
                            modal.style.padding = '2rem';
                        }
                    }
                }

                // Adjust styles when the modal is shown
                document.getElementById('loanModal').addEventListener('shown.bs.modal', function() {
                    adjustCalculatorStyles();
                });

                // Adjust styles when the window is resized
                window.addEventListener('resize', function() {
                    adjustCalculatorStyles();
                });
            </script>




            <script>
                // Function to reload the table
                function reloadTable() {
                    // You need to implement this function to reload the table data
                    location.reload();
                }

                // Function to open the account settings modal
                function openAccountSettingsModal() {
                    // Display the modal
                    $('#updateAccountModal').modal('show');
                }

                // Function to close the account settings modal
                function closeupdateAccountModal() {
                    // Hide the modal
                    $('#updateAccountModal').modal('hide');
                }
            </script>

            <!-- responsive of the name in the navbar -->
            <script>
                function adjustFontSize() {
                    const nameElement = document.getElementById('memberName');
                    const nameLength = nameElement.textContent.length;

                    if (nameLength > 20) {
                        nameElement.style.fontSize = '0.5em'; // Smaller font for long names
                    } else if (nameLength > 15) {
                        nameElement.style.fontSize = '0.8em'; // Medium font for medium-length names
                    } else {
                        nameElement.style.fontSize = '1em'; // Default font for short names
                    }
                }

                // Call the function on page load
                window.onload = adjustFontSize;
            </script>



        </body>

        </html>

<?php
    }
} else {
    // No member found with the provided ID
    echo "No member found with ID: $member_id";
}
?>