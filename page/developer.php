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
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">

    <link rel="stylesheet" href="../assets/style.css">

    <link rel="icon" href="../assets/pictures/cooplogo.jpg" type="image/x-icon"> <!-- Adjust the path accordingly -->
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"> <!-- Adjust the path accordingly -->
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/pictures/cooplogo.jpg">
    <!-- Adjust the path accordingly -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/pictures/cooplogo.jpg">
    <style>
        /* Adjust this value as needed */


        .img-border {
            border: 5px solid maroon;
            /* Adjust thickness as needed */
            border-radius: 50%;
            /* Keep the circular shape */
            padding: 5px;
            /* Padding inside the border */
        }

        .card-title {
            font-family: "Times New Roman", Times, serif;
            font-weight: bold;
            color: maroon;
        }

        .card {
            flex-direction: column;
        }

        .card-img-left {
            margin: 0 auto;
        }

        @media (min-width: 768px) {
            .card {
                flex-direction: row;
            }

            .card-img-left {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <aside id="sidebar">
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
                    <a href="addMembers.php" class="sidebar-link" title="Add Members">
                        <i class="lni lni-user"></i>
                        <span>Add Members</span>
                    </a>
                </li>


                <li class="sidebar-item">
                    <a href="loan_data.php" class="sidebar-link" title="Add Loan">
                        <i class="lni lni-layout"></i>
                        <span>Update Balance</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="sendNotification.php" class="sidebar-link" title="Send Notification"
                        onclick="sendNotification(); return false;">
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



            <div id="content-wrapper" class="d-flex flex-column">
                <div class="container mt1">
                    <h2 class="text-center fw-bold" style="color: maroon; font-size: 2.2rem;">Developers</h2>
                    <p class="text-center" style="font-size: 1.2rem;">IT Students of the Institute of Engineering and
                        Applied Technology</p>
                    <div class="row">
                        <!-- Card 1 -->
                        <div class="col-12 col-md-6 mb-3">
                            <div class="card shadow d-flex flex-column flex-md-row"
                                style="max-width: 90%; margin: 0 auto;">
                                <!-- Use flex-column for small screens -->
                                <img src="../assets/pictures/aljon.png" class="card-img-top rounded-circle mt-5 mx-auto"
                                    alt="Aljon A. Macsino"
                                    style="border: 2px solid maroon;border-radius: 50%; overflow: hidden; width: 130px; height: 130px; margin: 0 auto; display: flex; justify-content: center; align-items: center;">

                                <div class="card-body" style="position: relative;">
                                    <div
                                        style="background-image: url('../assets/pictures/logoieat.jpg'); background-size: 50%; background-repeat: no-repeat; background-position: center; position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; z-index: 0;">
                                    </div>
                                    <div style="position: relative; z-index: 1;">
                                        <h5 class="card-title" style="font-size: calc(1rem + 0.4vw);">Aljon A. Macsino
                                        </h5>
                                        <p class="card-text" style="font-size: calc(0.8rem + 0.4vw);">
                                            <b>Visual Graphics</b><br>
                                            <span
                                                style="font-family: 'Times New Roman', Times, serif; font-size: calc(0.9rem + 0.4vw);">
                                                Bulacan Agricultural State College</span><br>
                                            San Ildefonso, Bulacan, Philippines
                                        </p>
                                        <p class="card-text" style="font-size: calc(0.8rem + 0.4vw);">
                                            aljonmacsino.basc@gmail.com<br>0947 228 8621
                                        </p>
                                        <div class="d-flex justify-content-start">
                                            <a href="https://www.facebook.com/AljonMacsino" target="_blank"
                                                class="me-2">
                                                <img src="../assets/pictures/fb.png" alt="Facebook"
                                                    style="width: 25px; height: 25px;">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Card 2 -->
                        <div class="col-12 col-md-6 mb-3">
                            <div class="card shadow d-flex flex-column flex-md-row"
                                style="max-width: 90%; margin: 0 auto;">
                                <!-- Changed to flex-column for small screens -->
                                <img src="../assets/pictures/raymond.png"
                                    class="card-img-top rounded-circle mt-5 mx-auto" alt="Raymond E. Gonzales"
                                    style="border: 2px solid maroon; border-radius: 50%; overflow: hidden; width: 130px; height: 130px; margin: 0 auto; display: flex; justify-content: center; align-items: center;">
                                <div class="card-body" style="position: relative;">
                                    <div
                                        style="background-image: url('../assets/pictures/logoieat.jpg'); background-size: 50%; background-repeat: no-repeat; background-position: center; position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; z-index: 0;">
                                    </div>
                                    <div style="position: relative; z-index: 1;">
                                        <h5 class="card-title" style="font-size: calc(1rem + 0.4vw);">Raymond E.
                                            Gonzales</h5>
                                        <p class="card-text" style="font-size: calc(0.8rem + 0.4vw);"><b>Main
                                                Developer</b><br>
                                            <span
                                                style="font-family: 'Times New Roman', Times, serif; font-size: calc(0.9rem + 0.4vw);">
                                                Bulacan Agricultural State College</span><br>
                                            San Miguel, Bulacan, Philippines
                                        </p>
                                        <p class="card-text" style="font-size: calc(0.8rem + 0.4vw);">
                                            raymondgonzales.basc@gmail.com<br>0915 720 4510
                                        </p>
                                        <div class="d-flex justify-content-start">
                                            <a href="https://www.facebook.com/monmon.9/" target="_blank" class="me-2">
                                                <img src="../assets/pictures/fb.png" alt="Facebook"
                                                    style="width: 25px; height: 25px;">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Card 3 -->
                        <div class="col-12 col-md-6 mb-3">
                            <div class="card shadow d-flex flex-column flex-md-row"
                                style="max-width: 90%; margin: 0 auto;">
                                <!-- Changed to flex-column for small screens -->
                                <img src="../assets/pictures/earl.png" class="card-img-top rounded-circle mt-5 mx-auto"
                                    alt="Earl Gerald D. Domingo"
                                    style="border: 2px solid maroon; border-radius: 50%; overflow: hidden; width: 130px; height: 130px; margin: 0 auto; display: flex; justify-content: center; align-items: center;">
                                <div class="card-body" style="position: relative;">
                                    <div
                                        style="background-image: url('../assets/pictures/logoieat.jpg'); background-size: 50%; background-repeat: no-repeat; background-position: center; position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; z-index: 0;">
                                    </div>
                                    <div style="position: relative; z-index: 1;">
                                        <h5 class="card-title" style="font-size: calc(1rem + 0.4vw);">Earl Gerald D.
                                            Domingo</h5>
                                        <p class="card-text" style="font-size: calc(0.8rem + 0.4vw);"><b>
                                                Developer / Data Analyst</b><br>
                                            <span
                                                style="font-family: 'Times New Roman', Times, serif; font-size: calc(0.9rem + 0.4vw);">
                                                Bulacan Agricultural State College</span><br>
                                            San Miguel, Bulacan, Philippines
                                        </p>
                                        <p class="card-text" style="font-size: calc(0.7rem + .4vw);">
                                            earlgeralddomingo.basc@gmail.com<br>0938 819 2622
                                        </p>
                                        <div class="d-flex justify-content-start">
                                            <a href="https://www.facebook.com/domingo.05.cavs.1" target="_blank"
                                                class="me-2">
                                                <img src="../assets/pictures/fb.png" alt="Facebook"
                                                    style="width: 25px; height: 25px;">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4 -->
                        <div class="col-12 col-md-6 mb-3">
                            <div class="card shadow d-flex flex-column flex-md-row"
                                style="max-width: 90%; margin: 0 auto;">
                                <!-- Changed to flex-column for small screens -->
                                <img src="../assets/pictures/aubrey.png"
                                    class="card-img-top rounded-circle mt-5 mx-auto" alt="Aubrey Joy F. Sanchez"
                                    style="border: 2px solid maroon; border-radius: 50%; overflow: hidden; width: 130px; height: 130px; margin: 0 auto; display: flex; justify-content: center; align-items: center;">

                                <div class="card-body" style="position: relative;">
                                    <div
                                        style="background-image: url('../assets/pictures/logoieat.jpg'); background-size: 50%; background-repeat: no-repeat; background-position: center; position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; z-index: 0;">
                                    </div>
                                    <div style="position: relative; z-index: 1;">
                                        <h5 class="card-title" style="font-size: calc(1rem + 0.4vw);">Aubrey Joy F.
                                            Sanchez</h5>
                                        <p class="card-text" style="font-size: calc(0.8rem + 0.4vw);"><b>Project
                                                Manager</b><br>
                                            <span
                                                style="font-family: 'Times New Roman', Times, serif; font-size: calc(0.9rem + 0.4vw);">
                                                Bulacan Agricultural State College</span><br>
                                            San Ildefonso, Bulacan, Philippines
                                        </p>
                                        <p class="card-text" style="font-size: calc(0.8rem + 0.4vw);">
                                            sanchezaubreyjoyf.basc@gmail.com<br>0931 941 6056
                                        </p>
                                        <div class="d-flex justify-content-start">
                                            <a href="https://www.facebook.com/aubrey.o.sanchez" target="_blank"
                                                class="me-2">
                                                <img src="../assets/pictures/fb.png" alt="Facebook"
                                                    style="width: 25px; height: 25px;">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                    </div>
                    <h6 class="d-flex justify-content-center" style="font-size: 0.9rem;">Copyright ©2024 . All Rights
                        Reserved | A.Macsino | R.Gonzales | E.Domingo | A.Sanchez</h6>
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



    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../assets/script.js"></script>
</body>

</html>