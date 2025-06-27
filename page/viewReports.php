<?php
session_start();



if (!isset($_SESSION['email']) || !isset($_SESSION['username']) || $_SESSION['role_id'] != 1) {
    // If not logged in or not an admin, redirect to the login page
    header("Location: user_dashboard.php");
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BASCPCC</title>
    <link href="../assets/bootstrap/font/lineicons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">

    <link rel="stylesheet" href="../assets/report.css">

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
                    <a href="loan_data.php" class="sidebar-link">
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
                    <a href="viewReports.php" class="sidebar-link active" title="Reports">
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


            <div id="content-wrapper" class="d-flex flex-column">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
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
                            <!-- Filter Buttons -->



                            <div class="d-flex justify-content-between align-items-center">
                                <div class="button-container d-flex">
                                    <!-- Pending Button with Icon -->
                                    <button id="filterPending" class="btn btn-primary me-2 mb-2">
                                        <i class="lni lni-timer"></i> <!-- Lineicon for Pending -->
                                        <span class="button-text d-none d-sm-inline">Pending</span>
                                    </button>

                                    <!-- Archived Button with Icon -->
                                    <button id="filterArchived" class="btn btn-danger me-2 mb-2">
                                        <i class="lni lni-archive"></i> <!-- Lineicon for Archived -->
                                        <span class="button-text d-none d-sm-inline">Archived</span>
                                    </button>
                                </div>
                                <!-- Search Bar -->
                                <!-- Responsive Search Bar -->
                                <div class="input-group mb-2" style="max-width: 100%; flex: 1 1 300px;">
                                    <input type="text" id="searchInput" onkeyup="searchReports()"
                                        placeholder="Search for reports..." class="form-control">
                                    <button class="btn btn-success" type="button">
                                        <i class="lni lni-magnifier"></i>
                                    </button>
                                </div>
                            </div>







                            <!-- Report List -->
                            <div class="report-list-wrapper">
                                <div class="report-list">
                                    <!-- Reports will be inserted dynamically here -->
                                </div>
                            </div>
                        </div>
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

    <!-- Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Sender Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="email-subject"></h6>
                    <p class="email-report-type"></p>
                    <p class="email-message"></p>
                    <p class="email-date"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <!-- Button to change status to 'Archived' -->
                    <button id="markAsArchived" type="button" class="btn btn-warning">Mark as Archived</button>
                </div>
            </div>
        </div>
    </div>





    <script>

        // Search Function for the Report List
        function searchReports() {
            let input, filter, reportList, reportCards, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            reportList = document.getElementsByClassName("report-list")[0];  // Get the report list div
            reportCards = reportList.getElementsByClassName("report-card");  // Get all the report cards

            // Loop through all report cards and hide those that don't match the search query
            for (i = 0; i < reportCards.length; i++) {
                txtValue = reportCards[i].textContent || reportCards[i].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    reportCards[i].style.display = ""; // Show report if it matches
                } else {
                    reportCards[i].style.display = "none"; // Hide report if it doesn't match
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadReports(1); // Load Pending reports by default

            // Filter buttons
            document.getElementById('filterPending').addEventListener('click', function () {
                loadReports(1); // Load Pending reports
            });
            document.getElementById('filterArchived').addEventListener('click', function () {
                loadReports(2); // Load Archived reports
            });

            // Load reports based on the status
            function loadReports(status) {
                fetch(`../function/getReportDetails.php?status=${status}`)
                    .then(response => response.json())
                    .then(data => {
                        const reportList = document.querySelector('.report-list');
                        reportList.innerHTML = ''; // Clear previous reports
                        data.forEach(report => {
                            const reportCard = document.createElement('div');
                            reportCard.classList.add('report-card');
                            reportCard.dataset.reportId = report.report_id;

                            // Convert the time_date to a valid JavaScript Date
                            const reportDate = new Date(report.time_date);
                            const formattedDate = isNaN(reportDate) ? 'Invalid date' : reportDate.toLocaleString('en-PH', {
                                month: 'long',
                                day: 'numeric',
                                year: 'numeric',
                                hour: 'numeric',
                                minute: 'numeric',
                                second: 'numeric',
                                hour12: true
                            });

                            reportCard.innerHTML = `
                    <div class="card-header">
                        <span class="sender-name">${report.sender_name}</span>
                        <span class="time-date">${formattedDate}</span>
                    </div>
                    <div class="card-body">
                        <span class="email-subject">${report.subject}</span>
                        <p class="email-preview">${report.message.substring(0, 50)}...</p>
                    </div>
                `;
                            reportCard.addEventListener('click', () => openModal(report.report_id, status));
                            reportList.appendChild(reportCard);
                        });
                    });
            }

            // Open modal based on the report status
            function openModal(reportId, currentStatus) {
                fetch(`../function/getReportDetails.php?id=${reportId}`)
                    .then(response => response.json())
                    .then(report => {
                        document.getElementById('reportModalLabel').textContent = report.sender_name;
                        document.querySelector('.email-subject').textContent = report.subject;
                        document.querySelector('.email-report-type').textContent = report.report_type;
                        document.querySelector('.email-message').textContent = report.message;

                        // Log the time_date for debugging
                        console.log('Time Date:', report.time_date);

                        // Convert the time_date to a valid JavaScript Date
                        const reportDate = new Date(report.time_date);
                        console.log('Parsed Date:', reportDate);
                        console.log('Is NaN:', isNaN(reportDate)); // Check if it's NaN

                        // Format the date if it's valid
                        if (!isNaN(reportDate.getTime())) {
                            const formattedDate = reportDate.toLocaleString('en-PH', {
                                month: 'long',
                                day: 'numeric',
                                year: 'numeric',
                                hour: 'numeric',
                                minute: 'numeric',
                                second: 'numeric',
                                hour12: true
                            });
                            document.querySelector('.email-date').textContent = formattedDate;
                        }

                        const archiveButton = document.getElementById('markAsArchived');
                        // Enable button only for Pending reports
                        if (currentStatus === 1) {
                            archiveButton.style.display = 'inline-block';
                            archiveButton.onclick = function () {
                                updateReportStatus(report.report_id, 2); // Status 2: Archived
                            };
                        } else {
                            archiveButton.style.display = 'none'; // Hide the button for Archived reports
                        }

                        const modal = new bootstrap.Modal(document.getElementById('reportModal'));
                        modal.show();
                    });
            }

            // Function to update the report status
            function updateReportStatus(reportId, status) {
                fetch('../function/getReportDetails.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: reportId, status: status }) // Send report ID and new status
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            swal({
                                title: "Success!",
                                text: "Report status updated successfully",
                                icon: "success",
                                button: "OK"
                            }).then(() => {
                                location.reload(); // Reload the page to reflect changes
                            });
                        } else {
                            swal({
                                title: "Error!",
                                text: "Error updating report status",
                                icon: "error",
                                button: "Try Again"
                            });
                        }
                    });
            }

        });

    </script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/bootstrap/js/sweetalert.min.js"></script>
    <script src="../assets/script.js"></script>
</body>

</html>