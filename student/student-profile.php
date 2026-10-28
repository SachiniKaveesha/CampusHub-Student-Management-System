<?php 
session_start();
require '../connection.php'; 
Database::setUpConnection();

if (!isset($_SESSION['student'])) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student']['student_id'];
$msg = "";
$status = ""; 

if (isset($_POST['upload_photo_btn'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed_extensions = array("jpg", "jpeg", "png", "svg");
        $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            $new_img_name = "student_img_" . $student_id . "_" . uniqid() . "." . $file_extension;
            $target_dir = "../resource/profile_images/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $target_file = $target_dir . $new_img_name;

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                $db_save_path = "resource/profile_images/" . $new_img_name;
                
                Database::iud("UPDATE `students` SET `image` = '$db_save_path' WHERE `student_id` = '$student_id'");

                $_SESSION['student']['image'] = $db_save_path;

                $msg = "Profile picture updated successfully!";
                $status = "success";
            } else {
                $msg = "Failed to move uploaded file.";
                $status = "error";
            }
        } else {
            $msg = "Invalid file type. Only JPG, JPEG, PNG & SVG are allowed.";
            $status = "error";
        }
    } else {
        $msg = "Please select a valid image file.";
        $status = "error";
    }
}

if (isset($_POST['save_changes_btn'])) {
    $full_name = Database::$connection->real_escape_string(trim($_POST['full_name']));
    $email = Database::$connection->real_escape_string(trim($_POST['email']));
    $course_id = $_POST['course'];
    $batch_id = $_POST['batch'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($full_name) || empty($email)) {
        $msg = "Full Name and Email cannot be empty!";
        $status = "error";
    } else {
        $update_query = "UPDATE `students` SET `full_name` = '$full_name', `email` = '$email', `courses_course_id` = '$course_id', `batches_batch_id` = '$batch_id'";
        
        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $update_query .= ", `password` = '$new_password'";
            } else {
                $msg = "Passwords do not match!";
                $status = "error";
            }
        }

        if ($status !== "error") {
            $update_query .= " WHERE `student_id` = '$student_id'";
            Database::iud($update_query);

            $updated_student_rs = Database::search("SELECT * FROM `students` WHERE `student_id` = '$student_id'");
            $_SESSION['student'] = $updated_student_rs->fetch_assoc();

            $msg = "Profile details updated successfully!";
            $status = "success";
        }
    }
}

$student_rs = Database::search("SELECT * FROM `students` WHERE `student_id` = '$student_id'");
$student_data = $student_rs->fetch_assoc();

$profile_img = "../default-avatar.png"; 
if (!empty($student_data['image'])) {
    $profile_img = "../" . $student_data['image']; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | My Profile</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root { 
            --sidebar-width: 260px; 
            --accent-cyan: #00f0ff;
        }
        body { background-color: #090812; color: #ffffff; }
        .sidebar {
            width: var(--sidebar-width); height: 100vh;
            background: #0d0b18; position: fixed;
            border-right: 1px solid rgba(255,255,255,0.05); padding: 20px;
        }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; }
        .profile-card {
            background: #16142c; border-radius: 20px; padding: 30px;
            border: 1px solid rgba(255,255,255,0.03);
        }
        .profile-pic-container {
            position: relative; width: 120px; height: 120px; margin: 0 auto 20px;
        }
        .profile-pic-container img {
            width: 120px; height: 120px; border-radius: 50%; object-fit: cover;
            border: 3px solid var(--accent-cyan);
        }
        .upload-btn {
            position: absolute; bottom: 0; right: 0;
            background: var(--accent-cyan); width: 35px; height: 35px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: #000; cursor: pointer; border: 2px solid #16142c;
        }
        .sidebar-link {
            color: #94a3b8; padding: 12px 15px; display: flex; align-items: center;
            text-decoration: none; border-radius: 12px; margin-bottom: 5px; transition: 0.3s;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(0, 240, 255, 0.1); color: var(--accent-cyan);
        }
        .sidebar-link i { margin-right: 12px; }

        .custom-input {
            background: #110f24 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
            border-radius: 10px !important;
            padding: 12px !important;
            transition: 0.3s;
        }
        .custom-input:focus {
            background: #16142c !important;
            border-color: var(--accent-cyan) !important;
            box-shadow: 0 0 10px rgba(0, 240, 255, 0.2) !important;
            color: #ffffff !important;
        }
        .custom-input[readonly] {
            background: #0d0b18 !important;
            color: #64748b !important;
            border-color: rgba(255, 255, 255, 0.05) !important;
        }
        select.custom-input option {
            background: #110f24;
            color: #ffffff;
        }
        .form-label {
            color: #94a3b8 !important;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .btn-cyan {
            background-color: var(--accent-cyan) !important;
            color: #000000 !important;
        }
        .btn-outline-cyan {
            border-color: var(--accent-cyan) !important;
            color: var(--accent-cyan) !important;
        }
        .btn-outline-cyan:hover {
            background-color: var(--accent-cyan) !important;
            color: #000000 !important;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="mb-5 px-2">
            <h4 class="text-white fw-bold"><i class="fa-solid fa-graduation-cap text-cyan me-2"></i>Student<span>Portal</span></h4>
        </div>
        <nav>
            <a href="student-dashboard.php" class="sidebar-link"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="student-profile.php" class="sidebar-link active"><i class="fa-solid fa-user-gear"></i> Profile</a>
            <a href="student-my-events.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> My Events</a>
            <hr class="text-secondary">
            <a href="../index.php" class="sidebar-link"><i class="fa-solid fa-house"></i> Back to Home</a>
            <a href="../logout.php" class="sidebar-link"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
        </nav>
    </div>

    <div class="main-content">
        <h2 class="text-white fw-bold mb-4">Account Settings</h2>
        
        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?= ($status === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show border-0 bg-opacity-10 text-<?= ($status === 'success') ? 'success' : 'danger'; ?>" role="alert" style="background-color: <?= ($status === 'success') ? 'rgba(40, 167, 69, 0.1)' : 'rgba(220, 53, 69, 0.1)'; ?>;">
                <i class="fa-solid <?= ($status === 'success') ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> me-2"></i>
                <?= $msg; ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4 text-center">
                <div class="profile-card">
                    <form action="student-profile.php" method="POST" enctype="multipart/form-data">
                        <div class="profile-pic-container">
                            <img src="<?= $profile_img; ?>" id="profileDisplay">
                            <label for="fileUpload" class="upload-btn">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                            <input type="file" id="fileUpload" name="profile_pic" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        </div>
                        <h5 class="text-white fw-bold mb-1"><?= htmlspecialchars($student_data['full_name']); ?></h5>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($student_data['email']); ?></p>
                        <button type="submit" name="upload_photo_btn" class="btn btn-sm btn-outline-cyan rounded-pill px-3">Upload Photo</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="profile-card">
                    <h5 class="text-white fw-bold mb-4">Edit Profile Information</h5>
                    <form action="student-profile.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Full Name</label>
                                <input type="text" name="full_name" class="form-control custom-input" value="<?= htmlspecialchars($student_data['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Student ID (Non-Editable)</label>
                                <input type="text" class="form-control custom-input" value="ST-<?= sprintf("%03d", $student_data['student_id']); ?>" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Email Address</label>
                                <input type="email" name="email" class="form-control custom-input" value="<?= htmlspecialchars($student_data['email']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Select Your Course</label>
                                <select name="course" class="form-control custom-input" required>
                                    <option value="" disabled>-- Choose Course --</option>
                                    <?php
                                    if(class_exists('Database')){
                                        $course_rs = Database::search("SELECT * FROM `courses`");
                                        while($course_data = $course_rs->fetch_assoc()){
                                            $selected = ($student_data['courses_course_id'] == $course_data['course_id']) ? 'selected' : '';
                                            echo '<option value="'.$course_data['course_id'].'" '.$selected.'>'.htmlspecialchars($course_data['course_name']).'</option>';
                                        }
                                    } else {
                                        echo '<option value="1" selected>Software Engineering</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Select Your Batch</label>
                                <select name="batch" class="form-control custom-input" required>
                                    <option value="" disabled>-- Choose Batch --</option>
                                    <?php
                                    if(class_exists('Database')){
                                        $batch_rs = Database::search("SELECT * FROM `batches`");
                                        while($batch_data = $batch_rs->fetch_assoc()){
                                            $selected = ($student_data['batches_batch_id'] == $batch_data['batch_id']) ? 'selected' : '';
                                            echo '<option value="'.$batch_data['batch_id'].'" '.$selected.'>'.htmlspecialchars($batch_data['batch_name']).'</option>';
                                        }
                                    } else {
                                        echo '<option value="1" selected>2026 February Intake</option>';
                                    }
                                    ?>
                                </select> 
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small">New Password (Leave blank to keep current)</label>
                                <input type="password" name="new_password" class="form-control custom-input" placeholder="••••••••">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control custom-input" placeholder="••••••••">
                            </div>
                        </div>
                        <button type="submit" name="save_changes_btn" class="btn btn-cyan px-4 py-2.5 fw-bold rounded-3 mt-4">Save Changes</button> 
                    </form>
                </div> 
            </div> 
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileDisplay').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>