<?php
session_start();
require 'connection.php';

if (isset($_SESSION['student'])) {
    header("Location: student/student-dashboard.php");
    exit();
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Database::setUpConnection();

    $email = Database::$connection->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    $result = Database::search("SELECT * FROM `students` WHERE `email`='$email'");

    if ($result->num_rows > 0) {
        $student_data = $result->fetch_assoc();

        if ($password === $student_data['password']) {
            $_SESSION['student'] = $student_data;

            header("Location: student/student-dashboard.php");
            exit();
        } else {
             $error= "Invalid Password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Login</title>
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
    </style>
</head>

<body class="bg-darker">
    <div class="container auth-container">
        <div class="auth-card p-4 p-md-4 shadow-lg">

            <div class="text-center mb-4">
                <a class="navbar-brand fs-2 mb-2 d-inline-block" href="index.php" style="text-decoration: none; font-weight:700; color:#fff;">
                    <i class="fa-solid fa-graduation-cap me-2 text-cyan"></i>Campus<span class="text-cyan">Hub</span>
                </a>
                <p class="text-muted small">Welcome back, Student! Sign in to continue your portal.</p>
            </div>


            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label text-light small fw-600">Student Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control custom-input auth-input" placeholder="student@campus.edu" required>
                    </div>
                </div>

                <div class="mb-3 text-start">
                    <div class="d-flex justify-content-between">
                        <label class="form-label text-light small fw-600">Password</label>
                        <a href="forgot-password.php" class="text-cyan small text-decoration-none">Forgot?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" class="form-control custom-input auth-input" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="mb-4 form-check text-start">
                    <input type="checkbox" class="form-check-input bg-dark border-secondary" id="rememberMe">
                    <label class="form-check-label text-muted small" for="rememberMe">Remember Password</label>
                </div>

                <button type="submit" class="btn btn-cyan w-100 py-3 rounded-3 text-dark fw-bold text-uppercase tracking-wide">Login</button>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted small mb-0">New to the CampusHub? <a href="register.php" class="text-cyan text-decoration-none fw-bold">Create Account</a></p>
                <a href="index.php" class="d-inline-block mt-3 text-muted small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back to Home</a>
            </div>

            <div class="mt-5 text-start">
                <a href="http://localhost/campushub/admin/admin-login.php" class="text-muted small text-decoration-none">
                    <i class="fa-solid fa-lock me-1"></i> Admin Login
                </a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>