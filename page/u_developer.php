<?php
session_start();

// Check if user is logged in, if not redirect to login page
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
            /* Set font */
            font-weight: bold;
            /* Make text bold */
            color: maroon;
            /* Change text color to maroon */
        }

        .card {
            flex-direction: column;
            /* Stack image and content */
        }

        .card-img-left {
            margin: 0 auto;
            /* Center the image */
        }

        @media (min-width: 768px) {
            .card {
                flex-direction: row;
                /* Side by side on larger screens */
            }

            .card-img-left {
                margin: 0;
                /* Reset margin on larger screens */
            }
        }
    </style>
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
                <?php $encryptedMemberId = encryptData($member_info['member_id'], ENCRYPTION_KEY);?>
                    <a href="user_dashboard.php?member_id=<?php echo $encryptedMemberId; ?>" class="sidebar-link"
                        title="Dashboard" onclick="window.location.reload();" class="sidebar-link" title="Dashboard">
                        <i class="lni lni-bar-chart"></i>
                        <span>Dashboard</span>
                    </a>
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
                                    style="border: 2px solid maroon; border-radius: 50%; overflow: hidden; width: 130px; height: 130px; margin: 0 auto; display: flex; justify-content: center; align-items: center;">

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
                                <img src="../assets/pictures/earl.png"
                                    class="card-img-top rounded-circle mt-5 mx-auto" alt="Earl Gerald D. Domingo"
                                    style="border: 2px solid maroon; border-radius: 50%; overflow: hidden; width: 130px; height: 130px; margin: 0 auto; display: flex; justify-content: center; align-items: center;">
                                <div class="card-body" style="position: relative;">
                                    <div
                                        style="background-image: url('../assets/pictures/logoieat.jpg'); background-size: 50%; background-repeat: no-repeat; background-position: center; position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; z-index: 0;">
                                    </div>
                                    <div style="position: relative; z-index: 1;">
                                        <h5 class="card-title" style="font-size: calc(1rem + 0.4vw);">Earl Gerald D. Domingo</h5>
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
                                            <a href="https://www.facebook.com/domingo.05.cavs.1" target="_blank" class="me-2">
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
            <div class="footer-right d-flex justify-content-center align-items-end mt-2 pt-2 "
     style="background-color: #1d6325;">
    <span class="sidebar-footer-text text-light text-center" style="white-space: nowrap;">
        A capstone project designed and developed by TEAM AREA
    </span>
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
                <div class="modal-header" style="background-color: #2e7d32; color: white; padding: 20px;">
                    <h5 class="modal-title" id="editMemberModalLabel">Edit Account Information</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <form action="../function/editMembersFunction.php" method="POST" class="updateInfoMembers">
                        <?php $encryptedMemberId = encryptData($member_info['member_id'], ENCRYPTION_KEY);?>
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
    <div class="modal fade" id="passwordAccountModal" tabindex="-1" aria-labelledby="passwordAccountModalLabel"
        aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #2e7d32; color: white; padding: 20px;">
                    <h5 class="modal-title" id="passwordAccountModal" style="color: white;">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="background-color: #99cc99; color: #004d00;"></button>
                </div>
                <div class="modal-body">
                    <form action="../function/editMembersFunction.php" method="POST" class="updatePassword">
                        <div class="mb-3">
                        <?php $encryptedMemberId = encryptData($member_info['member_id'], ENCRYPTION_KEY);?>
                        <input type="hidden" id="member_id" name="member_id" value="<?php echo $encryptedMemberId; ?>">
                            <label for="oldPassword" class="form-label" style="color: #004d00;">Old Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="oldpassWord" name="oldpassword" required
                                    style="border: 1px solid #004d00;">
                                <button class="btn" type="button" id="toggleOldPassword"
                                    style="background-color: #99cc99; color: #004d00; border: 1px solid #004d00;">Show</button>
                            </div>
                            <label for="newpassWord" class="form-label" style="color: #004d00;">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="newpassWord" name="newpassword" required
                                    style="border: 1px solid #004d00;">
                                <button class="btn" type="button" id="toggleNewPassword"
                                    style="background-color: #99cc99; color: #004d00; border: 1px solid #004d00;">Show</button>
                            </div>
                        </div>
                        <button type="submit" name="updatepassword" class="btn"
                            style="background-color: #2e7d32; border: none; color: white; width: 100%; padding: 12px; margin-top: 20px;">Change
                            Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header" style="background-color: #004d40; color: white; border-radius: 5px 5px 0 0;">
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeProfilePictureModalLabel">Change Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add a form for uploading a new profile picture -->
                    <form action="../function/upload_profile_picture.php" method="post" enctype="multipart/form-data">
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

    

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/script.js"></script>
    <script src="../assets/viewPdf.js"></script>
    <script src="../assets/viewPassword.js"></script>

    
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