<?php
session_start();
include 'db.php'; // Include database connection

// Redirect if not logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Fetch all modules from the database
$sql_modules = "SELECT * FROM modules ORDER BY name ASC"; // Modify as needed
$result_modules = $conn->query($sql_modules);

$modules = [];
if ($result_modules->num_rows > 0) {
    while ($row = $result_modules->fetch_assoc()) {
        $modules[] = $row;
    }
}

// Handle Image Display
if (isset($_GET['image_id'])) {
    $image_id = intval($_GET['image_id']);
    
    // Fetch image data from the database
    $image_sql = "SELECT file FROM modules WHERE id = ?";
    $stmt = $conn->prepare($image_sql);
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($image_data);
    $stmt->fetch();
    
    // Set the appropriate header for image display (based on the file type)
    header("Content-Type: image/jpeg"); // Adjust based on your image type (jpeg, png, etc.)
    echo $image_data;
    exit;
}

// Handle the "viewed" checkbox submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['module_id'])) {
    $module_id = intval($_POST['module_id']);
    // If the checkbox is checked, $viewed will be 1, if unchecked, it will be 0
    $viewed = isset($_POST['viewed']) ? 1 : 0;

    // Update the "viewed" status of the module in the database
    $update_sql = "UPDATE modules SET viewed = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $viewed, $module_id);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modules</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .back-to-dashboard {
            position: absolute;
            top: 15px;
            left: 15px;
        }
        .module-card {
            border: 2px solid black;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            position: relative;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .module-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .module-title {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .module-description {
            margin-top: 10px;
        }

        .module-image {
            margin-top: 15px;
        }

        .viewed-checkbox {
            position: absolute;
            bottom: 15px;
            right: 20px;
        }

        .viewed-label {
            font-size: 0.9rem;
        }

        /* Additional Styling for Responsiveness */
        @media (max-width: 768px) {
            .module-card {
                padding: 15px;
            }

            .module-title {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 576px) {
            .module-title {
                font-size: 1rem;
            }

            .module-description {
                font-size: 0.9rem;
            }

            .module-card {
                padding: 10px;
            }
        }
    </style>
</head>

<body>

<!-- Back to Dashboard Button -->
<a href="view_courses.php" class="back-to-dashboard">
    <img src="../images/back.png" alt="Back to Dashboard" style="height: 24px; width: 24px;">
</a>

<div class="container mt-4">
    <h2 class="text-center mb-4">Modules</h2>

    <?php if (count($modules) > 0): ?>
        <?php foreach ($modules as $module): ?>
            <div class="module-card">
                <h4 class="module-title"><?= htmlspecialchars($module['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="module-description"><?= htmlspecialchars($module['description'], ENT_QUOTES, 'UTF-8') ?></p>

                <!-- Check if an image exists and display it -->
                <?php if (!empty($module['file'])): ?>
                    <div class="module-image">
                        <img src="view_image.php?image_id=<?= $module['id'] ?>" alt="Module Image" class="img-fluid" />
                    </div>
                <?php endif; ?>

                <!-- Viewed Checkbox -->
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                    <div class="viewed-checkbox">
                        <input type="checkbox" id="viewed_<?= $module['id'] ?>" class="form-check-input"
                            name="viewed" value="1" <?= $module['viewed'] == 1 ? 'checked' : ''; ?>
                            onchange="this.form.submit();">
                        <label for="viewed_<?= $module['id'] ?>" class="viewed-label">Viewed</label>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">No modules found.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>