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

$messages_rs = Database::search("SELECT * FROM `contact_messages` ORDER BY `sent_date` DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Manage Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.04);
        }

        .text-light-muted {
            color: #cbd5e1 !important;
        }

        .text-cyan {
            color: var(--accent-cyan) !important;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th {
            color: #64748b !important;
            font-size: 12px;
            padding: 16px 12px !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.05) !important;
        }

        .custom-table td {
            color: #f1f5f9 !important;
            padding: 16px 12px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
            background: transparent !important;
        }

        .msg-text {
            max-width: 350px;
            word-wrap: break-word;
            white-space: normal;
            color: #cbd5e1;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="mb-4 px-2">
            <h4 class="text-white fw-bold mb-0"><i class="fa-solid fa-shield-halved text-cyan me-2"></i>Admin CampusHub</h4>
        </div>
        <nav>
            <a href="admin-dashboard.php" class="sidebar-link"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="manage-students.php" class="sidebar-link"><i class="fa-solid fa-users"></i> Students</a>
            <a href="manage-events.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> Events</a>
            <a href="manage-messages.php" class="sidebar-link active"><i class="fa-solid fa-envelope-open-text"></i> Messages</a>
            <hr class="text-secondary opacity-25">
            <a href="../logout.php" class="sidebar-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-white fw-bold mb-0" style="font-size: 26px;">Student Inquiries</h2>
                <p class="text-light-muted small mb-0">Manage and review all contact form submissions from students</p>
            </div>

            <?php
            $nav_image_url = !empty($admin_data['image']) ? '../uploads/admin_profile/' . $admin_data['image'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin_data['full_name']) . '&background=00f0ff&color=000';
            ?>
            <a href="admin-profile.php">
                <img src="<?php echo $nav_image_url; ?>" class="rounded-circle" width="45" height="45" style="object-fit: cover; border: 2px solid #00f0ff;">
            </a>
        </div>

        <div class="stat-card">
            <h6 class="text-white fw-bold mb-4"><i class="fa-solid fa-envelope text-cyan me-2"></i>Inbox Messages</h6>
            <div class="table-responsive">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 25%;">Sender Details</th>
                            <th style="width: 20%;">Subject</th>
                            <th style="width: 35%;">Message Description</th>
                            <th style="width: 15%;">Received Date</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php
                        if ($messages_rs->num_rows > 0) {
                            $counter = 1;
                            while ($msg_data = $messages_rs->fetch_assoc()) {
                        ?>
                                <tr>
                                    <td class="text-secondary fw-bold"><?= $counter++; ?></td>
                                    <td>
                                        <div class="fw-semibold text-white"><?= htmlspecialchars($msg_data['full_name']); ?></div>
                                        <div class="text-muted font-monospace" style="font-size: 11px;"><?= htmlspecialchars($msg_data['email']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-cyan border border-info border-opacity-25 px-2 py-1">
                                            <?= htmlspecialchars($msg_data['subject']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="msg-text"><?= nl2br(htmlspecialchars($msg_data['message'])); ?></div>
                                    </td>
                                    <td class="font-monospace text-secondary">
                                        <?= date('Y-m-d h:i A', strtotime($msg_data['sent_date'])); ?>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center text-muted py-5"><i class="fa-regular fa-folder-open d-block mb-2 fs-3"></i> No messages found in database.</td></tr>';
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