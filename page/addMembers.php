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

    <link rel="stylesheet" href="../assets/bootstrap/fontawesome/font/css/fontawesome-all.min.css">

    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="../assets/addMembers.css"> -->
    <link rel="stylesheet" href="../assets/style.css">

    <link rel="icon" href="../assets/pictures/cooplogo.jpg" type="image/x-icon"> <!-- Adjust the path accordingly -->
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"> <!-- Adjust the path accordingly -->
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/pictures/cooplogo.jpg">
    <!-- Adjust the path accordingly -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/pictures/cooplogo.jpg">
</head>

<body>


    <style>
        /* Custom CSS to remove border from container, row, and column */
        .custom-container,
        .custom-row,
        .custom-col {
            border: none;
        }

        #searchInput {
            width: 300px;
            margin-right: 10px;
        }

        .table th {
            background-color: #17a34a;
            color: white;
        }
    </style>
    <div class="wrapper">
        <aside id="sidebar" class="sticky-sidebar">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="lni lni-grid-alt"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="admin_dashboard.php" title="Home" style="font-weight: bold;">BASCPCC</a>
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
                    <a href="addMembers.php" class="sidebar-link active" title="Add Members"
                        onclick="admin_dashboard(); return false;">
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
                            <button id="addMembers" name="addMembers" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#addMemberModal">
                                <!-- Icon visible on all screen sizes -->

                                <i class="fa fa-user-plus"></i>

                                <!-- Text visible only on larger screens (576px and above) -->
                                <span class="button-text d-none d-sm-inline">Add Members</span>
                            </button>




                            <input type="text" id="searchInput" onkeyup="searchTable()"
                                placeholder="Search for names..." class="form-control mb-3"
                                style="font-size: 14px; padding: 5px;">
                            <span class="input-group-btn">
                                <button class="btn btn-success text-center" type="button"
                                    style="font-size: 14px; padding: 5px 10px;">
                                    <i class="lni lni-magnifier"></i>
                                </button>



                        </div>





                        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table id="memberTable" class="table table-bordered table-striped table-hover">
                                <thead class="thead-light" style="position: sticky; top: 0;">
                                    <tr>

                                        <th>First Name</th>
                                        <th>Middle Name</th>
                                        <th>Last Name</th>
                                        <th>Contact Number</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    require '../conn/dbcon.php';
                                    $query = "SELECT m.member_id, m.fname, m.mname, m.lname, m.membership_date, m.tin_number, m.contact_number, i.institute_name
                                            FROM member m
                                            INNER JOIN member_institute mi ON m.member_id = mi.member_id
                                            INNER JOIN institute i ON mi.institute_id = i.institute_id
                                            ORDER BY m.fname ASC";
                                    $result_query = $conn->query($query);

                                    if ($result_query->num_rows > 0) {
                                        while ($data = $result_query->fetch_assoc()) {
                                            ?>
                                            <tr>

                                                <td><?= $data['fname']; ?></td>
                                                <td><?= $data['mname']; ?></td>
                                                <td><?= $data['lname']; ?></td>
                                                <td><?= $data['contact_number']; ?></td>
                                                <td class="text-center">

                                                    <a href="viewMembers.php?id=<?= $data['member_id']; ?>"
                                                        class="btn btn-success btn-sm">View</a>
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

            <!-- Footer Section -->
            <div class="footer-right d-flex justify-content-center align-items-end mt-2 pt-2 fw-bold"
                style="position: relative; background-color: #1d6325;">
                <pre
                    class="sidebar-footer-text text-light fw-bold text-center"> A capstone project designed and developed by <a href="developer.php" class="text-decoration-none text-light">TEAM AREA</a></pre>
            </div>
        </div>
    </div>

    <!-- Modal Structure for Add Members -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #2e7d32; color: white;">
                    <h5 class="modal-title" id="addMemberModalLabel">Add Member</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../function/addMembersFunction.php" method="POST">
                        <!-- Input Group -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="fname" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="fname" name="fname"
                                        placeholder="Enter First Name" required
                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                </div>

                                <div class="mb-3">
                                    <label for="mname" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="mname" name="mname"
                                        placeholder="Enter Middle Name" required
                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                </div>

                                <div class="mb-3">
                                    <label for="lname" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lname" name="lname"
                                        placeholder="Enter Last Name" required
                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="number" class="form-control" id="age" name="age"
                                        placeholder="Enter Age" required
                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                </div>

                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required
                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="membersDate" class="form-label">Membership Date</label>
                                    <input type="date" class="form-control" id="membersDate" name="membersDate" required
                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="membership_fee" class="form-label">Membership Fee</label>
                                    <input type="number" class="form-control" id="membership_fee" name="membership_fee"
                                        placeholder=" Enter Membership Fee" required
                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                </div>

                                <div class="mb-3">
                                    <label for="tinNumber" class="form-label">Tin Number</label>
                                    <input type="text" class="form-control" id="tinNumber" name="tinNumber"
                                        placeholder="Enter Tin Number" required
                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                </div>

                                <div class="mb-3">
                                    <label for="contactNumber" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="contactNumber" name="contactNumber"
                                        placeholder="e.g. 09" required
                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        placeholder="Enter Username" required
                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Enter Email" required
                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="instiName" class="form-label">Institute</label>
                                    <?php
                                    require '../function/instituteSelect.php';
                                    ?>
                                    <select class="form-select" id="instiName" name="instiName" required>
                                        <option value="">Select Institute</option>
                                        <?php foreach ($instituteNames as $instituteName): ?>
                                            <option value="<?= $instituteName ?>"><?= $instituteName ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>


                        </div>

                        <button type="submit" id="addButton-id" name="addButton" class="btn"
                            style="background-color: #2e7d32; border: none; color: white; width: 100%; padding: 12px; margin-top: 10px;">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- for submit button preventing multiple entry bug -->
    <script>
        document.getElementById("addButton-id").addEventListener("click", function () {
            const addButton = this;

            // Disable the button to prevent multiple clicks
            if (!addButton.disabled) {

                addButton.textContent = "Submitting..."; // Change button text

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
        var input, filter, table, tr, td, i, j, txtValue, found;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("memberTable");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td");
            found = false; // Reset the match flag for each row

            // Loop through all cells in the current row
            for (j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        found = true; // Match found in this row
                        break;
                    }
                }
            }

            // Show or hide the row based on the match
            tr[i].style.display = found ? "" : "none";
        }
    }
</script>

    <script>
        const instiSelect = document.getElementById('instiName');

        // Expand the dropdown on click
        instiSelect.addEventListener('click', function () {
            this.setAttribute('size', '5'); // Set number of visible options
        });

        // Close the dropdown when a selection is made
        instiSelect.addEventListener('change', function () {
            setTimeout(() => {
                this.removeAttribute('size'); // Collapse dropdown after selection
            }, 0); // Timeout to allow for selection to register
        });

        // Close the dropdown when it loses focus
        instiSelect.addEventListener('blur', function () {
            this.removeAttribute('size'); // Reset to dropdown behavior on blur
        });
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


    <!-- Bootstrap Bundle with Popper -->
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="../assets/script.js"></script>


</body>

</html>