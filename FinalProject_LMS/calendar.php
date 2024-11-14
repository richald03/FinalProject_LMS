<?php
session_start();

include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Handling the scheduling form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the form input
    $event_title = $_POST['event_title'];
    $event_date = $_POST['event_date'];
    $event_start_time = $_POST['event_start_time'];
    $event_end_time = $_POST['event_end_time'];
    $event_description = $_POST['event_description'];

    // Validate form inputs
    if (empty($event_title) || empty($event_date) || empty($event_start_time) || empty($event_end_time)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Insert the event into the database
        $query = "INSERT INTO scheduled_events (teacher_id, title, date, time, end_time, description, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssss", $teacher_id, $event_title, $event_date, $event_start_time, $event_end_time, $event_description);

        if ($stmt->execute()) {
            $success_message = "Event scheduled successfully!";
        } else {
            $error_message = "Failed to schedule event. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch scheduled events for the logged-in teacher
$scheduled_events = [];
$query = "SELECT title, date, time, end_time, description FROM scheduled_events WHERE teacher_id = ? ORDER BY date, time";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $scheduled_events[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TipTopLearn - Calendar</title>
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
    <div class="form-container">
        <h3>Schedule an Event</h3>

        <!-- Display success or error message -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Schedule Event Form -->
        <form method="POST" action="calendar.php">
            <input type="text" name="event_title" class="form-control" placeholder="Event Title" required>
            <input type="date" name="event_date" class="form-control" required>
            <label for="event_start_time">Start Time</label>
            <input type="time" name="event_start_time" class="form-control" required>
            <label for="event_end_time">End Time</label>
            <input type="time" name="event_end_time" class="form-control" required>
            <textarea name="event_description" class="form-control" placeholder="Event Description"></textarea>
            <button type="submit">Schedule Event</button>
        </form>
    </div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>