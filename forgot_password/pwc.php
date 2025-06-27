<?php
// Ensure session is started securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_message = isset($_GET['error']) ? urldecode($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Form</title>

    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

</head>
<style>
    .navbar {
        padding: 0.5rem 1rem;
    }

    .navbar-brand {
        font-weight: bold;
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .navbar-brand img {
        height: 40px;
        /* Adjust logo height */
        margin-right: 8px;
    }

    .navbar-brand .short-name {
        font-size: 20px;
        /* Adjust short name size */
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .navbar-brand img {
            height: 35px;
            /* Shrink the logo size on mobile */
        }

        .navbar-brand {
            font-size: 16px;
            justify-content: center;
            /* Center the brand logo and text */
            width: 100%;
        }

        /* Change to BASC for small screens */
        .navbar-brand span {
            display: none;
        }

        .navbar-brand .short-name {
            display: inline;
            font-size: 18px;
            /* Adjust BASC size for small screens */
        }
    }

    /* For larger screens, show the full name */
    @media (min-width: 769px) {
        .navbar-brand .short-name {
            display: none;
        }

        .navbar-brand span {
            display: inline;
            font-size: 22px;
            /* Adjust full name size for larger screens */
        }

        .navbar-brand {
            justify-content: flex-start;
            /* Default alignment on larger screens */
        }
    }
</style>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light">
        <div class="container-fluid d-flex justify-content-center align-items-center flex-wrap">
            <!-- Logo and Title -->
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../assets/pictures/basclogo.png" alt="Logo">
                <img src="../assets/pictures/cooplogo-cropted.png" alt="Logo" class="rounded-logo">
                <!-- Full name for larger screens, short name for smaller screens -->
                <span class="d-md-inline fw-bold">BULACAN AGRICULTURAL STATE COLLEGE</span>
                <span class="short-name fw-bold">BASC</span>
            </a>
        </div>
    </nav>

    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">


                    <div class="card">
                        <div class="card-header">
                            <h5>CHANGE PASSWORD</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="prc.php" method="POST">
                                <input type="hidden" name="password_token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
                                <div class="form-group mb-3">
                                    <label for="inputEmail" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" placeholder="Enter new email">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" name="new_password" placeholder="Enter new password">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirm password">
                                </div>
                                <div class="form-group mb-3">
                                    <button type="submit" name="update_password" class="btn btn-primary w-100 mb-3">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Link to locally hosted Bootstrap JS -->
    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>

</html>