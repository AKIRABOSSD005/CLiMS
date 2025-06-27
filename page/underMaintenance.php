<?php
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['email']) || !isset($_SESSION['username']) || $_SESSION['role_id'] != 1) {
    // If not logged in or not an admin, redirect to the login page
    header("Location: user_dashboard.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/pictures/cooplogo.jpg" type="image/x-icon"> <!-- Adjust the path accordingly -->
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"> <!-- Adjust the path accordingly -->
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/pictures/cooplogo.jpg">
    <!-- Adjust the path accordingly -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/pictures/cooplogo.jpg">

    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">

    <link rel="stylesheet" href="../assets/bootstrap/font/lineicons.css">

    <link rel="stylesheet" href="../assets/bootstrap/fontawesome/font/css/fontawesome-all.min.css">
    <title>Maintenance</title>
</head>


<style>
/* Under Maintenance Styling */
.maintenance-card {
    background: rgba(34, 34, 34, 0.9); /* Darker background with less transparency */
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 0 15px rgba(0, 255, 150, 0.6);
    text-align: center;
    color: #ffffff;
}

.maintenance-card h1 {
    font-size: 2.5rem;
    font-weight: bold;
    color: #1abc9c; /* Softer green for better readability */
    text-shadow: 0px 0px 10px #1abc9c, 0px 0px 20px #16a085;
}

.maintenance-card p {
    font-size: 1.2rem;
    color: #cccccc; /* Light gray for readability against the dark background */
}

.btn-maintenance {
    margin-top: 20px;
    background-color: #1abc9c;
    color: #ffffff; /* White text for better contrast */
    padding: 12px 24px;
    font-size: 1rem;
    border-radius: 25px;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.btn-maintenance:hover {
    background-color: #16a085;
    transform: translateY(-5px);
}

    </style>

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
                                    class="nav-icon pe-md-0 dropdown-toggle" id="navbarDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="name"> <?= $admin_info['fname'] ?? ''; ?>
                                        <?= $admin_info['mname'] ?? ''; ?> <?= $admin_info['lname'] ?? ''; ?></span>
                                    <img src="<?= !empty($admin_info['pictures']) ? '../assets/pictures/memberPictures/' . $admin_info['pictures'] : '../assets/pictures/account.png'; ?>"
                                        class="avatar img-fluid rounded-circle" alt="">
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end mt-2" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item"
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

            <style>
                .container,
                .row,
                .col {
                    border: none;
                }
            </style>

            <div id="content-wrapper" class="d-flex flex-column">
                <div class="container">
                    <br>
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Under Maintenance Section -->
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="maintenance-card col-md-8 col-lg-6 mx-auto">
                                    <h1>This page is Under Maintenance</h1>
                                    <p>We're currently working on some improvements. Please check back later. We
                                        apologize for any inconvenience caused.</p>
                                    <a href="admin_dashboard.php" class="btn btn-maintenance">Go to Dashboard</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-right d-flex justify-content-center align-items-end mt-2 pt-2 fw-bold"
                style="position: relative; background-color: #1d6325;">
                <pre class="sidebar-footer-text text-light fw-bold text-center"> A capstone project is design and develop by <a href="developer.php" class="text-decoration-none text-light">TEAM AREA</a></pre>
            </div>
        </div>
    </div>





    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../assets/script.js"></script>
</body>

</html>