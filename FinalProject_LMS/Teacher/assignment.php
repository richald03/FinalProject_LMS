<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Handle delete assignment
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM assignments WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = "Assignment deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to delete assignment.";
    }
    $delete_stmt->close();
    header("Location: assignment.php");
    exit();
}

// Fetch assignments from the database, ordered by due date (ascending)
$assignments = [];

$sql = "SELECT * FROM assignments ORDER BY due_date ASC"; // Sorting by due date (soonest first)
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Check if there are any assignments
if ($result->num_rows > 0) {
    // Fetch the results into the $assignments array
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
} else {
    $_SESSION['error_message'] = "No assignments found.";
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - Teacher Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .content {
            padding: 20px;
            margin-left: 270px;
        }

        /* List container styling */
        .assignment-list {
            list-style-type: none;
            padding: 0;
        }

        /* Styling for each assignment list item */
        .assignment-item {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .assignment-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }

        .assignment-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0056b3;
        }

        .assignment-info {
            font-size: 1rem;
            color: #555;
            margin-top: 10px;
        }

        .assignment-actions {
            margin-top: 15px;
        }

        .btn-custom {
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 5px;
            text-transform: uppercase;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-custom-edit {
            background-color: #ffbb33;
            color: white;
        }

        .btn-custom-edit:hover {
            background-color: #ffaa00;
        }

        .btn-custom-delete {
            background-color: #ff4d4d;
            color: white;
        }

        .btn-custom-delete:hover {
            background-color: #e60000;
        }

        .alert {
            margin-top: 20px;
        }

        /* Sidebar */
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
            margin-bottom: 10px;
        }

        .sidebar .logo-section h2 {
            font-size: 24px;
            color: #333;
        }

        .sidebar .profile-picture {
            border-radius: 50%;
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
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

        /* Make Sidebar Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px;
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
        <img src="../images/logo.png" alt="Logo">
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
        <h2>Manage Assignments</h2>

        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <!-- Assignments List -->
        <ul class="assignment-list">
    <?php if (empty($assignments)): ?>
        <li class="alert alert-warning">No assignments found.</li>
    <?php else: ?>
        <?php foreach ($assignments as $assignment): ?>
            <li class="assignment-item">
                <div class="assignment-title">
                    <?= htmlspecialchars($assignment['title']); ?>
                </div>
                <div class="assignment-info">
                    <p><strong>Description:</strong> <?= htmlspecialchars($assignment['description']); ?></p>
                    <p><strong>Due Date:</strong> <?= htmlspecialchars($assignment['due_date']); ?></p>
                    <p><strong>File:</strong>
                        <?php if ($assignment['file']): ?>
                            <a href="uploads/assignments/<?= $assignment['file']; ?>" target="_blank">View Assignment</a>
                        <?php else: ?>
                            No File
                        <?php endif; ?>
                    </p>
                </div>
                <div class="assignment-actions">
                    <!-- Edit Icon (Favicon) -->
                    <a href="edit_assignment.php?id=<?= $assignment['id']; ?>">
                        <img src="../images/edit.png" alt="Edit" style="width: 24px; height: 24px; cursor: pointer;">
                    </a>

                    <!-- Delete Icon (Favicon) -->
                    <a href="?delete_id=<?= $assignment['id']; ?>" onclick="return confirm('Are you sure you want to delete this assignment?');">
                        <img src="../images/bin.png" alt="Delete" style="width: 24px; height: 24px; cursor: pointer;">
                    </a>
                </div>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>

    </div>
</div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>