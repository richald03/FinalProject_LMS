<?php
session_start();
include '../db.php';

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Check if course_id is provided
if (!isset($_GET['course_id'])) {
    $_SESSION['error_message'] = "Course ID is missing!";
    header("Location: index.php");
    exit();
}

$course_id = $_GET['course_id']; // Safely get course_id

// Handle create and edit module
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_module'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        
        // Handle file upload
        $file_name = null;
        if (isset($_FILES['module_file']) && $_FILES['module_file']['error'] == 0) {
            $file_name = basename($_FILES['module_file']['name']);
            $target_dir = "uploads/modules/";
            $target_file = $target_dir . $file_name;
            
            // Ensure the uploads directory exists
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Move uploaded file to the target directory
            if (!move_uploaded_file($_FILES['module_file']['tmp_name'], $target_file)) {
                $_SESSION['error_message'] = "Failed to upload file.";
                header("Location: module.php?course_id=$course_id");
                exit();
            }
        }

        $sql = "INSERT INTO modules (course_id, name, description, file) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $course_id, $name, $description, $file_name);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Module created successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to create module.";
        }
        $stmt->close();
    }
}

// Handle delete module
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM modules WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = "Module deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to delete module.";
    }
    $delete_stmt->close();
    header("Location: module.php?course_id=$course_id");
    exit();
}

// Fetch modules for a specific course
$modules = [];
$sql = "SELECT * FROM modules WHERE course_id = ? ORDER BY name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $modules[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modules</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: bold;
        }

        .btn-custom {
            font-size: 14px;
            border-radius: 5px;
            text-transform: uppercase;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-create {
            background-color: #4CAF50;
            color: white;
        }

        .btn-create:hover {
            background-color: #45a049;
        }

        .btn-back {
            background-color: #007bff;
            color: white;
            margin-left: 20px;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }

        .module-table {
            margin-top: 40px;
        }

        .module-table th, .module-table td {
            text-align: center;
            padding: 15px;
        }

        .module-table th {
            background-color: #007bff;
            color: white;
        }

        .module-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .module-actions img {
            width: 20px;
            height: 20px;
            margin: 5px;
            cursor: pointer;
        }

        .module-actions img:hover {
            opacity: 0.7;
        }

        .alert {
            margin-top: 20px;
        }

        .module-card {
            background-color: #fafafa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .module-card h4 {
            font-size: 1.4rem;
            color: #007bff;
            margin-bottom: 10px;
        }

        .module-card p {
            font-size: 1rem;
            color: #666;
        }

        .button-group {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Create Modules</h2>

    <!-- Success or Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Create/Edit Module Form -->
    <form method="POST" class="module-form" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Module Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label for="module_file">Upload Module File (Optional)</label>
            <input type="file" id="module_file" name="module_file" class="form-control">
        </div>

        <input type="hidden" name="course_id" value="<?= $course_id; ?>">

        <div class="button-group">
            <button type="submit" name="create_module" class="btn btn-custom btn-create">Create Module</button>
            <a href="course_management.php" class="btn btn-custom btn-back">
                <img src="../images/back.png" alt="Back" style="width: 16px; height: 16px; margin-right: 8px;">
                Back
            </a>
        </div>
    </form>

    <!-- List of Modules -->
    <div class="module-table">
        <h3>Existing Modules</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>File</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $module): ?>
                    <tr>
                        <td><?= htmlspecialchars($module['name']); ?></td>
                        <td><?= htmlspecialchars($module['description']); ?></td>
                        <td><?= $module['file'] ? '<a href="uploads/modules/' . $module['file'] . '" target="_blank">View Module</a>' : 'No file'; ?></td>
                        <td class="module-actions">
                            <a href="edit_module.php?module_id=<?= $module['id']; ?>&course_id=<?= $course_id; ?>">
                                <img src="../images/edit.png" alt="Edit">
                            </a>
                            <a href="module.php?delete_id=<?= $module['id']; ?>&course_id=<?= $course_id; ?>">
                                <img src="../images/trash.png" alt="Delete">
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>