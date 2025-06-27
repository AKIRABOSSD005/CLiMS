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
        .table th {
            background-color: #17a34a;
            color: white;
        }

        .table-responsive {
            overflow-x: auto;
            /* Enable horizontal scrolling */
            -webkit-overflow-scrolling: touch;
            /* Enable smooth scrolling on iOS devices */
        }
        .spinning-logo {
            width: 100px;
            height: 100px;
            animation: spin 2s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
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
                    <a href="sendNotification.php" class="sidebar-link active" title="Send Notification"
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
                    // require '../conn/dbcon.php';
                    
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



            <br>
            <div id="content-wrapper" class="d-flex flex-column">
                <div class="table-responsive">


                    <div class="col-lg-12">
                        <div class="d-flex justify-content-between">
                            <input type="text" id="searchInput" onkeyup="searchTable()"
                                placeholder="Search for names..." class="form-control">
                            <span class="input-group-btn">
                                <button class="btn btn-success" type="button">
                                    <i class="lni lni-magnifier"></i>
                                </button>
                            </span>
                        </div>
                       
                        <!-- Trigger Button -->
                        <div class="d-flex justify-content-center my-3">
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#confirmSendEmailModal">
                                Send Email to All Members
                            </button>
                        </div>
                        
                        <!-- Modal -->
                        <div class="modal fade" id="confirmSendEmailModal" tabindex="-1" aria-labelledby="confirmSendEmailModalLabel" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="confirmSendEmailModalLabel">Confirm Email Notification</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                Are you sure you want to send email notifications to all members?
                              </div>
                              <div class="modal-footer">
                                <form id="sendToAllForm" action="../sendnotification/send_notificationFunction.php" method="POST">
                                  <input type="hidden" name="sendEmailToAll" value="1">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                  <button type="submit" class="btn btn-success" onclick="showLoading()">Yes</button>
                                </form>
                              </div>
                            </div>
                          </div>
                     </div>
                       
                        <div class="table-responsive" style="max-height: 395px; overflow-y: auto; overflow-x: auto;">
                            <table id="memberTable" class="table table-bordered table-striped"
                                style="width: 100%; min-width: 600px;"> <!-- Adjust min-width as necessary -->
                                <thead style="position: sticky; top: 0; background-color: white;">
                                    <tr>
                                        <th>First Name</th>
                                        <th>Middle Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    require '../conn/dbcon.php';
                                    $query = "SELECT * FROM member WHERE role_id='2' ORDER BY fname ASC";
                                    $result_query = $conn->query($query);
                                    if ($result_query->num_rows > 0) {
                                        while ($data = $result_query->fetch_assoc()) {
                                            ?>
                                            <tr>
                                                <td><?= $data['fname']; ?></td>
                                                <td><?= $data['mname']; ?></td>
                                                <td><?= $data['lname']; ?></td>
                                                <td><?= $data['email']; ?></td>
                                                <td style="display: block; gap:5px;">
                                                    <form action="../sendnotification/send_notificationFunction.php"
                                                        method="POST">
                                                        <input type="hidden" name="member_id"
                                                            value="<?= $data['member_id']; ?>">
                                                         <button type="submit" id="sendEmail-id" name="sendEmail"
                                                            class="btn btn-success w-100 mb-2">Send Email</button>


                                                    </form>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
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


    <!-- for submit button preventing multiple send bug -->
    <script>
        document.getElementById("sendEmail-id").addEventListener("click", function () {
            const addButton = this;

            // Disable the button to prevent multiple clicks
            if (!addButton.disabled) {

                addButton.textContent = "Sending..."; // Change button text

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
    <!-- Bootstrap Bundle with Popper -->
    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../assets/script.js"></script>
    
    <!-- Loading Overlay -->
<div id="loadingOverlay" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255,255,255,0.9);
    z-index: 1055;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    text-align: center;
">
    <img src="../assets/pictures/loading.png" alt="Loading..."  class="spinning-logo">
    <p style="font-size: 18px; color: #333;">Sending email to all members...</p>
</div>
<script>
function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    overlay.style.display = 'flex';
    document.body.style.pointerEvents = 'none'; // disable interaction
}
</script>
</body>

</html>