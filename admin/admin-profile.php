<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: admin-login.php');
    exit();
}

include('../connection.php');

$session_val = $_SESSION['admin_logged'];

$admin_rs = Database::search("SELECT * FROM `admin` WHERE `email`='" . $session_val . "' OR `admin_id`='" . $session_val . "'");

if ($admin_rs->num_rows > 0) {
    $admin_data = $admin_rs->fetch_assoc();
} else {
    $admin_data = ['admin_id' => 0, 'email' => 'admin@campushub.com', 'full_name' => 'Admin Hub', 'image' => null];
}

// Profile Update
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['full_name']);
    $password = trim($_POST['password']);
    $current_id = $admin_data['admin_id'];

    $image_name_save = $admin_data['image'];

    if (!empty($_FILES['admin_image']['name'])) {
        $file_extension = pathinfo($_FILES['admin_image']['name'], PATHINFO_EXTENSION);
        $new_image_name = 'admin_profile_' . $current_id . '.' . $file_extension;

        $target_dir = '../uploads/admin_profile/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_path = $target_dir . $new_image_name;

        if (move_uploaded_file($_FILES['admin_image']['tmp_name'], $target_path)) {
            $image_name_save = $new_image_name;
        }
    }
    $password_save = !empty($password) ? $password : $admin_data['password'];

    Database::iud("UPDATE `admin` SET `full_name`='" . $name . "', `password`='" . $password_save . "', `image`='" . $image_name_save . "' WHERE `admin_id`='" . $current_id . "'");

    $_SESSION['profile_success'] = "Profile updated successfully!";
    header('Location: admin-profile.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --accent-cyan: #00f0ff;
        }

        /* 💡 Scrollbar එක සම්පූර්ණයෙන්ම අයින් කර Screen එකට හිර කිරීම */
        body {
            background-color: #090812;
            color: #ffffff;
            font-family: sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: #0d0b18;
            position: fixed;
            top: 0;
            left: 0;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 20px;
            z-index: 1000;
        }

        .sidebar-link {
            color: #94a3b8;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 5px;
            transition: 0.3s;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: rgba(0, 240, 255, 0.1);
            color: var(--accent-cyan);
        }

        .sidebar-link i {
            margin-right: 12px;
            width: 20px;
        }

        /* 💡 Main Content එක මැදට ගන්න Flexbox භාවිත කර ඇත */
        .main-content {
            margin-left: var(--sidebar-width);
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* සිරස්ව මැදට ගැනීම */
            padding: 0 40px;
        }

        .profile-card {
            background: #16142c;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .profile-pic-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
        }

        .profile-pic-container img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent-cyan);
        }

        .upload-btn {
            position: absolute;
            bottom: 3px;
            right: 3px;
            background: var(--accent-cyan);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            cursor: pointer;
            border: 3px solid #16142c;
            transition: 0.3s;
        }

        .upload-btn:hover {
            background: #60a5fa;
        }

        .text-cyan {
            color: var(--accent-cyan) !important;
        }

        .form-control {
            background: #0d0b18 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            border-radius: 10px !important;
            padding: 10px !important;
        }

        .form-control:focus {
            border-color: var(--accent-cyan) !important;
            box-shadow: none !important;
        }

        .save-btn {
            background: var(--accent-cyan) !important;
            color: #000 !important;
            font-weight: bold;
            border-radius: 10px;
            padding: 12px;
            border: none;
            width: 100%;
            transition: 0.3s;
        }

        .save-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="mb-5 px-2">
            <h4 class="text-white fw-bold"><i class="fa-solid fa-shield-halved text-cyan me-2"></i>Admin<span class="text-muted fw-normal">Hub</span></h4>
        </div>
        <nav>
            <a href="admin-dashboard.php" class="sidebar-link active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="manage-students.php" class="sidebar-link"><i class="fa-solid fa-users"></i> Students</a>
            <a href="manage-events.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> Events</a>
            <a href="manage-messages.php" class="sidebar-link"><i class="fa-solid fa-envelope-open-text"></i> Messages</a>
            <hr class="text-secondary opacity-25">
            <a href="../logout.php" class="sidebar-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-6 col-xl-5 mx-auto">

                <div class="text-center mb-4">
                    <h2 class="text-white fw-bold mb-1">Admin Profile</h2>
                    <p class="text-muted small">Manage your personal account details</p>
                </div>

                <?php if (isset($_SESSION['profile_success'])): ?>
                    <div class="alert alert-success bg-success-subtle text-success border-success-subtle alert-dismissible fade show mb-3 py-2 small" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> <?php echo $_SESSION['profile_success']; ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['profile_success']); ?>
                <?php endif; ?>

                <div class="profile-card">
                    <form action="admin-profile.php" method="POST" enctype="multipart/form-data">

                        <div class="profile-pic-container mb-3">
                            <?php
                            $image_url = !empty($admin_data['image']) ? '../uploads/admin_profile/' . $admin_data['image'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin_data['full_name']) . '&background=00f0ff&color=000&size=120';
                            ?>
                            <img src="<?php echo $image_url; ?>" id="profileDisplay">
                            <label for="admin_image" class="upload-btn">
                                <i class="fa-solid fa-camera small"></i>
                            </label>
                            <input type="file" id="admin_image" name="admin_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Email Address</label>
                            <input type="email" class="form-control form-control-sm" value="<?php echo htmlspecialchars($admin_data['email']); ?>" readonly style="opacity: 0.5; cursor: not-allowed;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($admin_data['full_name']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-white small fw-bold">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep old password">
                        </div>

                        <button type="submit" name="update_profile" class="save-btn">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Save Changes
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
</body>

</html>