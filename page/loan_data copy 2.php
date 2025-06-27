<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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
    <link href="../assets/bootstrap/font/lineicons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/addMembers.css">
    <link rel="stylesheet" href="../assets/loan_data.css">
    <link rel="icon" href="../assets/pictures/cooplogo.jpg" type="image/x-icon"> <!-- Adjust the path accordingly -->
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"> <!-- Adjust the path accordingly -->
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/pictures/cooplogo.jpg">
    <!-- Adjust the path accordingly -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/pictures/cooplogo.jpg">
</head>

<body>
    <div class="wrapper">
        <aside id="sidebar">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="lni lni-grid-alt"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="#">BASCPCC</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="admin_dashboard.php" class="sidebar-link">
                        <i class="lni lni-bar-chart"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="addMembers.php" class="sidebar-link">
                        <i class="lni lni-user"></i>
                        <span>Add Members</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="loan_data.php" class="sidebar-link active">
                        <i class="lni lni-layout"></i>
                        <span>Update Balance</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="sendNotification.php" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Send Notifications</span>
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


            <div id="content-wrapper" class="d-flex flex-column">
                <style>
                    .container,
                    .row,
                    .col {
                        border: none;
                    }
                </style>
                <div class="container">
                    <br>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="button-container d-flex">
                                    <!-- Upload Button -->
                                    <button id="uploadMembers" name="uploadMembers"
                                        class="upload-loan-button btn btn-success me-2 mb-2" data-bs-toggle="modal"
                                        data-bs-target="#uploadMemberModal">
                                        <i class="lni lni-upload"></i>
                                        <span class="button-text d-none d-sm-inline">Upload Updated Loan</span>
                                    </button>
                                    <!-- Print Dropdown -->
                                    <a class="print-button btn btn-success me-2 mb-2" href="#" class="btn btn-success"
                                        data-bs-toggle="modal" data-bs-target="#confirmPasswordModal">


                                        <i class="lni lni-printer"></i> <!-- Lineicons print icon -->
                                        <span class="button-text">Print</span>
                                        <!-- Text will be hidden on small screens -->
                                    </a>
                                </div>
                                <!-- Search Box Container -->
                                <div class="input-group mb-2" style="max-width: 100%; flex: 1 1 300px;">
                                    <input type="text" id="searchInput" onkeyup="searchTable()" class="form-control"
                                        placeholder="Search for names..." aria-label="Search"
                                        style="font-size: 14px; padding: 5px;">
                                    <button class="btn btn-success" type="button"
                                        style="font-size: 14px; padding: 5px 10px;">
                                        <i class="lni lni-magnifier"></i>
                                    </button>
                                </div>
                            </div>



                            <!-- Table -->
                            <div class="table-responsive text-center" style="max-height: 400px; overflow-y: auto;">
                                <table id="memberTable" class="table table-bordered mt-2 table-striped">
                                    <thead class="thead-light" style="position: sticky; top: 0;">
                                        <tr>
                                            <th>First Name</th>
                                            <th>Middle Name</th>
                                            <th>Last Name</th>
                                            <th>Month Update</th>
                                            <th>Share Capital</th>
                                            <th>Wage Deduction</th>
                                            <th>Updated Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        require '../conn/dbcon.php';
                                        $query = "SELECT m.member_id, m.fname, m.mname, m.lname, l.updated_balance_history, l.share_capital, l.minus_wage, l.updated_balance
                                        FROM members m
                                        INNER JOIN loan l ON m.member_id = l.member_id ORDER BY m.fname ASC"; // Adjusted query to fetch loan-related data
                                        $result_query = $conn->query($query);

                                        if ($result_query->num_rows > 0) {
                                            while ($data = $result_query->fetch_assoc()) {
                                                // Convert updated_balance_history to a formatted date string
                                                $dateObject = DateTime::createFromFormat('Y-m-d', $data['updated_balance_history']);
                                                $formatted_date = $dateObject ? $dateObject->format('F j, Y') : 'Invalid date'; // Handle invalid date

                                        ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($data['fname']); ?></td>
                                                    <td><?= htmlspecialchars($data['mname']); ?></td>
                                                    <td><?= htmlspecialchars($data['lname']); ?></td>
                                                    <td><?= htmlspecialchars($formatted_date); ?></td>
                                                    <!-- Display formatted date -->
                                                    <td>₱ <?= number_format($data['share_capital'], 2); ?></td>
                                                    <td>₱ <?= number_format($data['minus_wage'], 2); ?></td>
                                                    <td>₱ <?= number_format($data['updated_balance'], 2); ?></td>
                                                </tr>
                                        <?php
                                            }
                                        } else {
                                            echo '<tr><td colspan="7" class="text-center">No data available</td></tr>'; // Adjusted column span
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Footer Section -->

                    </div>
                </div>
            </div>
            <div class="footer-right d-flex justify-content-center align-items-end mt-2 pt-2 fw-bold"
                style="position: relative; background-color: #1d6325;">
                <pre
                    class="sidebar-footer-text text-light fw-bold text-center"> A capstone project designed and developed by <a href="developer.php" class="text-decoration-none text-light">TEAM AREA</a></pre>
            </div>
        </div>
    </div>




    <style>
        .dropdown-menu.scrollable-dropdown-menu {
            max-height: 150px;
            /* Adjust as needed */
            overflow-y: auto;
        }

        /* Ensure the backdrop is visible */
        .modal-backdrop {
            display: block !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
        }
    </style>


    <div class="modal fade" id="uploadMemberModal" tabindex="-1" aria-labelledby="uploadMemberModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #17a34a;">
                    <h5 class="modal-title" id="uploadMemberModalLabel">Upload Updated Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form for Uploading Members -->
                    <form action="../function/csv.php" enctype="multipart/form-data" method="POST">
                        <div class="mb-3">
                            <label for="file" class="form-label">Choose file to upload</label>
                            <input type="file" class="form-control" id="fileInput" name="data_upload" id="data_upload"
                                required>
                        </div>
                        <div class="modal-footer">
                            <button name="submit" class="btn btn-success" id="uploadLoanID" style="align-items: center;">Upload</button>
                        
                        </div>
                    </form>

                    
                </div>
            </div>
        </div>
    </div>

 


    <!-- Password Confirmation Modal -->
    <div class="modal fade" id="confirmPasswordModal" tabindex="-1" aria-labelledby="confirmPasswordModalLabel" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #17a34a;">
                    <h5 class="modal-title" id="confirmPasswordModalLabel">Confirm Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../function/confirmPasswordPrint.php" method="POST" id="passwordForm">
                        <div class="mb-3">
                            <!-- Removed session_start() here -->
                            <input type="hidden" id="memberid" value="<?php echo $_SESSION['member_id']; ?>">

                            <label for="passwordInput" class="form-label">Enter Password</label>
                            <input type="password" id="passwordInput" name="password" class="form-control"
                                autocomplete="off" readonly
                                onfocus="this.removeAttribute('readonly');" placeholder="Enter password" required>

                        </div>
                        <div id="errorMessage" class="text-danger" style="display: none;">Incorrect password. Please try again.</div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="print-button btn btn-success " name="confirmPassword" data-bs-toggle="modal" id="confirmPasswordID">
                        Confirm Password
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Print Report Modal -->
    <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="monthlyModalLabel">Print Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="monthlyForm" action="../function/printFunction.php" enctype="multipart/form-data"
                        method="POST">
                        <div class="mb-3">
                            <h5 class="mb-1">Select Report Type Options</h5>
                            <div class="dropdown-report-type">
                                <button class="btn btn-secondary dropdown-toggle" id="report-type-dropdown"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Select Report Type
                                </button>
                                <ul class="dropdown-menu scrollable-dropdown-menu">
                                    <li><a class="dropdown-item" href="#" data-report-type="monthly">Monthly</a>
                                    </li>
                                    <li><a class="dropdown-item" href="#" data-report-type="quarterly">Quarterly</a>
                                    </li>
                                    <li><a class="dropdown-item" href="#" data-report-type="annual">Annual</a></li>
                                </ul>
                            </div>

                            <br>

                            <label id="month-selection-label" for="month-dropdown" style="display: none;">Select a
                                Month</label>
                            <div class="dropdown-month" id="month-dropdown" style="display: none;">
                                <button class="btn btn-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Select a Month
                                </button>
                                <ul class="dropdown-menu scrollable-dropdown-menu" id="month-menu"></ul>
                            </div>

                            <label id="month-year-selection-label" for="month-year-dropdown"
                                style="display: none;">Select a Year</label>
                            <div class="dropdown-month-year" id="month-year-dropdown" style="display: none;">
                                <button class="btn btn-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Select a Year
                                </button>
                                <ul class="dropdown-menu scrollable-dropdown-menu" id="month-year-menu">
                                </ul>
                            </div>

                            <label id="quarter-selection-label" for="quarter-dropdown" style="display: none;">Select
                                a Quarter</label>
                            <div class="dropdown-quarter" id="quarter-dropdown" style="display: none;">
                                <button class="btn btn-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Select a Quarter
                                </button>
                                <ul class="dropdown-menu scrollable-dropdown-menu" id="quarter-menu">
                                    <li><a class="dropdown-item" href="#" data-quarter="Q1">Quarter 1</a>
                                    </li>
                                    <li><a class="dropdown-item" href="#" data-quarter="Q2">Quarter 2</a>
                                    </li>
                                    <li><a class="dropdown-item" href="#" data-quarter="Q3">Quarter 3</a>
                                    </li>
                                    <li><a class="dropdown-item" href="#" data-quarter="Q4">Quarter 4</a>
                                    </li>
                                </ul>


                                <ul class="dropdown-menu scrollable-dropdown-menu" id="quarter-year-menu">

                                </ul>
                            </div>

                            <label id="year-selection-label" for="year-dropdown" style="display: none;">Select a
                                Year</label>
                            <div class="dropdown-year" id="year-dropdown" style="display: none;">
                                <button class="btn btn-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Select a Year
                                </button>
                                <ul class="dropdown-menu scrollable-dropdown-menu" id="year-menu"></ul>
                            </div>

                            <input type="hidden" name="report_type" id="reportType">
                            <input type="hidden" name="selected_month" id="selectedMonth">
                            <input type="hidden" name="selected_month_year" id="selectedMonthYear">
                            <input type="hidden" name="selected_quarter" id="selectedQuarter">
                            <input type="hidden" name="selected_quarter_year" id="selected_quarter_year">


                            <input type="hidden" name="selected_year" id="selectedYear">


                        </div>
                        <div class="modal-footer d-flex justify-content-center align-items-center">
                            <button type="submit" class="btn btn-success ms-2" name="printReport">Print
                                Report</button>
                            <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    
  // Select elements
const confirmPasswordButton = document.getElementById('confirmPasswordID');
const printModal = document.getElementById('printModal');
const confirmPasswordModal = document.getElementById('confirmPasswordModal');
const errorMessage = document.getElementById('errorMessage');
const passwordForm = document.getElementById('passwordForm');
const printReportButton = document.querySelector('button[name="printReport"]');

// Function to open the Print Report Modal
function openPrintReportModal() {
    const printModalInstance = bootstrap.Modal.getOrCreateInstance(printModal);
    printModalInstance.show();
}

// Function to handle Confirm Password Button click
confirmPasswordButton.addEventListener('click', async function (event) {
    event.preventDefault();

    // Retrieve password input value
    const passwordInput = document.getElementById('passwordInput').value;
    const memberId = document.getElementById('memberid').value;

    // Validate password before proceeding
    if (!passwordInput || !memberId) {
        errorMessage.textContent = 'Please enter your password.';
        errorMessage.style.display = 'block';

        // Ensure the modal remains open
        const confirmModalInstance = bootstrap.Modal.getOrCreateInstance(confirmPasswordModal);
        confirmModalInstance.show();

        // Focus the password field
        document.getElementById('passwordInput').focus();
        return;
    }

    // Create a FormData object for the AJAX request
    const formData = new FormData(passwordForm);
    formData.append('memberid', memberId);

    try {
        // AJAX Request to validate password
        const response = await fetch('../function/confirmPasswordPrint.php', {
            method: 'POST',
            body: formData,
        });

        // Check if response is okay
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        // Get the response text and trim whitespace
        const resultText = await response.text();
        const result = resultText.trim();

        // Handle specific response strings
        switch (result) {
            case 'success':
                // Hide Confirm Password Modal and open Print Report Modal
                const confirmModalInstance = bootstrap.Modal.getOrCreateInstance(confirmPasswordModal);
                confirmModalInstance.hide();

                // Clear password input and error message
                document.getElementById('passwordInput').value = '';
                errorMessage.style.display = 'none';

                // Open the Print Report Modal
                openPrintReportModal();
                break;

            case 'incorrect_password':
                errorMessage.textContent = 'Incorrect password. Please try again.';
                errorMessage.style.display = 'block';
                break;

            case 'user_not_found':
                errorMessage.textContent = 'User not found. Please check your member ID.';
                errorMessage.style.display = 'block';
                break;

            case 'missing_input':
                errorMessage.textContent = 'Please enter your password and member ID.';
                errorMessage.style.display = 'block';
                break;

            case 'db_error':
                errorMessage.textContent = 'A database error occurred. Please try again later.';
                errorMessage.style.display = 'block';
                break;

            default:
                errorMessage.textContent = 'An unexpected error occurred. Please try again.';
                errorMessage.style.display = 'block';
                break;
        }

        // Ensure Confirm Password Modal stays open if there's an error
        if (result !== 'success') {
            const confirmModalInstance = bootstrap.Modal.getOrCreateInstance(confirmPasswordModal);
            confirmModalInstance.show();

            // Focus the password field on error
            document.getElementById('passwordInput').focus();
        }
    } catch (error) {
        console.error('Error during password verification:', error);
        errorMessage.textContent = 'An error occurred during verification. Please try again.';
        errorMessage.style.display = 'block';

        // Ensure Confirm Password Modal stays open on error
        const confirmModalInstance = bootstrap.Modal.getOrCreateInstance(confirmPasswordModal);
        confirmModalInstance.show();

        // Focus the password field
        document.getElementById('passwordInput').focus();
    }
});


// Prevent autocomplete issues in the form
document.addEventListener('DOMContentLoaded', () => {
    passwordForm.setAttribute('autocomplete', 'off');
    document.getElementById('passwordInput').setAttribute('autocomplete', 'new-password');
});

// Prevent the Enter key from being used in the modal
document.getElementById('passwordForm').addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
});

    </script>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../assets/script.js"></script>
    <script src="../assets/loan_data.js"></script>
    <script>
        function searchTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("memberTable");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
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