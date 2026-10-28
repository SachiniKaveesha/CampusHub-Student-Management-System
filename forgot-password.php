<?php
date_default_timezone_set('Asia/Colombo');
session_start();
require 'connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Database::setUpConnection();
    
    $email = Database::$connection->real_escape_string(trim($_POST['email']));
    $result = Database::search("SELECT * FROM `students` WHERE `email` = '$email'");
//otp
    if ($result->num_rows > 0) {
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        Database::iud("UPDATE `students` SET `otp_code` = '$otp', `otp_expiry` = '$expiry' WHERE `email` = '$email'");

        $_SESSION['reset_email'] = $email;
        $_SESSION['temp_otp_display'] = "Your local test code is: $otp"; 

        header("Location: verify-otp.php");
        exit;
    } else {
        $error = "No student found with that email address!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 0; }
        .auth-card { background-color: var(--bg-secondary); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 20px; position: relative; max-width: 450px; width: 100%; }
        .auth-card::after { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--accent-cyan), var(--accent-purple)); border-top-left-radius: 20px; border-top-right-radius: 20px; }
        .input-group-text { background-color: rgba(13, 11, 24, 0.7) !important; border: 1px solid rgba(255, 255, 255, 0.15) !important; border-right: none !important; color: #64748b !important; }
        .auth-input { border-left: none !important; }
    </style>
</head>
<body class="bg-darker">
    <div class="container auth-container">
        <div class="auth-card p-4 p-md-5 shadow-lg">
            
            <div class="text-center mb-4">
                <a class="navbar-brand fs-2 mb-2 d-inline-block" href="index.php" style="text-decoration: none; font-weight:700; color:#fff;">
                    <i class="fa-solid fa-graduation-cap me-2 text-cyan"></i>Campus<span class="text-cyan">Hub</span>
                </a>
                <h4 class="text-light fw-600 mt-2">Reset Password</h4>
                <p class="text-muted small">Enter your registered email below, and we'll send a 6-digit verification code to your inbox.</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <form action="forgot-password.php" method="POST">
                <div class="mb-4 text-start">
                    <label class="form-label text-light small fw-600">University Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control custom-input auth-input" placeholder="student@campus.edu" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-cyan w-100 py-3 rounded-3 text-dark fw-bold text-uppercase tracking-wide">Send Verification Code</button>
            </form>

            <div class="text-center mt-4">
                <a href="login.php" class="d-inline-block text-cyan small text-decoration-none fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> Back to Login</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>