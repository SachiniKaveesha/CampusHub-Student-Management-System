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

$alert_message = false;
$alert_type = "";

// NEW EVENT DEPLOYMENT
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deploy_event'])) {
    $event_name = trim($_POST['title']);
    $event_date = trim($_POST['date']);
    $event_venue = trim($_POST['venue']);

    if (!empty($event_name) && !empty($event_date) && !empty($event_venue)) {
        Database::iud("INSERT INTO `events` (`event_name`, `event_date`, `location`, `status`) VALUES ('" . $event_name . "', '" . $event_date . "', '" . $event_venue . "', '1')");
        $alert_message = "Event deployed successfully!";
        $alert_type = "success";
    } else {
        $alert_message = "Please fill all fields.";
        $alert_type = "danger";
    }
}

// TOGGLE STATUS (ACTIVATE / DEACTIVATE BACKEND)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $event_id = $_GET['id'];

    if ($action === 'deactivate') {
        Database::iud("UPDATE `events` SET `status`='0' WHERE `event_id`='" . $event_id . "'");
        header('Location: manage-events.php');
        exit();
    } elseif ($action === 'activate') {
        Database::iud("UPDATE `events` SET `status`='1' WHERE `event_id`='" . $event_id . "'");
        header('Location: manage-events.php');
        exit();
    }
}

// FETCH ALL EVENTS (ACTIVE & INACTIVE ALL)
$all_events_rs = Database::search("SELECT * FROM `events` ORDER BY `event_date` ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Manage Events</title>
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
            margin-bottom: 25px;
        }

        .custom-input {
            background: #0d0b18 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            border-radius: 10px;
            padding: 10px;
        }

        .custom-input:focus {
            border-color: var(--accent-cyan) !important;
            box-shadow: none;
        }

        .text-cyan {
            color: var(--accent-cyan) !important;
        }

        .btn-cyan {
            background: var(--accent-cyan);
            color: #000;
            font-weight: bold;
            border-radius: 10px;
            border: none;
        }

        .btn-cyan:hover {
            background: #00d8e6;
            color: #000;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th {
            color: #64748b !important;
            font-size: 12px;
            padding: 14px 10px !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.05) !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-table td {
            color: #f1f5f9 !important;
            padding: 14px 10px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
            background: transparent !important;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="mb-5 px-2">
            <h4 class="text-white fw-bold"><i class="fa-solid fa-shield-halved text-cyan me-2"></i>AdminHub</h4>
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
                <h2 class="text-white fw-bold mb-0">Event Configuration</h2>
                <p class="text-white-50 mb-0">Create and manage upcoming campus programs</p>
            </div>
            <?php
            $nav_image_url = !empty($admin_data['image']) ? '../uploads/admin_profile/' . $admin_data['image'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin_data['full_name']) . '&background=00f0ff&color=000';
            ?>
            <a href="admin-profile.php">
                <img src="<?php echo $nav_image_url; ?>" class="rounded-circle" width="45" height="45" style="object-fit: cover; border: 2px solid #00f0ff;">
            </a>
        </div>

        <?php if ($alert_message): ?>
            <div class="alert alert-<?php echo $alert_type; ?> bg-dark text-white border-secondary alert-dismissible fade show mb-4" role="alert">
                <i class="fa-solid fa-circle-info text-cyan me-2"></i> <?php echo $alert_message; ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="stat-card">
                    <h5 class="text-white fw-bold mb-4"><i class="fa-solid fa-calendar-plus text-cyan me-2"></i>Create New Event</h5>
                    <form action="manage-events.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small">Event Title</label>
                            <input type="text" name="title" class="form-control custom-input" placeholder="CodeFest 2026" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Event Date</label>
                            <input type="date" name="date" class="form-control custom-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Venue</label>
                            <input type="text" name="venue" class="form-control custom-input" placeholder="Main Tech Auditorium" required>
                        </div>
                        <button type="submit" name="deploy_event" class="btn btn-cyan w-100 py-2.5 mt-2">Deploy Event</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="stat-card h-100">
                    <h5 class="text-white fw-bold mb-4"><i class="fa-solid fa-list-check text-cyan me-2"></i>Campus Events List</h5>
                    <div class="table-responsive">
                        <table class="custom-table align-middle">
                            <thead>
                                <tr>
                                    <th>Event Title</th>
                                    <th>Date</th>
                                    <th>Venue</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <?php
                                if ($all_events_rs->num_rows > 0) {
                                    while ($event = $all_events_rs->fetch_assoc()) {
                                ?>
                                        <tr>
                                            <td class="fw-bold text-white"><?php echo htmlspecialchars($event['event_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                                            <td>
                                                <?php if ($event['status'] == '1'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="manage-events.php?view_event=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-cyan py-1 px-2 me-1" title="View Registered Students">
                                                    <i class="fa-solid fa-users"></i> Students
                                                </a>

                                                <?php if ($event['status'] == '1'): ?>
                                                    <a href="manage-events.php?action=deactivate&id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="return confirm('Are you sure you want to deactivate this event?');" title="Deactivate Event">
                                                        <i class="fa-solid fa-ban"></i> Disable
                                                    </a>
                                                <?php else: ?>
                                                    <a href="manage-events.php?action=activate&id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-outline-success py-1 px-2" onclick="return confirm('Are you sure you want to activate this event?');" title="Activate Event">
                                                        <i class="fa-solid fa-circle-check"></i> Enable
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="5" class="text-center text-muted py-4">No events found.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php
        if (isset($_GET['view_event'])):
            $selected_id = $_GET['view_event'];
            $ev_name_rs = Database::search("SELECT `event_name` FROM `events` WHERE `event_id`='" . $selected_id . "'");
            $ev_name_data = $ev_name_rs->fetch_assoc();

            $attendees_rs = Database::search("SELECT `students`.`student_id`, `students`.`full_name`, `registrations`.`status` AS `reg_status` 
                                              FROM `registrations` 
                                              INNER JOIN `students` ON `registrations`.`students_student_id` = `students`.`student_id` 
                                              WHERE `registrations`.`events_event_id` = '" . $selected_id . "'");
        ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="text-white fw-bold mb-0">
                                <i class="fa-solid fa-graduation-cap text-cyan me-2"></i>
                                Students Registered for: <span class="text-cyan"><?php echo htmlspecialchars($ev_name_data['event_name']); ?></span>
                            </h5>
                            <a href="manage-events.php" class="btn btn-sm btn-outline-secondary text-white"><i class="fa-solid fa-xmark"></i> Close List</a>
                        </div>

                        <div class="table-responsive">
                            <table class="custom-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Full Name</th>
                                        <th>Approval Status</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <?php
                                    if ($attendees_rs->num_rows > 0) {
                                        while ($student = $attendees_rs->fetch_assoc()) {
                                    ?>
                                            <tr>
                                                <td>#STU-<?php echo str_pad($student['student_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                                <td class="fw-semibold text-white"><?php echo htmlspecialchars($student['full_name']); ?></td>
                                                <td>
                                                    <?php
                                                    $reg_status = strtolower($student['reg_status']);
                                                    if ($reg_status === 'approved' || $reg_status === 'confirmed' || $reg_status === '1'):
                                                    ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Approved</span>
                                                    <?php elseif ($reg_status === 'pending' || $reg_status === 'active'): ?>
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Pending</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Rejected</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="3" class="text-center text-muted py-4">No students have registered for this event yet.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>