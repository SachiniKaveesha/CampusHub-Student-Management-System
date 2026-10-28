<?php
session_start();

if (!isset($_SESSION['admin_logged'])) {
    header('Location: admin-login.php');
    exit();
}

include('../connection.php');

$session_val = $_SESSION['admin_logged'];
$admin_rs = Database::search("SELECT * FROM `admin` WHERE `email`='" . $session_val . "' OR `admin_id`='" . $session_val . "'");
$admin_data = $admin_rs->fetch_assoc();

if (isset($_GET['disable_id'])) {
    $disable_id = $_GET['disable_id'];
    Database::iud("UPDATE `students` SET `status`='0' WHERE `student_id`='" . $disable_id . "'");
    $_SESSION['student_disabled_alert'] = true;
    header('Location: manage-students.php');
    exit();
}
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_text = trim($_GET['search']);
    $search_query = " AND (`students`.`full_name` LIKE '%" . $search_text . "%' 
                       OR `students`.`email` LIKE '%" . $search_text . "%' 
                       OR `students`.`student_id` LIKE '%" . $search_text . "%')";
}

$student_rs = Database::search("SELECT `students`.*, `courses`.`course_name`, `batches`.`batch_name` 
                               FROM `students` 
                               LEFT JOIN `courses` ON `students`.`courses_course_id` = `courses`.`course_id` 
                               LEFT JOIN `batches` ON `students`.`batches_batch_id` = `batches`.`batch_id` 
                               WHERE `students`.`status`='1'" . $search_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | All Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --accent-cyan: #00f0ff;
        }

        body {
            background-color: #090812;
            color: #ffffff;
            font-family: sans-serif;
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

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
        }

        .stat-card {
            background: #16142c;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        .text-cyan {
            color: var(--accent-cyan) !important;
        }

        .table-dark {
            background: transparent !important;
        }

        .table th {
            color: #64748b !important;
            font-size: 12px;
        }

        .table td {
            color: #f1f5f9 !important;
        }

        .search-input {
            background: #0d0b18 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            border-radius: 10px 0 0 10px !important;
        }

        .search-input:focus {
            border-color: var(--accent-cyan) !important;
            box-shadow: none !important;
        }

        .search-btn {
            background: var(--accent-cyan) !important;
            color: #000 !important;
            border-radius: 0 10px 10px 0 !important;
            font-weight: bold;
        }

        .clear-btn {
            border-radius: 10px !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #94a3b8 !important;
        }

        /* 🛠️ අකුරු පැහැදිලිව පෙනෙන්න හදපු Custom Neon Badges */
        .badge-course {
            background-color: rgba(0, 240, 255, 0.1) !important;
            color: #00f0ff !important;
            border: 1px solid rgba(0, 240, 255, 0.3) !important;
            font-weight: 600;
        }

        .badge-batch {
            background-color: rgba(168, 85, 247, 0.1) !important;
            color: #c084fc !important;
            border: 1px solid rgba(168, 85, 247, 0.3) !important;
            font-weight: 600;
        }

        .badge-notset {
            background-color: rgba(239, 68, 68, 0.1) !important;
            color: #f87171 !important;
            border: 1px solid rgba(239, 68, 68, 0.3) !important;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="mb-5 px-2">
            <h4 class="text-white fw-bold"><i class="fa-solid fa-shield-halved text-cyan me-2"></i>Admin<span>Hub</span></h4>
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
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="text-white fw-bold mb-0">Student Directory</h2>
                <p class="text-white-50 mb-0">View and manage registered student profiles</p>
            </div>
            <?php
            $nav_image_url = !empty($admin_data['image']) ? '../uploads/admin_profile/' . $admin_data['image'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin_data['full_name']) . '&background=00f0ff&color=000';
            ?>
            <a href="admin-profile.php">
                <img src="<?php echo $nav_image_url; ?>" class="rounded-circle" width="45" height="45" style="object-fit: cover; border: 2px solid #00f0ff;">
            </a>
        </div>

        <?php if (isset($_SESSION['student_disabled_alert'])): ?>
            <div class="alert alert-warning bg-warning-subtle text-warning border-warning-subtle alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> Student account has been deactivated.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['student_disabled_alert']); ?>
        <?php endif; ?>

        <div class="mb-4">
            <form action="manage-students.php" method="GET" class="d-flex gap-2" style="max-width: 500px;">
                <div class="input-group">
                    <input type="text" name="search" class="form-control search-input"
                        placeholder="Search by Name, Email or Student ID..."
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn search-btn" type="submit">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                    </button>
                </div>
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <a href="manage-students.php" class="btn clear-btn d-flex align-items-center">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="stat-card">
            <h5 class="text-white fw-bold mb-4">
                <i class="fa-solid fa-graduation-cap text-cyan me-2"></i>
                <?php echo isset($_GET['search']) && !empty($_GET['search']) ? 'Search Results' : 'Active Registered Students'; ?>
                (Total: <?php echo $student_rs->num_rows; ?>)
            </h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead>
                        <tr class="text-muted small">
                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Email Address</th>
                            <th>Course</th>
                            <th>Batch</th>
                            <th>Registered Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php
                        if ($student_rs->num_rows > 0) {
                            while ($student_data = $student_rs->fetch_assoc()) {
                        ?>
                                <tr>
                                    <td><span class="text-cyan fw-semibold">#STU-<?php echo $student_data['student_id']; ?></span></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($student_data['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student_data['email']); ?></td>

                                    <td>
                                        <?php if (!empty($student_data['course_name'])): ?>
                                            <span class="badge badge-course px-2 py-1">
                                                <?php echo htmlspecialchars($student_data['course_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-notset px-2 py-1">Not Set</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($student_data['batch_name'])): ?>
                                            <span class="badge badge-batch px-2 py-1">
                                                <?php echo htmlspecialchars($student_data['batch_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-notset px-2 py-1">Not Set</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?php echo date('Y-m-d', strtotime($student_data['created_at'])); ?></td>
                                    <td class="text-end">
                                        <a href="manage-students.php?disable_id=<?php echo $student_data['student_id']; ?>"
                                            class="btn btn-sm btn-outline-warning py-1 px-2"
                                            onclick="return confirm('Are you sure you want to deactivate this student account?');">
                                            <i class="fa-solid fa-user-slash me-1"></i> Deactivate
                                        </a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="7" class="text-center text-muted py-4">No matching students found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>