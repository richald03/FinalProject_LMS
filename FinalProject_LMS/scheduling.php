<?php
session_start();

// Ensure the teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TipTopLearn - My Schedules</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
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
            margin-top: 10px;
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
            padding: 40px;
            font-family: 'Roboto', sans-serif;
        }

        .event-list {
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
        }

        .event-list h3 {
            text-align: center;
            color: #007bff;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .event-item {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .event-item:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .event-item h5 {
            font-size: 22px;
            color: #333;
            font-weight: 600;
        }

        .event-item p {
            margin: 5px 0;
            color: #666;
        }

        .event-item .date-time {
            font-weight: bold;
            color: #007bff;
        }

        .alert {
            margin-bottom: 20px;
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
        <img src="profile-placeholder.png" alt="Profile Picture" class="profile-picture">
        <h3>Teacher's Panel</h3>
        <p>Teacher</p>
    </div>
<!-- Navigation Links inside Sidebar -->
    <nav>
        <a href="teacher_dashboard.php" class="nav-link">Dashboard</a>
        <a href="course_management.php" class="nav-link">Course Management</a>
        <a href="student-management.php" class="nav-link">Student Management</a>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="gradingDropdown" role="button">Assignment/Grading</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="assignments.php">Assignments</a>
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
    <div class="event-list">
        <h3>My Scheduled Events</h3>

        <?php if (isset($_SESSION['scheduled_events']) && count($_SESSION['scheduled_events']) > 0): ?>
            <?php foreach ($_SESSION['scheduled_events'] as $event): ?>
                <div class="event-item">
                    <h5><?php echo htmlspecialchars($event['title']); ?></h5>
                    <p class="date-time"><strong>Date & Time:</strong> <?php echo htmlspecialchars($event['date']); ?> at <?php echo htmlspecialchars($event['time']); ?></p>
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                <strong>No events scheduled yet!</strong> You have not scheduled any events at the moment.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
