<?php
date_default_timezone_set('Asia/Colombo');
session_start();
require 'connection.php';

$error = '';
$success = '';
$test_otp_banner = isset($_SESSION['temp_otp_display']) ? $_SESSION['temp_otp_display'] : '';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Database::setUpConnection();

    $otp_input = Database::$connection->real_escape_string(trim($_POST['otp']));
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $result = Database::search("SELECT * FROM `students` WHERE `email` = '$email' AND `otp_code` = '$otp_input' 
            AND `otp_expiry` > NOW()");

        if ($result->num_rows > 0) {
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);

            Database::iud("UPDATE `students` SET `password` = '$new_hash', `otp_code` = NULL, `otp_expiry` = NULL 
                WHERE `email` = '$email'");

            unset($_SESSION['reset_email']);
            unset($_SESSION['temp_otp_display']);

            $success = "Password reset complete! Redirecting to login...";
            header("refresh:2;url=login.php");
        } else {
            $error = "Invalid or expired OTP verification code!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Verify OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }

        .auth-card {
            background-color: var(--bg-secondary);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            position: relative;
            max-width: 450px;
            width: 100%;
        }

        .auth-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-purple));
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .input-group-text {
            background-color: rgba(13, 11, 24, 0.7) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-right: none !important;
            color: #64748b !important;
        }

        .auth-input {
            border-left: none !important;
        }

        .otp-input {
            letter-spacing: 8px;
            font-size: 1.2rem;
            font-weight: 700;
            text-align: center;
        }
    </style>
</head>

<body class="bg-darker">
    <div class="container auth-container">
        <div class="auth-card p-4 p-md-5 shadow-lg">

            <div class="text-center mb-4">
                <a class="navbar-brand fs-2 mb-2 d-inline-block" href="index.php" style="text-decoration: none; font-weight:700; color:#fff;">
                    <i class="fa-solid fa-graduation-cap me-2 text-cyan"></i>Campus<span class="text-cyan">Hub</span>
                </a>
                <h4 class="text-light fw-600 mt-2">Verify Code</h4>
                <p class="text-muted small">We've sent a code to your email. Check inbox and input it along with your brand new password.</p>
            </div>

            <?php if (!empty($test_otp_banner)): ?>
                <div class="alert alert-info text-center small fw-bold mb-3"><?= $test_otp_banner; ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= $success; ?></div>
            <?php endif; ?>

            <form action="verify-otp.php" method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label text-light small fw-600">6-Digit Verification Code</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                        <input type="text" name="otp" class="form-control custom-input auth-input otp-input" placeholder="000000" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required>
                    </div>
                </div>

                <div class="mb-3 text-start">
                    <label class="form-label text-light small fw-600">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" class="form-control custom-input auth-input" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="mb-4 text-start">
                    <label class="form-label text-light small fw-600">Confirm New Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-shield-halved"></i></span>
                        <input type="password" name="confirm_password" class="form-control custom-input auth-input" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-cyan w-100 py-3 rounded-3 text-dark fw-bold text-uppercase tracking-wide">Update Password</button>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted small">Didn't receive the code? <a href="#" class="text-cyan text-decoration-none fw-bold">Resend OTP</a></p>
                <a href="forgot-password.php" class="d-inline-block mt-2 text-muted small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>