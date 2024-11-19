<?php
session_start();
include 'db.php'; // Include database connection

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Fetch the module ID from the URL
if (isset($_GET['module_id'])) {
    $module_id = $_GET['module_id'];
} else {
    $_SESSION['error_message'] = "Module not found!";
    header("Location: module.php");
    exit();
}

// Fetch module data from the database
$sql = "SELECT * FROM modules WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $module_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Module not found!";
    header("Location: module.php");
    exit();
} else {
    $module = $result->fetch_assoc();
}
$stmt->close();

// Handle the form submission for editing the module
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $current_file = $_POST['current_file']; // Keep the current file if no new file is uploaded

    // Handle file upload
    if (isset($_FILES['module_file']) && $_FILES['module_file']['error'] == 0) {
        $file_name = basename($_FILES['module_file']['name']);
        $target_dir = "uploads/modules/";
        $target_file = $target_dir . $file_name;

        // Ensure the uploads directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['module_file']['tmp_name'], $target_file)) {
            $current_file = $file_name; // Update with the new file name
        } else {
            $_SESSION['error_message'] = "Failed to upload file.";
            header("Location: edit_module.php?module_id=$module_id");
            exit();
        }
    }

    // Update the module in the database
    $sql = "UPDATE modules SET name = ?, description = ?, file = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $description, $current_file, $module_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Module updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update module.";
    }

    $stmt->close();

    // Redirect to the module list or the same page
    header("Location: edit_module.php?module_id=$module_id");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Module</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            color: #333;
        }

        .container {
            max-width: 800px;
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

        .btn-save {
            background-color: #4CAF50;
            color: white;
        }

        .btn-save:hover {
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

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Module</h2>

    <!-- Success or Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Edit Module Form -->
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Module Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($module['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars($module['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="module_file">Upload Module File (Optional)</label>
            <input type="file" id="module_file" name="module_file" class="form-control">
            <small>Current file: <?= $module['file'] ? '<a href="uploads/modules/' . $module['file'] . '" target="_blank">Download</a>' : 'No file uploaded'; ?></small>
        </div>

        <input type="hidden" name="current_file" value="<?= $module['file']; ?>">

        <div class="button-group">
            <button type="submit" class="btn btn-custom btn-save">Save Changes</button>
            <a href="module.php?course_id=<?= $module['course_id']; ?>" class="btn btn-custom btn-back">Back</a>
        </div>
    </form>
</div>

</body>
</html>