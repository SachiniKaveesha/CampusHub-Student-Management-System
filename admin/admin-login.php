<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #090812;
            color: #fff;
            font-family: sans-serif;
        }

        .admin-auth-card {
            background: rgba(22, 20, 44, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 240, 255, 0.2);
            border-radius: 24px;
            max-width: 420px;
            width: 100%;
            padding: 40px;
        }

        .custom-input {
            background: #0d0b18 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            border-radius: 10px;
            padding: 12px;
        }

        .custom-input:focus {
            border-color: #00f0ff !important;
            box-shadow: none;
        }

        .btn-cyan {
            background: #00f0ff;
            color: #000;
            font-weight: bold;
            border-radius: 10px;
            border: none;
        }

        .btn-cyan:hover {
            background: #00d8e6;
            color: #000;
        }

        .text-cyan {
            color: #00f0ff;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="admin-auth-card text-center">
        <div class="mb-4">
            <i class="fa-solid fa-user-shield fs-1 text-cyan mb-2"></i>
            <h2 class="fw-bold mb-1">Admin <span class="text-cyan">Portal</span></h2>
            <p class="text-muted small uppercase">Secure Command Center</p>
        </div>

        <?php
        if (isset($_SESSION['error_msg'])) {
            echo '<div style="background-color: #ff4d4d; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 14px;">'
                . $_SESSION['error_msg'] .
                '</div>';
            unset($_SESSION['error_msg']);
        }
        ?>

        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> Invalid Credentials!
            </div>
        <?php } ?>

        <form action="admin-login-process.php" method="POST">
            <div class="mb-3 text-start">
                <label class="form-label text-muted small">Access Key / Email</label>
                <input type="email" name="admin_email" class="form-control custom-input" placeholder="admin@campushub.lk" required>
            </div>
            <div class="mb-4 text-start">
                <label class="form-label text-muted small">Master Password</label>
                <input type="password" name="admin_password" class="form-control custom-input" placeholder="••••••••" required>
            </div>
            <button type="submit" name="login_btn" class="btn btn-cyan w-100 py-3 fw-bold">AUTHORIZE ACCESS</button>
        </form>
        <div class="mt-3">
            <a href="http://localhost/campushub/login.php">
                <i class="fa-solid fa-lock me-1"></i> Back
            </a>
        </div>
    </div>

</body>

</html>