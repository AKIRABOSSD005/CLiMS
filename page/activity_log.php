<?php
session_start();



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
                    <a href="viewReports.php" class="sidebar-link" title="Reports">
                        <i class="lni lni-envelope"></i>
                        <span>Reports</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="activity_log.php" class="sidebar-link active" title="Reports">
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
                            <div class="table-responsive text-center" style="max-height: 400px; overflow-y: auto;">
                                <table id="memberTable" class="table table-bordered mt-2 table-striped">
                                    <thead class="thead-light" style="position: sticky; top: 0;">
                                        <tr>
                                            <th>LOG_ID</th>



                                            <th>Log Activity</th>
                                            <th>Log Activity Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        require '../conn/dbcon.php';

                                        // Query to join members and activity_log tables, including the middle name
                                        $query = "SELECT CONCAT(m.fname, ' ', COALESCE(m.mname, ''), ' ', m.lname) AS full_name, 
                                        a.log_id, a.activity_type, a.activity_time FROM activity_log a 
                                        INNER JOIN member m 
                                        ON a.member_id = m.member_id 
                                        ORDER BY 
                                        a.activity_time DESC";
                                        $result_query = $conn->query($query);

                                        if ($result_query->num_rows > 0) {
                                            while ($log = $result_query->fetch_assoc()) {
                                                // Convert the activity_time to a formatted date string
                                                $dateObject = DateTime::createFromFormat('Y-m-d H:i:s', $log['activity_time']);
                                                $formatted_date = $dateObject ? $dateObject->format('F j, Y, g:i A') : 'Invalid date'; // Handle invalid date
                                        ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($log['log_id']); ?></td>

                                                    <td><?= htmlspecialchars($log['activity_type']); ?> <strong><?= htmlspecialchars($log['full_name']); ?></strong></td>
                                                    <td><?= htmlspecialchars($formatted_date); ?></td>
                                                </tr>
                                        <?php
                                            }
                                        } else {
                                            echo '<tr><td colspan="4" class="text-center">No data available</td></tr>'; // Adjusted column span to 4
                                        }
                                        ?>

                                    </tbody>
                                </table>

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


    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/bootstrap/js/sweetalert.min.js"></script>
    <script src="../assets/script.js"></script>
</body>

</html>