<?php
session_start();
$emailOrUsername = isset($_SESSION['emailOrUsername']) ? $_SESSION['emailOrUsername'] : '';
// Check if the Remember Me checkbox is checked
if (isset($_POST['rememberMe']) && $_POST['rememberMe'] == 'on') {
    // Set a cookie to remember the user
    $expiry = time() + (30 * 24 * 60 * 60); // Cookie expires in 30 days
    setcookie('remember_user', $_POST['emailOrUsername'], $expiry, '/');
}

// Check if there's a cookie to pre-fill the login form
if (isset($_COOKIE['remember_user'])) {
    $rememberedUser = $_COOKIE['remember_user'];
} else {
    $rememberedUser = ''; // Default to empty string if no cookie is found
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BASCPCC</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/font.min.css">
    <link rel="stylesheet" href="assets/login.css">
    <link rel="stylesheet" href="assets/bootstrap/fontawesome/font/css/fontawesome-all.min.css">
    
        <link rel="icon" href="../assets/pictures/cooplogo.jpg" type="image/x-icon"> <!-- Adjust the path accordingly -->
            <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"> <!-- Adjust the path accordingly -->
            <link rel="icon" type="image/png" sizes="32x32" href="../assets/pictures/cooplogo.jpg">
            <!-- Adjust the path accordingly -->
            <link rel="icon" type="image/png" sizes="16x16" href="../assets/pictures/cooplogo.jpg">


</head>
<body>
    
<div class="form-container">
        <div class="text-center logo">
            <img src="assets/pictures/cooplogo.jpg" alt="Logo">
        </div>

        <!-- Login Form -->
        <form action="function/loginFunction.php" method="POST">
            <div class="mb-3">
                <label for="inputEmail" class="form-label">Email address or Username</label>
                <input type="text" class="form-control <?= isset($_SESSION['username_error']) ? 'is-invalid' : '' ?>"
                       name="emailOrUsername" id="inputEmail" value="<?= htmlspecialchars($emailOrUsername); ?>" placeholder="Email or Username" required>
                <?php if (isset($_SESSION['username_error'])): ?>
                    <div class="invalid-feedback">
                        <?= $_SESSION['username_error']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="inputPassword" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control <?= isset($_SESSION['password_error']) ? 'is-invalid' : '' ?>"
                           id="inputPassword" name="password" placeholder="Password" required>
                    <span class="input-group-text" id="togglePassword"><i class="fas fa-eye"></i></span>
                    <?php if (isset($_SESSION['password_error'])): ?>
                        <div class="invalid-feedback">
                            <?= $_SESSION['password_error']; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100 mb-3">Login</button>

            <div class="d-flex justify-content-between">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="rememberMe" id="rememberCheck">
                    <label class="form-check-label" for="rememberCheck">Remember Me</label>
                </div>
                <a href="forgot_password/pr.php" class="forgot-password">Forgot password?</a>
            </div>
        </form>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#inputPassword');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>

<?php
// Clear session errors after displaying
unset($_SESSION['username_error']);
unset($_SESSION['password_error']);
unset($_SESSION['emailOrUsername']);
?>