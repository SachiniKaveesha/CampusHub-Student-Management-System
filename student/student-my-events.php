<?php
session_start();
require '../connection.php';

if (!isset($_SESSION['student'])) {
    header("Location: ../login.php");
    exit();
}

Database::setUpConnection();
$student_id = $_SESSION['student']['student_id'];

$query = "SELECT registrations.status AS reg_status, events.event_name, events.event_date 
          FROM `registrations` 
          INNER JOIN `events` ON registrations.events_event_id = events.event_id 
          WHERE registrations.students_student_id = '$student_id'
          ORDER BY events.event_date DESC";

$registered_events_rs = Database::search($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | My Registered Events</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
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

        .event-card {
            background: #16142c;
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.03);
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

        .bg-purple {
            background-color: #6366f1 !important;
        }

        /* 🎨 කලින් නොපෙනී ගිය Alert එක වෙනුවට අලුතින් දාපු Custom CSS */
        .no-data-card {
            background: #16142c;
            border: 2px dashed rgba(255, 255, 255, 0.1) !important;
            border-radius: 20px;
            padding: 50px 20px;
            text-align: center;
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
            <a href="student-profile.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> Profile</a>
            <a href="student-my-events.php" class="sidebar-link active"><i class="fa-solid fa-calendar-check"></i> My Events</a>
            <hr class="text-secondary">
            <a href="../index.php" class="sidebar-link"><i class="fa-solid fa-house"></i> Back to Home</a>
            <a href="../logout.php" class="sidebar-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <h2 class="text-white fw-bold mb-4">My Registered Events</h2>

        <div class="row g-4">
            <?php
            if ($registered_events_rs->num_rows > 0) {
                while ($event = $registered_events_rs->fetch_assoc()) {

                    $category_text = "Campus Event";
                    $category_badge_class = "bg-cyan text-dark";

                    if (stripos($event['event_name'], 'design') !== false || stripos($event['event_name'], 'bootcamp') !== false || stripos($event['event_name'], 'ui') !== false) {
                        $category_text = "Design";
                        $category_badge_class = "bg-purple text-light";
                    } elseif (stripos($event['event_name'], 'hackathon') !== false || stripos($event['event_name'], 'code') !== false || stripos($event['event_name'], 'fest') !== false) {
                        $category_text = "Hackathon";
                        $category_badge_class = "bg-info text-dark";
                    }

                    $status_badge_class = "bg-warning-subtle text-warning border border-warning-subtle";
                    $status_text = htmlspecialchars($event['reg_status']);

                    if (strtolower($event['reg_status']) == 'confirmed' || strtolower($event['reg_status']) == 'active' || $event['reg_status'] == '1') {
                        $status_badge_class = "bg-success-subtle text-success border border-success-subtle";
                        $status_text = "Confirmed";
                    } elseif (strtolower($event['reg_status']) == 'cancelled' || $event['reg_status'] == '0') {
                        $status_badge_class = "bg-danger-subtle text-danger border border-danger-subtle";
                        $status_text = "Cancelled";
                    }
            ?>
                    <div class="col-md-6">
                        <div class="event-card d-flex align-items-center justify-content-between">
                            <div>
                                <span class="badge <?= $category_badge_class; ?> mb-2"><?= $category_text; ?></span>
                                <h5 class="text-white fw-bold mb-1"><?= htmlspecialchars($event['event_name']); ?></h5>
                                <p class="text-muted small mb-0">
                                    <i class="fa-regular fa-calendar me-1"></i>
                                    <?= date('F d, Y', strtotime($event['event_date'])); ?>
                                </p>
                            </div>
                            <span class="badge <?= $status_badge_class; ?> px-3 py-2 rounded-pill"><?= $status_text; ?></span>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '
                <div class="col-12 text-center">
                    <div class="no-data-card">
                        <i class="fa-solid fa-calendar-xmark text-muted mb-3" style="font-size: 3rem;"></i>
                        <h4 class="text-white fw-bold mb-2">No Registered Events</h4>
                        <p class="text-muted small mb-0">You haven\'t registered for any campus events yet.</p>
                    </div>
                </div>';
            }
            ?>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>