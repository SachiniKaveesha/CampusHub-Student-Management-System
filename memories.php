<?php
session_start();

if (!isset($_SESSION['student'])) {
    header('Location: login.php'); 
    exit();
}

include('connection.php');
Database::setUpConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub | Student Memories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body {
            background: #090812;
            color: #fff;
        }
        .memory-card {
            background: #16142c;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 0.2s;
        }
        .memory-card:hover {
            transform: translateY(-5px);
        }
        .img-container img {
            height: 250px;
            width: 100%;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }
    </style>
</head>
<body>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="text-info fw-bold"><i class="fa-solid fa-camera-retro me-2"></i> Campus Memories</h2>
                <p class="text-muted mb-0">Glimpses of activities, tech-fests, and student memories shared by everyone.</p>
            </div>
            <a href="index.php#gallery" class="btn btn-sm btn-outline-secondary text-white">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Home
            </a>
        </div>

        <div class="row">
            <?php
            $folder_path = "uploads/gallery/";
            
            $images = glob($folder_path . "*.{jpg,jpeg,png,webp}", GLOB_BRACE);

            if (!empty($images)) {
                $images = array_reverse($images);

                foreach ($images as $image) {
            ?>
                    <div class="col-md-4 mb-4">
                        <div class="card memory-card rounded shadow-sm p-2">
                            <div class="img-container">
                                <img src="<?= htmlspecialchars($image); ?>" class="card-img-top rounded" alt="Campus Memory">
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '<div class="col-12 text-center text-muted py-5">
                        <i class="fa-regular fa-image fa-3x mb-3 text-secondary"></i>
                        <p>No memories shared yet. Be the first to share a memory!</p>
                      </div>';
            }
            ?>
        </div>
    </div>

</body>
</html>