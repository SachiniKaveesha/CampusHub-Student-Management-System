<?php
session_start();
require 'connection.php';
Database::setUpConnection();

$profile_img = "default-avatar.png";
$student_id = null;

if (isset($_SESSION['student'])) {
    $student_id = $_SESSION['student']['student_id'];

    $student_result = Database::search("SELECT `image` FROM `students` WHERE `student_id` = '$student_id'");

    if ($student_result->num_rows > 0) {
        $student_data = $student_result->fetch_assoc();
        if (!empty($student_data['image'])) {
            $profile_img = $student_data['image'];
        }
    }
}

// 1. Dropdown
$all_events_rs = Database::search("SELECT * FROM `events` WHERE `event_date` >= CURDATE() ORDER BY `event_date` ASC");

// 2. Event Registration Form 
$status_message = "";
$status_type = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_register'])) {
    if (!isset($_SESSION['student'])) {
        $status_message = "Please login to your student account to register for events!";
        $status_type = "danger";
    } else {
        $selected_event_id = $_POST['selected_event'];

        if (empty($selected_event_id)) {
            $status_message = "Please select a valid event!";
            $status_type = "danger";
        } else {
            $check_reg_rs = Database::search("SELECT * FROM `registrations` WHERE `students_student_id` = '$student_id' AND `events_event_id` = '$selected_event_id'");

            if ($check_reg_rs->num_rows > 0) {
                $status_message = "You have already registered for this event!";
                $status_type = "warning";
            } else {
                Database::iud("INSERT INTO `registrations` (`status`, `students_student_id`, `events_event_id`) VALUES ('Pending', '$student_id', '$selected_event_id')");
                $status_message = "Registration submitted successfully! Check 'My Events' for confirmation.";
                $status_type = "success";
            }
        }
    }
}

// 3. Contact Form 
$contact_message = "";
$contact_type = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_contact'])) {
    $c_name = trim($_POST['c_name']);
    $c_email = trim($_POST['c_email']);
    $c_subject = trim($_POST['c_subject']);
    $c_msg = trim($_POST['c_message']);

    if (empty($c_name) || empty($c_email) || empty($c_subject) || empty($c_msg)) {
        $contact_message = "All fields are required for the contact form!";
        $contact_type = "danger";
    } elseif (!filter_var($c_email, FILTER_VALIDATE_EMAIL)) {
        $contact_message = "Please enter a valid email address!";
        $contact_type = "danger";
    } else {
        Database::iud("INSERT INTO `contact_messages` (`full_name`, `email`, `subject`, `message`, `sent_date`) 
                       VALUES ('$c_name', '$c_email', '$c_subject', '$c_msg', NOW())");

        $contact_message = "Thank you! Your message has been sent successfully.";
        $contact_type = "success";
    }
}
// 4. Gallery Photo Upload 
$gallery_message = "";
$gallery_type = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_gallery_upload'])) {
    if (!isset($_SESSION['student'])) {
        $gallery_message = "Please login to upload event photographs!";
        $gallery_type = "danger";
    } else {
        if (isset($_FILES['gallery_file']) && $_FILES['gallery_file']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['gallery_file']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                if (!is_dir('uploads/gallery')) {
                    mkdir('uploads/gallery', 0777, true);
                }

                $new_filename = "gallery_" . time() . "_" . uniqid() . "." . $ext;
                $destination = "uploads/gallery/" . $new_filename;

                if (move_uploaded_file($_FILES['gallery_file']['tmp_name'], $destination)) {
                    $gallery_message = "Photograph uploaded successfully to Campus Gallery!";
                    $gallery_type = "success";
                } else {
                    $gallery_message = "Failed to move uploaded file.";
                    $gallery_type = "danger";
                }
            } else {
                $gallery_message = "Invalid file type! Only JPG, PNG & GIF are allowed.";
                $gallery_type = "danger";
            }
        } else {
            $gallery_message = "Please select a valid image file.";
            $gallery_type = "danger";
        }
    }
}

// 5. Multimedia (Video/Audio) Upload
$media_message = "";
$media_type = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_media_upload'])) {
    if (!isset($_SESSION['student'])) {
        $media_message = "Please login to upload multimedia content!";
        $media_type = "danger";
    } else {
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] == 0) {
            $allowed_media = ['mp4', 'mp3', 'wav', 'mpeg'];
            $filename = $_FILES['media_file']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed_media)) {
                if (!is_dir('uploads/media')) {
                    mkdir('uploads/media', 0777, true);
                }

                $new_filename = "media_" . time() . "_" . uniqid() . "." . $ext;
                $destination = "uploads/media/" . $new_filename;

                if (move_uploaded_file($_FILES['media_file']['tmp_name'], $destination)) {
                    $media_message = "Multimedia content uploaded successfully!";
                    $media_type = "success";
                } else {
                    $media_message = "Failed to save media file.";
                    $media_type = "danger";
                }
            } else {
                $media_message = "Invalid format! Only MP4, MP3 & WAV files are allowed.";
                $media_type = "danger";
            }
        } else {
            $media_message = "Please select a valid video or audio file.";
            $media_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Connect & Create</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0b0f19;
            color: #f8fafc;
            overflow-x: hidden;
        }

        .text-cyan {
            color: #00f2fe !important;
            text-shadow: 0 0 10px rgba(0, 242, 254, 0.3);
        }

        .bg-darker {
            background-color: #070a12 !important;
        }

        /* Navbar Styling */
        .navbar {
            background: rgba(11, 15, 25, 0.85) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #00f2fe !important;
        }

        .custom-card,
        .form-container {
            background: rgba(18, 24, 38, 0.6);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .tech-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 242, 254, 0.15);
            border-color: rgba(0, 242, 254, 0.3);
        }

        .custom-input,
        .form-select.custom-input {
            background-color: rgba(7, 10, 18, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff !important;
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .custom-input:focus {
            border-color: #00f2fe;
            box-shadow: 0 0 12px rgba(0, 242, 254, 0.25);
            background-color: #070a12 !important;
        }

        .btn-cyan {
            background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
            border: none;
            color: #070a12 !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-cyan:hover {
            box-shadow: 0 0 20px rgba(0, 242, 254, 0.4);
            transform: scale(1.02);
        }

        .btn-outline-cyan {
            border: 2px solid #00f2fe;
            color: #00f2fe !important;
            font-weight: 600;
        }

        .btn-outline-cyan:hover {
            background: rgba(0, 242, 254, 0.1);
            box-shadow: 0 0 15px rgba(0, 242, 254, 0.2);
        }

        .custom-table {
            background: transparent !important;
            color: #fff;
        }

        .custom-table th {
            background-color: rgba(0, 242, 254, 0.05) !important;
            color: #00f2fe;
            border-bottom: 2px solid rgba(0, 242, 254, 0.2);
        }

        .custom-table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 16px;
        }

        .bg-soft-cyan {
            background: rgba(0, 242, 254, 0.1);
            color: #00f2fe;
        }

        .heading-line {
            width: 60px;
            height: 4px;
            background: #00f2fe;
            border-radius: 2px;
        }

        .footer-links a {
            transition: all 0.3s ease;
            display: inline-block;
        }

        .footer-links a:hover {
            color: #fff !important;
            transform: translateX(5px);
        }

        .social-links a {
            display: inline-block;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            color: #fff !important;
            transform: translateY(-3px);
        }

        .bottom-policy-links:hover {
            color: #fff !important;
            text-decoration: underline !important;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="#"><i class="fa-solid fa-graduation-cap me-2 text-cyan"></i>Campus<span class="text-cyan">Hub</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto align-items-center gap-2 gap-lg-3">
                    <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="eventsDropdown" role="button" data-bs-toggle="dropdown">Events</a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="#register-event">Register Event</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="#multimedia">Media Hub</a></li>
                    <li class="nav-item"><a class="nav-link" href="#announcements">Announcements</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact-section">Contact</a></li>
                </ul>

                <div class="d-flex align-items-center mt-3 mt-lg-0">
                    <?php if (isset($_SESSION['student'])): ?>
                        <div class="nav-item">
                            <a href="student/student-profile.php" class="d-flex align-items-center text-decoration-none">
                                <img src="<?= $profile_img; ?>" alt="Profile" class="rounded-circle border border-cyan" style="width: 40px; height: 40px; object-fit: cover;">
                                <span class="text-light ms-2 d-none d-md-inline small fw-semibold">
                                    Hi, <?= htmlspecialchars($_SESSION['student']['full_name']); ?>
                                </span>
                            </a>
                        </div>
                    <?php else: ?>
                        <a class="btn btn-outline-cyan px-4 rounded-pill me-2 btn-sm" href="login.php">Login</a>
                        <a class="btn btn-cyan px-4 rounded-pill btn-sm text-dark" href="register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <section id="home" class="hero-section d-flex align-items-center pt-5 pb-5">
        <div class="container text-center text-lg-start">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <span class="badge bg-soft-cyan mb-3 px-3 py-2 rounded-pill fs-6">Welcome to Campus Life</span>
                    <h1 class="display-4 fw-bold text-white mb-4">Empowering the Next Generation of Innovators</h1>
                    <p class="lead text-muted mb-4">Connect with peers, keep up with announcements, explore upcoming tech events, and build your profile in the ultimate university ecosystem.</p>
                    <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                        <a href="#register-event" class="btn btn-cyan btn-lg rounded-pill px-4">Explore Events <i class="fa-solid fa-arrow-right ms-2"></i></a>
                        <a href="#about" class="btn btn-outline-light btn-lg rounded-pill px-4">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0 text-center">
                    <div class="hero-image-wrapper">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=600" alt="Students" class="img-fluid rounded-4 shadow-lg border border-secondary border-opacity-20">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="py-5 bg-darker">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&q=80&w=600" alt="About Campus" class="img-fluid rounded-4 shadow">
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <h2 class="text-white fw-bold mb-2">About CampusHub</h2>
                    <div class="heading-line mb-4"></div>
                    <p class="text-muted mb-4">CampusHub is a digital ecosystem designed to blend academic excellence with vibrant student life. We bridge the gap between administrators, student groups, and individual learners to build an interactive community environment.</p>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <h6 class="text-white"><i class="fa-solid fa-circle-check text-cyan me-2"></i> Smart Networking</h6>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <h6 class="text-white"><i class="fa-solid fa-circle-check text-cyan me-2"></i> Real-time Alerts</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="announcements" class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="text-white fw-bold">Recent Announcements</h2>
                <p class="text-muted">Stay updated with the latest institutional notices</p>
                <div class="heading-line mx-auto"></div>
            </div>
            <div class="table-responsive rounded-4 border border-secondary border-opacity-20 shadow-lg">
                <table class="table table-hover align-middle mb-0 custom-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Topic</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $xml_file = 'admin/announcements.xml';
                        if (!file_exists($xml_file)) {
                            $xml_file = 'announcements.xml';
                        }

                        if (file_exists($xml_file)) {
                            $xml = simplexml_load_file($xml_file);
                            $announcements_array = [];
                            foreach ($xml->announcement as $announcement) {
                                $announcements_array[] = $announcement;
                            }
                            $announcements_array = array_reverse($announcements_array);

                            foreach ($announcements_array as $notice) {
                                $date = htmlspecialchars($notice->date);
                                $title = htmlspecialchars($notice->title);
                                $description = htmlspecialchars($notice->description);
                        ?>
                                <tr>
                                    <td class="text-cyan font-monospace"><?php echo $date; ?></td>
                                    <td class="text-white fw-semibold"><?php echo $title; ?></td>
                                    <td class="text-muted small"><?php echo $description; ?></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2 py-1"><i class="fa-solid fa-circle-dot me-1 small"></i> Live</span></td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-center text-muted py-4">No recent announcements available.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="register-event" class="py-5 bg-darker">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-container p-4 p-md-5 shadow-lg">
                        <div class="text-center mb-4">
                            <h3 class="text-white fw-bold">Event Registration</h3>
                            <p class="text-muted small">Secure your spot for upcoming premium tech bootcamps and competitions.</p>
                            <div class="heading-line mx-auto"></div>
                        </div>

                        <?php if (!empty($status_message)): ?>
                            <div class="alert alert-<?= $status_type; ?> alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-circle-info me-2"></i> <?= $status_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="index.php#register-event" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-light small fw-medium">Full Name</label>
                                    <input type="text" class="form-control custom-input"
                                        value="<?= isset($_SESSION['student']) ? htmlspecialchars($_SESSION['student']['full_name']) : ''; ?>"
                                        placeholder="Your Full Name" readonly required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light small fw-medium">Student ID</label>
                                    <input type="text" class="form-control custom-input" value="ST-<?= isset($_SESSION['student']) ? sprintf("%03d", $_SESSION['student']['student_id']) : ''; ?>"
                                        placeholder="Student ID" readonly required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-light small fw-medium">Select Event</label>
                                    <select class="form-select custom-input" name="selected_event" required>
                                        <option value="" selected disabled>Choose Event...</option>
                                        <?php
                                        if ($all_events_rs->num_rows > 0) {
                                            while ($ev = $all_events_rs->fetch_assoc()) {
                                                echo '<option value="' . $ev['event_id'] . '">' . htmlspecialchars($ev['event_name']) . ' (' . date('M d, Y', strtotime($ev['event_date'])) . ')</option>';
                                            }
                                        } else {
                                            echo '<option value="" disabled>No upcoming events found</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" name="btn_register" class="btn btn-cyan w-100 py-3 rounded-3 text-uppercase tracking-wider">Submit Registration</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="gallery" class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="text-white fw-bold">Life at CampusHub</h2>
                <p class="text-muted">Glimpses of activities, tech-fests, and student memories</p>
                <div class="heading-line mx-auto"></div>
            </div>

            <div class="row justify-content-center mb-5">
                <div class="col-md-8">
                    <?php if (!empty($gallery_message)): ?>
                        <div class="alert alert-<?= $gallery_type; ?> alert-dismissible fade show" role="alert">
                            <?= $gallery_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['student'])): ?>
                        <div class="p-4 form-container">
                            <h5 class="text-white mb-3"><i class="fa-solid fa-camera text-cyan me-2"></i> Share Your Campus Memories</h5>
                            <form action="index.php#gallery" method="POST" enctype="multipart/form-data" class="d-flex flex-column flex-md-row gap-3 align-items-center">
                                <input type="file" name="gallery_file" class="form-control custom-input" required>
                                <button type="submit" name="btn_gallery_upload" class="btn btn-cyan px-4 text-nowrap">Upload Photo</button>
                                <a href="memories.php" class="btn btn-outline-info">
                                    <i class=" me-1"></i> Memories
                                </a>
                            </form>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted small"><i class="fa-solid fa-lock me-1"></i> Want to upload photos?
                            <a href="login.php" class="text-cyan">Login as student</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 custom-card tech-card">
                        <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&q=80&w=500" class="card-img-top rounded-top-4" alt="Hackathon">
                        <div class="card-body">
                            <span class="badge bg-soft-cyan mb-2">Hackathon</span>
                            <h5 class="card-title text-white">CodeFest 2026</h5>
                            <p class="card-text text-muted small">The ultimate 24-hour coding challenge for engineering minds.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 custom-card tech-card">
                        <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&q=80&w=500" class="card-img-top rounded-top-4" alt="Exhibition">
                        <div class="card-body">
                            <span class="badge bg-soft-cyan mb-2">Exhibition</span>
                            <h5 class="card-title text-white">Tech Expo</h5>
                            <p class="card-text text-muted small">Showcasing state-of-the-art software developments and UI designs.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 custom-card tech-card">
                        <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&q=80&w=500" class="card-img-top rounded-top-4" alt="Workshop">
                        <div class="card-body">
                            <span class="badge bg-soft-cyan mb-2">Workshop</span>
                            <h5 class="card-title text-white">UI/UX Bootcamp</h5>
                            <p class="card-text text-muted small">Master wireframing, prototyping, and design thinking workflows.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="multimedia" class="py-5 bg-darker border-top border-bottom border-secondary border-opacity-10">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="text-white fw-bold">Multimedia Highlights</h2>
                <p class="text-muted">Watch virtual tours, listen to campus audio briefs or share your creations</p>
                <div class="heading-line mx-auto"></div>
            </div>

            <div class="row justify-content-center mb-5">
                <div class="col-md-8">
                    <?php if (!empty($media_message)): ?>
                        <div class="alert alert-<?= $media_type; ?> alert-dismissible fade show" role="alert">
                            <?= $media_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['student'])): ?>
                        <div class="p-4 form-container">
                            <h5 class="text-white mb-3"><i class="fa-solid fa-clapperboard text-cyan me-2"></i> Upload Video / Audio Clips</h5>
                            <form action="index.php#multimedia" method="POST" enctype="multipart/form-data" class="d-flex flex-column flex-md-row gap-3 align-items-center">
                                <input type="file" name="media_file" class="form-control custom-input" accept=".mp4,.mp3,.wav" required>
                                <button type="submit" name="btn_media_upload" class="btn btn-cyan px-4 text-nowrap"><i class="fa-solid fa-upload me-2"></i>Upload Media</button>
                            </form>
                            <small class="text-muted mt-2 d-block">Supported Formats: <strong>MP4, MP3, WAV</strong></small>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted small"><i class="fa-solid fa-lock me-1"></i> Log in to upload your multimedia entries.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <div class="p-3 custom-card h-100 shadow-lg">
                        <div class="ratio ratio-16x9 rounded-3 overflow-hidden">
                            <video controls poster="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=800">
                                <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                        <h5 class="text-white mt-3 mb-1">Campus Virtual Tour 2026</h5>
                        <p class="text-muted small mb-0">Explore our advanced computer labs, library, and student lounges.</p>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="p-4 custom-card h-100 d-flex flex-column justify-content-center">
                        <div class="text-center mb-4">
                            <div class="p-3 bg-soft-cyan text-cyan rounded-circle d-inline-block mb-3">
                                <i class="fa-solid fa-microphone-lines fs-2"></i>
                            </div>
                            <h4 class="text-white fw-bold">Campus Podcast & Anthem</h4>
                            <p class="text-muted small">Listen to the latest weekly brief from the CampusHub team</p>
                        </div>

                        <div class="p-3 rounded-3 bg-darker border border-secondary border-opacity-10">
                            <label class="form-label text-light small d-block text-center mb-2 font-monospace">CAMPUS_HUB_ANTHEM.MP3</label>
                            <audio controls class="w-100">
                                <source src="https://www.w3schools.com/html/horse.mp3" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact-section" class="py-5">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-container p-4 p-md-5 shadow-lg">
                        <div class="text-center mb-4">
                            <h3 class="text-white fw-bold">Get In Touch</h3>
                            <p class="text-muted small">Have questions? &nbsp; Submit an online inquiry form and our team will get back to you.</p>
                            <div class="heading-line mx-auto"></div>
                        </div>

                        <?php if (!empty($contact_message)): ?>
                            <div class="alert alert-<?= $contact_type; ?> alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-circle-info me-2"></i> <?= $contact_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="index.php#contact-section" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-light small fw-medium">Your Name</label>
                                    <input type="text" name="c_name" class="form-control custom-input"
                                        value="<?= isset($_SESSION['student']) ? htmlspecialchars($_SESSION['student']['full_name']) : ''; ?>"
                                        placeholder="Enter Full Name" required <?= isset($_SESSION['student']) ? 'readonly' : ''; ?>>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light small fw-medium">Email Address</label>
                                    <input type="email" name="c_email" class="form-control custom-input"
                                        value="<?= isset($_SESSION['student']) ? htmlspecialchars($_SESSION['student']['email']) : ''; ?>"
                                        placeholder="name@example.com" required <?= isset($_SESSION['student']) ? 'readonly' : ''; ?>>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-light small fw-medium">Subject</label>
                                    <input type="text" name="c_subject" class="form-control custom-input" placeholder="What is this regarding?" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-light small fw-medium">Message</label>
                                    <textarea name="c_message" rows="4" class="form-control custom-input" placeholder="Type your message here..." required></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" name="btn_contact" class="btn btn-cyan w-100 py-3 rounded-3 text-uppercase tracking-wider">Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer id="contact" class="bg-dark text-light py-5 border-top border-secondary border-opacity-10">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white fw-bold mb-3">CampusHub</h5>
                    <p class="text-muted small lh-lg">A modern platform designed to streamline your campus experience, connect with peers, and manage academic activities seamlessly.</p>
                </div>

                <div class="col-lg-2 col-md-6 shadow-sm">
                    <h5 class="text-white fw-semibold mb-3">Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none small">Home</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none small">Features</a></li>
                        <li class="mb-2"><a href="#about" class="text-muted text-decoration-none small">About Us</a></li>
                        <li class="mb-2"><a href="#contact-section" class="text-muted text-decoration-none small">Contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white fw-semibold mb-3">Contact Us</h5>
                    <ul class="list-unstyled text-muted small">
                        <li class="mb-2"><i class="fa-solid fa-envelope me-2 text-secondary"></i> info@campushub.lk</li>
                        <li class="mb-2"><i class="fa-solid fa-phone me-2 text-secondary"></i> +94 11 234 5678</li>
                        <li class="mb-2"><i class="fa-solid fa-location-dot me-2 text-secondary"></i> Colombo, Sri Lanka</li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white fw-semibold mb-3">Follow Us</h5>
                    <p class="text-muted small mb-3">Stay connected with our community on social platforms.</p>
                    <div class="d-flex gap-3 social-links">
                        <a href="#" class="text-muted transition-all" title="GitHub"><i class="fa-brands fa-github fs-5"></i></a>
                        <a href="#" class="text-muted transition-all" title="LinkedIn"><i class="fa-brands fa-linkedin fs-5"></i></a>
                        <a href="#" class="text-muted transition-all" title="Behance"><i class="fa-brands fa-behance fs-5"></i></a>
                    </div>
                </div>
            </div>

            <hr class="my-4 border-secondary border-opacity-20">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-muted small mb-0">&copy; 2026 CampusHub. Designed by <span class="text-white fw-semibold">Sachini Kaveesha</span>.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <a href="#" class="text-muted text-decoration-none small me-3 bottom-policy-links">Privacy Policy</a>
                    <a href="#" class="text-muted text-decoration-none small bottom-policy-links">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>