<?php
session_start();

include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

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
        $query = "INSERT INTO scheduled_events (admin_id, title, date, time, end_time, description, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssss", $admin_id, $event_title, $event_date, $event_start_time, $event_end_time, $event_description);

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
$query = "SELECT title, date, time, end_time, description FROM scheduled_events WHERE admin_id = ? ORDER BY date, time";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
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

        /* Sidebar transition for hide/show effect */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    width: 250px;
    height: 100vh;
    background-color: #66a3ff;
    overflow-y: auto;
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
}

.sidebar.show {
    transform: translateX(0);
}

/* Content shift when sidebar is visible */
.content {
    margin-left: 0;
    transition: margin-left 0.3s ease-in-out;
}

.sidebar.show ~ .content {
    margin-left: 250px;
}


.burger-btn {
    position: fixed;
    top: 10px;
    left: 10px;
    z-index: 1100;
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
}

@media (min-width: 992px) {
    .burger-btn {
        display: none; /* Hide burger button on larger screens */
    }

    .sidebar {
        transform: translateX(0); /* Always visible on larger screens */
    }

    .content {
        margin-left: 250px; /* Content margin for larger screens */
    }
}


@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%; 
        height: 100vh; 
        background-color: #66a3ff;
        transform: translateX(-100%); 
        z-index: 1050; /* Stay above other elements */
        transition: transform 0.3s ease-in-out;
    }

    .sidebar.show {
        transform: translateX(0); 
    }

    .content {
        display: block; /* Default content visibility */
        transition: opacity 0.3s ease-in-out;
    }

    .content.hide {
        display: none; /* Hide content when sidebar is active */
    }
}


@media (min-width 440px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%; 
        height: 100vh; 
        background-color: #66a3ff;
        transform: translateX(-100%); 
        z-index: 1050; /* Stay above other elements */
        transition: transform 0.3s ease-in-out;
    }

    .sidebar.show {
        transform: translateX(0); 
    }

    .content {
        display: block; /* Default content visibility */
        transition: opacity 0.3s ease-in-out;
    }

    .content.hide {
        display: none; /* Hide content when sidebar is active */
    }
}
    </style>
</head>
<body>

<!-- Burger Button for Sidebar -->
<button class="burger-btn" id="burgerToggle">☰</button>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column" id="sidebar">
    <div class="logo-section text-center mb-4">
        <img src="../images/logo.png" alt="Logo">
    </div>

    <!-- Navigation Links inside Sidebar -->
    <nav>
        <a href="../Admin/index.php" class="nav-link">Dashboard</a>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="calendarDropdown" role="button">Announcements</a>
            <div class="dropdown-menu">
                <a href="../Admin/calendar.php" class="nav-link">Calendar</a>
                <a href="../Admin/scheduling.php" class="nav-link">Scheduling</a>
            </div>
        </div>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="calendarDropdown" role="button">Department Management</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="../Admin/view_department.php">View Departments</a>
                <a class="dropdown-item" href="../Admin/manage_department.php">Manage Department</a>                
            </div>
        </div>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="calendarDropdown" role="button">Course Management</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="../Admin/view_course.php">View Courses</a>
                <a class="dropdown-item" href="../Admin/manage_course.php">Manage Course</a>                
            </div>
        </div>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="calendarDropdown" role="button">Program Management</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="../Admin/view_program.php">View Programs</a>
                <a class="dropdown-item" href="../Admin/manage_program.php">Manage Program</a>                
            </div>
        </div>
        
        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="calendarDropdown" role="button">Student Management</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="../Admin/view_student.php">View Students</a>
                <a class="dropdown-item" href="../Admin/manage_student.php">Manage Student</a>                
            </div>
        </div>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="calendarDropdown" role="button">Teacher Management</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="../Admin/view_teacher.php">View Teachers</a>
                <a class="dropdown-item" href="../Admin/manage_teacher.php">Manage Teacher</a>                
            </div>
        </div>

        <a href="../Admin/register.php" class="nav-link">Register</a>
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

    <script>
    document.getElementById('burgerToggle').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show'); // Toggle 'show' class
    });
</script>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>