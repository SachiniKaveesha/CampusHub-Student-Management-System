<?php
session_start();
require '../connection.php';

if (!isset($_SESSION['student'])) {
    header("Location: ../login.php");
    exit();
}

Database::setUpConnection();

$student_id = $_SESSION['student']['student_id'];

$student_query = "SELECT students.*, batches.batch_name FROM `students` 
                  INNER JOIN `batches` ON students.batches_batch_id = batches.batch_id 
                  WHERE students.`student_id` = '$student_id'";

$student_rs = Database::search($student_query);
$student_data = $student_rs->fetch_assoc();

$events_query = "SELECT COUNT(*) AS active_events FROM `registrations` 
                 WHERE `students_student_id` = '$student_id' AND `status` = 'Active'";
$events_rs = Database::search($events_query);
$events_data = $events_rs->fetch_assoc();
$active_events = $events_data['active_events'];

$broadcast_rs = Database::search("SELECT * FROM `announcements` ORDER BY `publish_date` DESC LIMIT 3");

$profile_image = "../default-avatar.png";
if (!empty($student_data['image'])) {
    $profile_image = "../" . $student_data['image'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Student Dashboard</title>
    <link class="css-link" rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --accent-cyan: #00f0ff;
        }

        body {
            background-color: #090812;
            color: #ffffff;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: #0d0b18;
            position: fixed;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 20px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
        }

        .student-card {
            background: #16142c;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            transition: 0.3s;
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
        }

        .text-cyan {
            color: var(--accent-cyan) !important;
        }

        .bg-darker {
            background: #0d0b18 !important;
        }

        .uppercase {
            text-transform: uppercase;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="mb-5 px-2">
            <h4 class="text-white fw-bold"><i class="fa-solid fa-graduation-cap text-cyan me-2"></i>Student<span>Portal</span></h4>
        </div>
        <nav>
            <a href="student-dashboard.php" class="sidebar-link active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="student-profile.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> Profile</a>
            <a href="student-my-events.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> My Events</a>
            <hr class="text-secondary">
            <a href="../index.php" class="sidebar-link"><i class="fa-solid fa-house"></i> Back to Home</a>
            <a href="../logout.php" class="sidebar-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="text-white fw-bold mb-0">Welcome Back, <?= htmlspecialchars($student_data['full_name']); ?>!</h2>
                <p class="text-muted">Here is an overview of your campus activities.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="student-profile.php" class="text-decoration-none text-white small me-2"><?= htmlspecialchars($student_data['full_name']); ?></a>
                <img src="<?= $profile_image; ?>" class="rounded-circle" width="45" height="45" style="object-fit: cover;">
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="student-card">
                    <h6 class="text-muted small uppercase">Registered Events</h6>
                    <h2 class="text-white fw-bold"><?= $active_events; ?> Active</h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="student-card">
                    <h6 class="text-muted small uppercase">Student ID</h6>
                    <h2 class="text-cyan fw-bold">ST-<?= sprintf("%03d", $student_data['student_id']); ?></h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="student-card">
                    <h6 class="text-muted small uppercase">Current Registered Batch</h6>
                    <h2 class="text-white fw-bold"><?= htmlspecialchars($student_data['batch_name']); ?></h2>
                </div>
            </div>
        </div>

        <div class="student-card mt-4">
            <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-bell text-cyan me-2"></i>Latest Broadcasts</h5>

            <?php
            if ($broadcast_rs->num_rows > 0) {
                while ($broadcast = $broadcast_rs->fetch_assoc()) {
            ?>
                    <div class="p-3 mb-2 rounded bg-darker border-start border-cyan border-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-cyan text-dark">Academic Notice</span>
                            <small class="text-muted"><?= date('d M Y', strtotime($broadcast['publish_date'])); ?></small>
                        </div>
                        <h6 class="text-white fw-bold mb-1"><?= htmlspecialchars($broadcast['title']); ?></h6>
                        <p class="text-light-50 small mb-0"><?= htmlspecialchars($broadcast['description']); ?></p>
                    </div>
            <?php
                }
            } else {
                echo '<p class="text-muted small mb-0">No recent announcements available.</p>';
            }
            ?>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>