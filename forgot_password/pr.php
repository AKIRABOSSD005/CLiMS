<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <title>Forgot Password</title>
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
            height: 24px;
            margin-right: 8px;
        }

        /* Adjust the form control sizes based on screen size */
        .form-inline .form-control {
            font-size: 14px;
            padding: 0.3rem 0.6rem;
        }

        @media (max-width: 576px) {

            /* Extra small (xs) */
            .form-inline input,
            .form-inline button {
                width: auto;
                /* Maintain width without stacking */
                max-width: 90px;
                /* Make inputs narrower */
                font-size: 12px;
                /* Reduce font size for smaller screens */
                padding: 0.2rem 0.4rem;
                /* Smaller padding for mobile */
            }

            .navbar-brand img {
                height: 20px;
            }

            .navbar-brand {
                justify-content: center;
                width: 100%;
            }

            .navbar-brand span {
                display: none;
            }

            .navbar-brand .short-name {
                display: inline;
                font-size: 14px;
            }

            /* Center the form in the navbar on mobile devices */
            .form-inline {
                justify-content: center;
            }


        }


        @media (min-width: 577px) and (max-width: 768px) {

            /* Small (s) */
            .form-inline input,
            .form-inline button {
                font-size: 13px;
                padding: 0.25rem 0.5rem;
                width: 120px;
            }


        }

        @media (min-width: 769px) and (max-width: 992px) {

            /* Medium (m) */
            .form-inline input,
            .form-inline button {
                font-size: 14px;
                padding: 0.3rem 0.6rem;
                width: 150px;
            }

            .forgot-password {
                font-size: 15px;
                /* Larger font size */
            }
        }

        @media (min-width: 993px) and (max-width: 1200px) {

            /* Large (lg) */
            .form-inline input,
            .form-inline button {
                font-size: 15px;
                padding: 0.35rem 0.7rem;
                width: 180px;
            }

            .forgot-password {
                font-size: 16px;
                /* Even larger font size */
            }
        }

        @media (min-width: 1201px) {

            /* Extra large (xl) */
            .form-inline input,
            .form-inline button {
                font-size: 16px;
                padding: 0.4rem 0.8rem;
                width: 200px;
            }


        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
            <!-- Logo and Title -->
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../assets/pictures/basclogo.png" alt="Logo">
                <img src="../assets/pictures/cooplogo-cropted.png" alt="Logo" class="rounded-logo">
                <span class="d-none d-sm-inline fw-bold">BULACAN AGRICULTURAL STATE COLLEGE</span>
                <span class="short-name fw-bold d-inline d-sm-none">BASC</span> <!-- Show BASC only on small screens -->
            </a>

            <!-- Login Form and Forgot Password Link -->
            <div class="d-flex align-items-center"> <!-- Use align-items-center to align items in the center -->
                <form action="../function/loginFunction.php" method="POST" class="form-inline d-flex mb-2 mb-md-0">
                    <input class="form-control me-2" type="text" name="emailOrUsername" placeholder="Email" required>
                    <input class="form-control me-2" type="password" name="password" placeholder="Password" required>
                    <button class="btn btn-outline-success" type="submit" class="mr-sm-2" name="login">Login</button>
                </form>

            </div>
        </div>
    </nav>

    <br><br>

    <!-- Forgot Password Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php if (isset($_SESSION['status'])) : ?>
                    <div class="alert alert-success">
                        <!-- Use htmlspecialchars to prevent XSS -->
                        <h5><?php echo htmlspecialchars($_SESSION['status']); ?></h5>
                    </div>
                    <?php unset($_SESSION['status']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header text-center">
                        Forgot Password
                    </div>
                    <div class="card-body">
                        <form action="prc.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" name="password_reset_link" class="btn btn-primary w-100 mb-3">Send Password Link</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>