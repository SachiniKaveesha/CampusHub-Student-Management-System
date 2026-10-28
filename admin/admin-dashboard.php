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

// METRICS DATA FETCHING
$students_count_rs = Database::search("SELECT COUNT(`student_id`) AS `total` FROM `students` WHERE `status`='1'");
$students_count_data = $students_count_rs->fetch_assoc();
$total_students = $students_count_data['total'];

$events_count_rs = Database::search("SELECT COUNT(`event_id`) AS `total` FROM `events` WHERE `status`='1'");
$events_count_data = $events_count_rs->fetch_assoc();
$active_events = $events_count_data['total'];

$new_reg_rs = Database::search("SELECT COUNT(`student_id`) AS `total` FROM `students` WHERE `status`='1' AND `created_at` >= NOW() - INTERVAL 30 DAY");
$new_reg_data = $new_reg_rs->fetch_assoc();
$new_registrations = $new_reg_data['total'];

// ANNOUNCEMENT 
$announcement_alert = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['broadcast_notice'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $current_date = date('Y-m-d');

    if (!empty($title) && !empty($description)) {
        $safe_title = addslashes($title);
        $safe_description = addslashes($description);

        Database::iud("INSERT INTO `announcements` (`title`, `description`, `publish_date`) 
            VALUES ('" . $safe_title . "', '" . $safe_description . "', NOW())");

        $xml_file = 'announcements.xml';
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        if (file_exists($xml_file) && filesize($xml_file) > 0) {
            $dom->load($xml_file);
            $root = $dom->documentElement;
        } else {
            $root = $dom->createElement('campushub');
            $dom->appendChild($root);
        }

        $announcement = $dom->createElement('announcement');

        $title_node = $dom->createElement('title', htmlspecialchars($title));
        $date_node = $dom->createElement('date', $current_date);
        $desc_node = $dom->createElement('description', htmlspecialchars($description));

        $announcement->appendChild($title_node);
        $announcement->appendChild($date_node);
        $announcement->appendChild($desc_node);

        $root->appendChild($announcement);
        $dom->save($xml_file);

        $announcement_alert = "Notice broadcasted and saved to XML successfully!";
    }
}

// REGISTRATION ACTIONS (APPROVE / REJECT)
if (isset($_GET['action']) && isset($_GET['rid'])) {
    $action = $_GET['action'];
    $reg_id = $_GET['rid'];

    if ($action === 'approve') {
        Database::iud("UPDATE `registrations` SET `status`='Approved' WHERE `registration_id`='" . $reg_id . "'");
    } elseif ($action === 'reject') {
        Database::iud("UPDATE `registrations` SET `status`='Rejected' WHERE `registration_id`='" . $reg_id . "'");
    }

    header('Location: admin-dashboard.php');
    exit();
}

//Pending Registrations
$pending_rs = Database::search("SELECT `registrations`.`registration_id` AS `reg_id`, `students`.`full_name`, `events`.`event_name` 
                               FROM `registrations` 
                               INNER JOIN `students` ON `registrations`.`students_student_id` = `students`.`student_id` 
                               INNER JOIN `events` ON `registrations`.`events_event_id` = `events`.`event_id` 
                               WHERE `registrations`.`status`='Pending'");

// CHART
$months_data = [];
for ($m = 1; $m <= 6; $m++) {
    $chart_rs = Database::search("SELECT COUNT(`student_id`) AS `total` FROM `students` WHERE MONTH(`created_at`) = '$m' AND YEAR(`created_at`) = '2026'");
    $chart_row = $chart_rs->fetch_assoc();
    $months_data[] = $chart_row['total'];
}
$chart_data_string = implode(',', $months_data);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --sidebar-width: 250px;
            --accent-cyan: #00f0ff;
        }

        body {
            background-color: #090812;
            color: #ffffff;
            font-family: sans-serif;
            overflow-x: hidden;
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
            padding: 25px;
            min-height: 100vh;
        }

        .stat-card {
            background: #16142c;
            border-radius: 16px;
            padding: 18px 20px;
            border: 1px solid rgba(255, 255, 255, 0.04);
            height: 100%;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            background: rgba(0, 240, 255, 0.1);
            color: var(--accent-cyan);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 20px;
        }

        .text-light-muted {
            color: #cbd5e1 !important;
        }

        .text-cyan {
            color: var(--accent-cyan) !important;
        }

        .custom-input {
            background: #0d0b18 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 14px;
        }

        .btn-cyan {
            background: var(--accent-cyan);
            color: #000;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            font-size: 14px;
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
            padding: 12px 8px !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.05) !important;
        }

        .custom-table td {
            color: #f1f5f9 !important;
            padding: 12px 8px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
            background: transparent !important;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="mb-4 px-2">
            <h4 class="text-white fw-bold mb-0"><i class="fa-solid fa-shield-halved text-cyan me-2"></i>Admin CampusHub</h4>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-white fw-bold mb-0" style="font-size: 26px;">Command Dashboard</h2>
                <p class="text-light-muted small mb-0">Welcome back, Head Administrator</p>
            </div>

            <?php
            $nav_image_url = !empty($admin_data['image']) ? '../uploads/admin_profile/'
                . $admin_data['image'] : 'https://ui-avatars.com/api/?name='
                . urlencode($admin_data['full_name']) . '&background=00f0ff&color=000';
            ?>

            <a href="admin-profile.php">
                <img src="<?php echo $nav_image_url; ?>" class="rounded-circle" width="45" height="45" style="object-fit: cover; border: 2px solid #00f0ff;">
            </a>
        </div>

        <?php if ($announcement_alert): ?>
            <div class="alert alert-success bg-success-subtle text-success border-success-subtle alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?php echo $announcement_alert; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-light-muted small mb-1">Total Students</p>
                        <h3 class="text-white fw-bold mb-0"><?php echo number_format($total_students); ?></h3>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-light-muted small mb-1">Active Events</p>
                        <h3 class="text-white fw-bold mb-0"><?php echo $active_events; ?></h3>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-light-muted small mb-1">New Registrations</p>
                        <h3 class="text-white fw-bold mb-0"><?php echo $new_registrations; ?></h3>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-id-card"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-light-muted small mb-1">System Status</p>
                        <h3 class="text-cyan fw-bold mb-0">Online</h3>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-microchip"></i></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="stat-card">
                    <h6 class="text-white fw-bold mb-3">Registration Trends (2026)</h6>
                    <div style="height: 160px; position: relative;">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="stat-card">
                    <h6 class="text-white fw-bold mb-2"><i class="fa-solid fa-bullhorn text-cyan me-2"></i>Create Announcement</h6>
                    <form action="admin-dashboard.php" method="POST" class="d-flex flex-column gap-2">
                        <input type="text" name="title" class="form-control custom-input" placeholder="Notice Title..." required>
                        <textarea name="description" class="form-control custom-input" rows="2" placeholder="Write description here..." style="resize: none;" required></textarea>

                        <button type="submit" name="broadcast_notice" class="btn btn-cyan w-100 py-2">Broadcast Notice</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <h6 class="text-white fw-bold mb-3"><i class="fa-solid fa-id-card text-cyan me-2"></i>Pending Event Registrations</h6>
            <div class="table-responsive">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Event Requested</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php
                        if ($pending_rs->num_rows > 0) {
                            while ($pending_data = $pending_rs->fetch_assoc()) {
                        ?>
                                <tr>
                                    <td class="fw-semibold text-white"><?php echo htmlspecialchars($pending_data['full_name']); ?></td>
                                    <td class="text-white-50"><?php echo htmlspecialchars($pending_data['event_name']); ?></td>
                                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Pending</span></td>
                                    <td class="text-end">
                                        <a href="admin-dashboard.php?action=approve&rid=<?php echo $pending_data['reg_id']; ?>" class="btn btn-sm btn-cyan py-1 px-3 me-1">Approve</a>
                                        <a href="admin-dashboard.php?action=reject&rid=<?php echo $pending_data['reg_id']; ?>" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="return confirm('Reject this registration?');"><i class="fa-solid fa-xmark"></i></a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-center text-muted py-4">No pending event registrations.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('growthChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    data: [<?php echo $chart_data_string; ?>],
                    borderColor: '#00f0ff',
                    backgroundColor: 'rgba(0, 240, 255, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: 'rgba(255,255,255,0.03)'
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 10
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>