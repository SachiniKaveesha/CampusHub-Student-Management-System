<?php
session_start();
require 'connection.php';

$error = '';
$success = '';

Database::setUpConnection();
$courses_result = Database::search("SELECT * FROM `courses`");
$batches_result = Database::search("SELECT * FROM `batches`");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = isset($_POST['name']) ? Database::$connection->real_escape_string(trim($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? Database::$connection->real_escape_string(trim($_POST['phone'])) : '';
    $email = isset($_POST['email']) ? Database::$connection->real_escape_string(trim($_POST['email'])) : '';
    $course_id = isset($_POST['course_id']) ? Database::$connection->real_escape_string($_POST['course_id']) : '';
    $batch_id = isset($_POST['batch_id']) ? Database::$connection->real_escape_string($_POST['batch_id']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($name) || empty($phone) || empty($email) || empty($course_id) || empty($batch_id) || empty($password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $result = Database::search("SELECT * FROM `students` WHERE `email` = '$email'");

        if ($result->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            $query = "INSERT INTO `students` (`full_name`, `email`, `password`, `phone`, `batches_batch_id`, `courses_course_id`, `status`) 
                      VALUES ('$name', '$email', '$password', '$phone', '$batch_id', '$course_id', 1)";

            Database::iud($query);
            $success = "Registration successful! You can now sign in.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Sign Up</title>
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
            max-width: 600px;
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

        select.auth-input {
            background-color: rgba(13, 11, 24, 0.5) !important;
            color: #fff !important;
        }

        select.auth-input option {
            background-color: #110e1e;
            color: #fff;
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
                <p class="text-muted small">Create your profile and join the student community.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= $success; ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="row g-3 text-start">

                    <div class="col-md-6">
                        <label class="form-label text-light small fw-600">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="name" class="form-control custom-input auth-input" placeholder="Alex Carter" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-light small fw-600">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" name="phone" class="form-control custom-input auth-input" placeholder="0771234567" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-light small fw-600">Enrolled Course</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-book-open"></i></span>
                            <select name="course_id" class="form-select custom-input auth-input" required>
                                <option value="" selected disabled>Select Course</option>
                                <?php while ($course = $courses_result->fetch_assoc()): ?>
                                    <option value="<?= $course['course_id']; ?>"><?= htmlspecialchars($course['course_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-light small fw-600">Academic Batch</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-users"></i></span>
                            <select name="batch_id" class="form-select custom-input auth-input" required>
                                <option value="" selected disabled>Select Batch</option>
                                <?php while ($batch = $batches_result->fetch_assoc()): ?>
                                    <option value="<?= $batch['batch_id']; ?>"><?= htmlspecialchars($batch['batch_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-light small fw-600">University Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control custom-input auth-input" placeholder="alex@campus.edu" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-light small fw-600">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control custom-input auth-input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-light small fw-600">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-shield-halved"></i></span>
                            <input type="password" name="confirm_password" class="form-control custom-input auth-input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="col-12 my-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input bg-dark border-secondary" id="terms" required>
                            <label class="form-check-label text-muted small" for="terms">I agree to CampusHub Privacy Framework</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-cyan w-100 py-3 rounded-3 text-dark fw-bold text-uppercase tracking-wide">Register Account</button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted small mb-0">Already registered? <a href="login.php" class="text-cyan text-decoration-none fw-bold">Sign In</a></p>
                <a href="index.php" class="d-inline-block mt-3 text-muted small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back to Home</a>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>