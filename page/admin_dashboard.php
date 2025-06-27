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
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">

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
                    <a href="admin_dashboard.php" onclick="admin_dashboard(); return false;">BASCPCC</a>
                </div>
            </div>
            <ul class="sidebar-nav">

                <li class="sidebar-item">
                    <a href="admin_dashboard.php" class="sidebar-link active" title="Dashboard"
                        onclick="admin_dashboard(); return false;">
                        <i class="lni lni-bar-chart"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="addMembers.php" class="sidebar-link" title="Add Members">
                        <i class="lni lni-user"></i>
                        <span>Add Members</span>
                    </a>
                </li>


                <li class="sidebar-item">
                    <a href="loan_data.php" class="sidebar-link" title="Add Loan">
                        <i class="lni lni-layout"></i>
                        <span>Updated Balance</span>
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





            <!-- Wrapper -->
            <style>
                /* Custom CSS to remove border from container, row, and column */
                .custom-container,
                .custom-row,
                .custom-col {
                    border: none;
                }
            </style>
            <style>
                .custom-row-Cards {
                    border: none;
                }

                .card {
                    height: 100%;
                    width: auto;
                }

                .card-body {

                    flex-direction: column;
                    justify-content: center;
                }
            </style>


            <div id="content-wrapper" class="d-flex flex-column">
                <br>
                <br>
                <div class="container mt-0">
                    <div class="row" style="border: none !important;">
                        <div class="col-md-12">
                            <div class="row custom-row-Cards">
                                <div class="col-sm-12 col-md-4 mb-2">
                                    <!-- Members Count -->
                                    <div class="card" style="background-color: #FFB067;">
                                        <div class="card-body">
                                            <h6 class="card-title">Members</h6>
                                            <h5 class="text-center">
                                                <?php


                                                $query_account = "SELECT COUNT(*) AS total_count FROM member WHERE role_id='2'";

                                                $result_account = mysqli_query($conn, $query_account);

                                                if ($result_account) {
                                                    $row = mysqli_fetch_assoc($result_account);
                                                    $member_count = $row['total_count'];
                                                    echo $member_count;
                                                } else {
                                                    echo "Error executing query: " . mysqli_error($conn);
                                                }
                                                ?>


                                            </h5>


                                        </div>
                                    </div>
                                </div>


                                <div class="col-sm-12 col-md-4 mb-2">
                                    <!-- Updated Balance Total -->
                                    <div class="card" style="background-color: #FFED86;">
                                        <div class="card-body">
                                            <h6 class="card-title">Account Receivable</h6>
                                            <h5 class="text-center">

                                                <?php
                                                require '../conn/dbcon.php';

                                                // Query to get the total sum of the updated_balance column
                                                $query_balance = "SELECT SUM(updated_balance) AS total_balance FROM loan";

                                                $result_balance = mysqli_query($conn, $query_balance);

                                                if ($result_balance) {
                                                    $row = mysqli_fetch_assoc($result_balance);
                                                    $total_balance = $row['total_balance'];
                                                    echo number_format($total_balance, 2); // Format as needed
                                                } else {
                                                    echo "Error executing query: " . mysqli_error($conn);
                                                }
                                                ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>



                                <div class="col-sm-12 col-md-4 mb-2">
                                    <!-- Members Count -->
                                    <div class="card" style="background-color: #ADD8E6;">
                                        <div class="card-body">
                                            <h6 class="card-title">Reports</h6>
                                            <h5 class="text-center">
                                                <?php
                                                require '../conn/dbcon.php';

                                                $query_reports = "SELECT COUNT(*) AS total_count FROM report";

                                                $result_reports = mysqli_query($conn, $query_reports);

                                                if ($result_reports) {
                                                    $row = mysqli_fetch_assoc($result_reports);
                                                    $reports_count = $row['total_count'];
                                                    echo $reports_count;
                                                } else {
                                                    echo "Error executing query: " . mysqli_error($conn);
                                                }
                                                ?>
                                            </h5>


                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4 mb-2">
                                    <!-- Total Processing Fee -->
                                    <div class="card" style="background-color: #CEFFC9;">
                                        <div class="card-body">
                                            <h6 class="card-title">Total Processing Fee</h6>
                                            <h5 class="text-center">
                                                ₱

                                                <?php
                                                require '../conn/dbcon.php';

                                                // Modify the query to sum the 'processing_fee' column from the 'loan_charge' table
                                                $query_processing_fee = "SELECT SUM(annual_processing_fee) AS total_processing_fee FROM loan_summary_report";

                                                $result_processing_fee = mysqli_query($conn, $query_processing_fee);

                                                if ($result_processing_fee) {
                                                    $row = mysqli_fetch_assoc($result_processing_fee);
                                                    $total_processing_fee = $row['total_processing_fee'];
                                                    echo number_format($total_processing_fee, 2); // Display total with 2 decimal places
                                                } else {
                                                    echo "Error executing query: " . mysqli_error($conn);
                                                }
                                                ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4 mb-2">
                                    <!-- Total Loan Interest -->
                                    <div class="card" style="background-color: #ff8383;">
                                        <div class="card-body">
                                            <h6 class="card-title">Total Loan Interest</h6>
                                            <h5 class="text-center">
                                                ₱
                                                <?php
                                                require '../conn/dbcon.php';

                                                // Modify the query to sum the 'loan_interest' column from the 'loan_charge' table
                                                $query_loan_interest = "SELECT SUM(annual_loan_interest) AS total_loan_interest FROM loan_summary_report";

                                                $result_loan_interest = mysqli_query($conn, $query_loan_interest);

                                                if ($result_loan_interest) {
                                                    $row = mysqli_fetch_assoc($result_loan_interest);
                                                    $total_loan_interest = $row['total_loan_interest'];
                                                    echo number_format($total_loan_interest, 2); // Display total with 2 decimal places
                                                } else {
                                                    echo "Error executing query: " . mysqli_error($conn);
                                                }
                                                ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4 mb-2">
                                    <!-- Total Membership Fee -->
                                    <div class="card" style="background-color: #C1BBDD;">
                                        <div class="card-body">
                                            <h6 class="card-title">Total Membership Fee</h6>
                                            <h5 class="text-center">
                                                ₱
                                                <?php
                                                require '../conn/dbcon.php';

                                                // Modify the query to sum the 'membership_fee' column from the 'members' table
                                                $query_membership_fee = "SELECT SUM(membership_fee) AS total_membership_fee FROM member";

                                                $result_membership_fee = mysqli_query($conn, $query_membership_fee);

                                                if ($result_membership_fee) {
                                                    $row = mysqli_fetch_assoc($result_membership_fee);
                                                    $total_membership_fee = $row['total_membership_fee'];
                                                    echo number_format($total_membership_fee, 2); // Display total with 2 decimal places
                                                } else {
                                                    echo "Error executing query: " . mysqli_error($conn);
                                                }
                                                ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>



                                <!-- break -->




                            </div>
                        </div>
                    </div>

                    <!-- Row for chart -->


                    <div class="row custom-row-Cards py-0" style="border: none !important;">
                        <div class="col-sm-12 col-md-6 mb-2">
                            <div class="card mt-5 mb-auto">
                                <!-- First Card Content (Pie Chart 2) -->
                                <?php include '../function/piechart2.php'; ?>
                                <div id="chartContainer2" class="chartContainer py-5 mb-2"
                                    style="height: 250px; width: 100%;"></div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 mb-2">
                            <div class="card mt-5 mb-auto">
                                <!-- Second Card Content (Loan Balances) -->
                                <?php include '../function/piechart1.php'; ?>
                                <div id="chartContainer" class="chartContainer py-5 mb-2"
                                    style="height: 250px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>

                    <br>








                </div>


            </div>

            <!-- Footer Section -->
            <div class="footer-right d-flex justify-content-center align-items-end mt-2 pt-2 fw-bold"
                style="position: relative; background-color: #1d6325;">
                <pre
                    class="sidebar-footer-text text-light fw-bold text-center"> A capstone project designed and developed by <a href="developer.php" class="text-decoration-none text-light">TEAM AREA</a></pre>
            </div>

        </div>

    </div>


    <script src="../assets/canvasjschart/jquery.canvasjs.min.js"></script>
    <script src="../assets/canvasjschart/canvasjs.min.js"></script>


    <script>
        window.onload = function () {
            // Fetch gender counts from the PHP backend
            fetch('../function/piechart2.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json(); // Parse the JSON data from the PHP script
                })
                .then(genderData => {
                    // Transform the gender data into an array for chart consumption
                    var memberData = Object.keys(genderData).map(gender => ({
                        y: genderData[gender], // Number of members
                        label: gender // Gender (e.g., 'Male', 'Female')
                    }));

                    // Create the doughnut chart using CanvasJS
                    var memberChart = new CanvasJS.Chart("chartContainer2", {
                        theme: "light2",
                        animationEnabled: true,
                        title: {
                            text: "Gender Distribution"
                        },
                        data: [{
                            type: "doughnut",
                            indexLabel: "{label} - {y}",
                            yValueFormatString: "#,##0",
                            showInLegend: true,
                            legendText: "{label} : {y}",
                            dataPoints: memberData // Data points for the chart
                        }]
                    });

                    // Render the chart with the gender data
                    memberChart.render();
                })
                .catch(error => {
                    // Handle any errors in fetching data or rendering the chart
                    console.error('Error fetching data:', error);
                });



            // Loan balances chart
            var labels = ["0-20k", "21k-40k", "41k-60k", "61k-80k", "81k-100k"]; // Define custom labels

            var chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "Number of active loan with their balances" // Update title to reflect the data
                },
                axisX: {
                    title: "Updated Balance",
                    interval: 1, // Set the interval between each label
                    labelFormatter: function (e) {
                        return labels[e.value]; // Show custom labels
                    }
                },
                axisY: {
                    title: "Count",
                    interval: 10, // Set the interval between each label
                    tickLength: 0, // Hide tick marks
                    gridThickness: 0 // Hide grid lines
                },
                data: [{
                    type: "column",
                    yValueFormatString: "#,##0",
                    dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?> // Dynamically populate dataPoints array
                }]
            });

            // Render both charts
            chart.render(); // Render the loan balances chart
        };
    </script>






    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="../assets/canvasjsstockhart/canvasjs.stock.min.js"></script>
</body>

</html>