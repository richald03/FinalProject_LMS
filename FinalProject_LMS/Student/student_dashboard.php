<?php
session_start();
include 'db.php';

// Redirect if not logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Sidebar Styling */
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

        /* Content Layout */
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        /* Mobile View - Collapse Sidebar */
        @media (max-width: 992px) {
            .sidebar {
                position: absolute;
                width: 100%;
                height: auto;
                padding: 10px;
            }

            .content {
                margin-left: 0;
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

        /* Smaller Screens (for mobile devices) */
        @media (max-width: 768px) {
            .sidebar {
                display: none; /* Hide sidebar */
            }

            .content {
                margin-left: 0;
            }

            .top-right-button {
                position: relative;
                top: 0;
                right: 0;
            }

            .sidebar nav a {
                padding: 8px;
                font-size: 14px;
            }

            /* Profile Picture Scaling */
            .sidebar .profile-picture {
                width: 50px;
                height: 50px;
            }
        }

        /* Mobile Navigation - Show Sidebar when toggled */
        .sidebar-toggler {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar-toggler {
                display: block;
                position: absolute;
                top: 20px;
                left: 20px;
                font-size: 24px;
                color: #fff;
                background: #007bff;
                border: none;
                padding: 10px;
                cursor: pointer;
            }
        }

    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column" id="sidebar">
    <div class="logo-section text-center mb-4">
        <img src="../images/logo.png" alt="Logo">
    </div>

    <div class="text-center mb-4">
    <img src="<?php echo $_SESSION['profile_picture'] ?? 'uploads/profile_pictures/default.jpg'; ?>" alt="Profile Picture" class="profile-picture">
        <h3>Student's Panel</h3>
        <p>Student</p>
    </div>

    <!-- Navigation Links inside Sidebar -->
    <nav>
        <a href="student_dashboard.php" class="nav-link">Dashboard</a>
        <a href="view_courses.php" class="nav-link">Courses</a>
        <a href="view_grades.php" class="nav-link"> Grades</a>
        <a href="view_announcements.php" class="nav-link">Announcements</a>
        <a href="update_profile.php" class="nav-link">Update Profile</a>
    </nav>

    <!-- Logout Link -->
    <a href="logout.php" class="btn btn-danger mt-auto">Logout</a>
</div>

<!-- Content Section -->
<div class="content">
    <!-- Your content goes here -->
</div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>