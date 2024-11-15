<?php
session_start();
include 'db.php'; // Include your database connection file

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $title = $_POST['assignment_title'] ?? '';
    $description = $_POST['assignment_description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    // Validate required fields
    if (empty($title) || empty($description) || empty($due_date)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: create_assignment.php");
        exit();
    }

    // Handle file upload
    $file_name = null;
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
        $file_name = basename($_FILES['assignment_file']['name']);
        $target_dir = "uploads/assignments/";
        $target_file = $target_dir . $file_name;

        // Ensure the uploads directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move uploaded file to the target directory
        if (!move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target_file)) {
            $_SESSION['error_message'] = "Failed to upload file.";
            header("Location: create_assignment.php");
            exit();
        }
    }

    // Insert data into the database
    $sql = "INSERT INTO assignments (title, description, due_date, file) VALUES (?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $title, $description, $due_date, $file_name);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Assignment created successfully!";
        } else {
            $_SESSION['error_message'] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Failed to prepare the SQL statement: " . $conn->error;
    }

    // Redirect to the form page
    header("Location: create_assignment.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Styles for Sidebar */
        .sidebar {
            width: 250px;
            background-color: #66a3ff;
            color: #fff;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }

        .sidebar .logo-section img {
            width: 80px;
            height: 80px;
        }

        .sidebar .logo-section h2 {
            font-size: 24px;
            color: #333;
        }

        .sidebar .profile-picture {
            border-radius: 50%;
            width: 80px;
            height: 80px;
        }

        .sidebar nav a {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 10px;
            text-align: center;
            margin-top: 10px;
            border-radius: 5px;
            background-color: #007bff;
        }

        .sidebar nav a:hover {
            background-color: #0056b3;
        }

        .sidebar .dropdown:hover .dropdown-menu {
            display: block;
        }

        .sidebar .dropdown .dropdown-menu {
            display: none;
            position: static;
            float: none;
            background-color: #66a3ff;
        }

        .sidebar .dropdown .dropdown-item {
            color: #fff;
            padding: 8px 20px;
        }

        .sidebar .dropdown .dropdown-item:hover {
            background-color: #0056b3;
        }

        /* Custom Styles for Content */
        .content {
            margin-left: 270px;
            padding: 20px;
        }

        .form-container {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-container h3 {
            text-align: center;
            color: #007bff;
        }

        .form-container form input,
        .form-container form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form-container form button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: #fff;
            border-radius: 5px;
        }

        .form-container form button:hover {
            background-color: #0056b3;
        }

        .alert {
            margin-bottom: 20px;
        }

        /* Make Sidebar and Content Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px;
            }
            .content {
                margin-left: 0;
                padding: 15px;
            }
            .form-container {
                padding: 20px;
            }
            .form-container form button {
                padding: 10px;
            }
            .sidebar .logo-section img {
                width: 60px;
                height: 60px;
            }
            .sidebar nav a {
                padding: 8px;
                font-size: 14px;
            }
            .sidebar .profile-picture {
                width: 60px;
                height: 60px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
                padding: 10px;
            }
            .sidebar nav a {
                padding: 6px;
                font-size: 12px;
            }
            .form-container {
                padding: 15px;
            }
            .form-container form input,
            .form-container form textarea,
            .form-container form button {
                padding: 8px;
            }
            .sidebar .logo-section img {
                width: 50px;
                height: 50px;
            }
            .sidebar .profile-picture {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column">
    <div class="logo-section text-center mb-4">
        <img src="images/logo.png" alt="Logo">
    </div>

    <div class="text-center mb-4">
        <img src="<?php echo $_SESSION['profile_picture'] ?? 'uploads/profile_pictures/default.jpg'; ?>" alt="Profile Picture" class="profile-picture">
        <h3>Teacher's Panel</h3>
        <p>Teacher</p>
    </div>

    <!-- Navigation Links inside Sidebar -->
    <nav>
        <a href="teacher_dashboard.php" class="nav-link">Dashboard</a>
        <a href="course_management.php" class="nav-link">Course Management</a>
        <a href="student_management.php" class="nav-link">Student Management</a>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="gradingDropdown" role="button">Assignment/Grading</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="assignment.php">Assignments</a>
                <a class="dropdown-item" href="grading.php">Grading</a>
            </div>
        </div>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="calendarDropdown" role="button">Calendar/Scheduling</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="calendar.php">Calendar</a>
                <a class="dropdown-item" href="scheduling.php">Scheduling</a>
            </div>
        </div>

        <a href="update_profile.php" class="nav-link">Update Profile</a>
    </nav>

    <!-- Logout Link -->
    <a href="logout.php" class="btn btn-danger mt-auto">Logout</a>
</div>

<!-- Main Content -->
<div class="content">
    <div class="container">
        <h2>Create Assignment</h2>
        
        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <form action="create_assignment.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="assignment_title">Assignment Title</label>
                <input type="text" id="assignment_title" name="assignment_title" class="form-control" placeholder="Enter assignment title" required>
            </div>
            <div class="form-group">
                <label for="assignment_description">Assignment Description</label>
                <textarea id="assignment_description" name="assignment_description" class="form-control" rows="5" placeholder="Describe the assignment" required></textarea>
            </div>
            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="assignment_file">Upload File (optional)</label>
                <input type="file" id="assignment_file" name="assignment_file" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Create Assignment</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>