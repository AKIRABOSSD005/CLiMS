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
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="../assets/bootstrap/font/lineicons.css">
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">

    <link rel="icon" href="../assets/pictures/cooplogo.jpg" type="image/x-icon"> 
            <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon">
            <link rel="icon" type="image/png" sizes="32x32" href="../assets/pictures/cooplogo.jpg">
            <link rel="icon" type="image/png" sizes="16x16" href="../assets/pictures/cooplogo.jpg">
</head>
<style>
    #searchInput {
        width: 100px;
        margin-right: 10px;
    }

    .table th {
        background-color: #17a34a;
        color: white;
    }
</style>

<body>
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require("../conn/dbcon.php");

    if (isset($_GET['id'])) {
        $id = mysqli_real_escape_string($conn, $_GET['id']);
        $query = "SELECT * FROM member WHERE member_id = '$id'";
        $result_query = mysqli_query($conn, $query);
        if ($result_query && mysqli_num_rows($result_query) > 0) {
            $admin_info = mysqli_fetch_array($result_query);
        }
    }
    ?>

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
                    <a href="admin_dashboard.php" class="sidebar-link active" title="Dashboard">
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
                    <a href="loan_data.php" class="sidebar-link" title="Send Notification"
                        onclick="loan_data(); return false;">
                        <i class="lni lni-layout"></i>
                        <span>Add Loan</span>
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
            <nav class="navbar navbar-expand px-4 py-2 custom-navbar">
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
                            <a href="admin_profile.php?id=<?php echo $admin_info['member_id']; ?>"
                                class="nav-icon pe-md-0 dropdown-toggle" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="name"> <?= $admin_info['fname'] ?? ''; ?>
                                    <?= $admin_info['mname'] ?? ''; ?> <?= $admin_info['lname'] ?? ''; ?></span>
                                <img src="<?= !empty($admin_info['pictures']) ? '../assets/pictures/memberPictures/' . $admin_info['pictures'] : '../assets/pictures/account.png'; ?>"
                                    class="avatar img-fluid rounded-circle" alt="">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end mt-2" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="#addAdminModal" data-bs-toggle="modal">Add Admin</a>
                                </li>
                                <li><a class="dropdown-item" href="../function/logoutFunction.php">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Content Wrapper -->
            <div id="content-wrapper" class="d-flex flex-column">
                <div class="container mt-1">
                    <div class="row" style="border: none !important;">
                        <div class="col-md-12 mt-1">
                            <!-- Profile Info Card -->
                            <div class="card mb-0" style="border: 1px solid #17a34a; border-radius: 10px;">
                                <div class="card-body">
                                    <div class="row">
                                        <!-- First Column: Profile Image and Username -->
                                        <div class="col-md-4 text-center">
                                            <a href="#" data-bs-toggle="modal"
                                                data-bs-target="#changeProfilePictureModal">
                                                <img src="<?= !empty($admin_info['pictures']) ? '../assets/pictures/memberPictures/' . $admin_info['pictures'] : '../assets/pictures/account.png'; ?>"
                                                    class="rounded img-fluid" alt="Profile Image"
                                                    style="width: 150px; height: 150px; object-fit: cover; border: 2px solid #17a34a; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 15px;">
                                            </a>
                                            <h6 style="font-weight: bold; color: #17a34a;">
                                                <?= $admin_info['username']; ?>
                                            </h6>
                                        </div>

                                        <!-- Second Column: Member Information -->
                                        <div class="col-md-8">
                                            <div class="row mb-2">
                                                <div class="col-sm-6"><strong style="color: #17a34a;">First
                                                        Name:</strong></div>
                                                <div class="col-sm-6"><?= $admin_info['fname']; ?></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-6"><strong style="color: #17a34a;">Middle
                                                        Name:</strong></div>
                                                <div class="col-sm-6"><?= $admin_info['mname']; ?></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-6"><strong style="color: #17a34a;">Last
                                                        Name:</strong></div>
                                                <div class="col-sm-6"><?= $admin_info['lname']; ?></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-6"><strong style="color: #17a34a;">Contact
                                                        Number:</strong></div>
                                                <div class="col-sm-6"><?= $admin_info['contact_number']; ?></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-6"><strong style="color: #17a34a;">Email
                                                        Address:</strong></div>
                                                <div class="col-sm-5"><?= $admin_info['email']; ?></div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="button-loan my-1 py-1 text-center d-flex flex-column flex-md-row justify-content-center">
                                    <a href="#updateAccountModal" data-bs-toggle="modal" class="mx-2 my-1"
                                        style="color: #17a34a; font-weight: bold;">
                                        Account Settings <i class="lni lni-cog"></i>
                                    </a>
                                    <a href="#updatePasswordModal" data-bs-toggle="modal" class="mx-2 my-1"
                                        style="color: #17a34a; font-weight: bold;">
                                        Change Password <i class="lni lni-cog"></i>
                                    </a>
                                </div>


                            </div>
                        </div>
                    </div>

                    <br>
                    <h3>LIST OF ADMIN ACCOUNTS</h3>


                    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: #2e7d32; color: white;">
                                    <h5 class="modal-title" id="addAdminModalLabel">Add Admin</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="../function/addAdminFunction.php" method="POST">
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
                                                    <label for="contactNumber" class="form-label">Contact Number</label>
                                                    <input type="text" class="form-control" id="contactNumber"
                                                        name="contactNumber" placeholder="e.g. 09" required
                                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                </div>


                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="username" class="form-label">Username</label>
                                                    <input type="text" class="form-control" id="username"
                                                        name="username" placeholder="Enter Username" required
                                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                </div>

                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email Address</label>
                                                    <input type="email" class="form-control" id="email" name="email"
                                                        placeholder="Enter Email" required
                                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                </div>

                                                <div class="mb-3">
                                                    <label for="password" class="form-label">Password</label>
                                                    <input type="password" class="form-control" id="password"
                                                        name="password" placeholder="Enter Password" required
                                                        style="border: 1px solid #388e3c; border-radius: 4px; padding: 10px;">
                                                </div>

                                            </div>
                                        </div>



                                        <button type="submit" name="addAdmin" class="btn"
                                            style="background-color: #2e7d32; border: none; color: white; width: 100%; padding: 12px; margin-top: 10px;">Add
                                            Admin</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">


                        <div class="col-lg-12">
                            <div class="d-flex justify-content-between">


                                <div class="input-group mb-2" style="max-width: 500px;">
                                    <input type="text" id="searchInput" onkeyup="searchTable()"
                                        placeholder="Search for names..." class="form-control"
                                        style="font-size: 14px; padding: 5px;">
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button"
                                            style="font-size: 12px; padding: 5px 10px;">
                                            <i class="lni lni-magnifier"></i>
                                        </button>
                                    </span>
                                </div>


                            </div>


                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                <table id="memberTable" class="table table-bordered table-striped table-hover">
                                    <thead class="thead-light" style="position: sticky; top: 0;">
                                        <tr>

                                            <th>First Name</th>
                                            <th>Middle Name</th>
                                            <th>Last Name</th>
                                            <th>Contact Number</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        require '../conn/dbcon.php';
                                        $query = "SELECT m.member_id, m.fname, m.mname, m.lname, m.age, m.contact_number
                                            FROM member m
                                            WHERE role_id ='1'
                
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


                    <!-- Modal for Account Settings -->
                    <div class="modal fade" id="updateAccountModal" tabindex="-1"
                        aria-labelledby="updateAccountModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg"> <!-- Changed to 'modal-lg' for a wider modal -->
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="editMemberModalLabel">Edit Account Information</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="../function/editMembersFunction.php" method="POST"
                                        class="updateInfoMembers">
                                        <div class="mb-3">
                                            <input type="hidden" id="member_id" name="member_id"
                                                value="<?php echo $admin_info['member_id']; ?>">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="fname" class="form-label">First Name</label>
                                                    <input type="text" class="form-control" id="fname" name="fname"
                                                        value="<?php echo $admin_info['fname']; ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="mname" class="form-label">Middle Name</label>
                                                    <input type="text" class="form-control" id="mname" name="mname"
                                                        value="<?php echo $admin_info['mname']; ?>" required>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="lname" class="form-label">Last Name</label>
                                                    <input type="text" class="form-control" id="lname" name="lname"
                                                        value="<?php echo $admin_info['lname']; ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="contactNumber" class="form-label">Contact Number</label>
                                                    <input type="text" class="form-control" id="contactNumber"
                                                        name="contactNumber"
                                                        value="<?php echo $admin_info['contact_number']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="userName" class="form-label mt-3">Username</label>
                                                    <input type="text" class="form-control" id="userName"
                                                        name="username" value="<?php echo $admin_info['username']; ?>"
                                                        required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="eMail" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="eMail" name="email"
                                                        value="<?php echo $admin_info['email']; ?>" required>
                                                </div>
                                            </div>


                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="updateAdminInfo" class="btn btn-success">Save
                                                Changes</button>
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Modal for changing password -->
                    <div class="modal fade" id="updatePasswordModal" tabindex="-1" aria-labelledby="updatePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="updatePasswordModalLabel">Change Admin Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../function/editMembersFunction.php" method="POST" class="updateInfoMembers">
                    <input type="hidden" id="member_id" name="member_id" value="<?php echo $admin_info['member_id']; ?>">

                    <div class="d-flex flex-column align-items-center gy-3"> <!-- Center all input fields -->
                        <!-- Old Password -->
                        <div class="col-12 col-md-6 mb-3"> <!-- Adjust width with col-md-6 -->
                            <label for="oldPassword" class="form-label">Old Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="oldpassWord" name="oldpassword" autocomplete="new-password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleOldPassword">Show</button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="col-12 col-md-6 mb-3">
                            <label for="newpassWord" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="newpassWord" name="newpassword" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">Show</button>
                            </div>
                        </div>

                        <!-- Re-enter New Password -->
                        <div class="col-12 col-md-6 mb-3">
                            <label for="reenternewpassWord" class="form-label">Re-enter New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="reenternewpassWord" name="ReEnternewpassword" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleReEnterNewPassword">Show</button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer mt-3">
                        <button type="submit" name="updateAdminPassword" class="btn btn-success">Change Password</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


                    
                </div>
            </div>
        </div>

        <!-- Modal for changing profile picture -->
        <div class="modal fade" id="changeProfilePictureModal" tabindex="-1"
                        aria-labelledby="changeProfilePictureModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="changeProfilePictureModalLabel">Change Profile Picture
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Add a form for uploading a new profile picture -->
                                    <form action="../function/admin_upload_profile.php" method="post"
                                        enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="profilePicture" class="form-label">Choose a new profile
                                                picture:</label>
                                            <input type="file" class="form-control" id="profilePicture"
                                                name="profilePicture" accept="image/*">
                                        </div>
                                        <button type="submit" name="uploadAdminProfile"
                                            class="btn btn-primary">Upload</button>
                                    </form>
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


    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/script.js"></script>
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
        document.getElementById('toggleOldPassword').addEventListener('click', function() {
            togglePasswordVisibility('oldpassWord', 'toggleOldPassword');
        });

        document.getElementById('toggleNewPassword').addEventListener('click', function() {
            togglePasswordVisibility('newpassWord', 'toggleNewPassword');
        });

        document.getElementById('toggleReEnterNewPassword').addEventListener('click', function() {
            togglePasswordVisibility('reenternewpassWord', 'toggleReEnterNewPassword');
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

    <script>
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
</body>

</html>