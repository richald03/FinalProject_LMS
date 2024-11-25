<?php
session_start();
include '../db.php';

// Ensure the teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Fetch the logged-in user's details
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT first_name, last_name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result_user = $stmt->get_result();

if ($result_user && $result_user->num_rows > 0) {
    $user_data = $result_user->fetch_assoc();
    $first_name = $user_data['first_name'];
    $last_name = $user_data['last_name'];
} else {
    $first_name = 'Student'; 
    $last_name = '';         
}


// Get the logged-in teacher's ID
$teacher_id = $_SESSION['user_id'];

// Fetch scheduled events from the database for the logged-in teacher
$query = "SELECT id, title, date, time, end_time, description FROM scheduled_events WHERE teacher_id = ? ORDER BY date, time";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

// Store events in an array
$scheduled_events = [];
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
    <title>Schedules</title>
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

        /* Added Box Shadow, Padding, and Border to Event List */
        .event-list {
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
            border: 1px solid #ddd; /* Add a subtle border */
            margin-bottom: 20px;
        }

        .event-list h3 {
            text-align: center;
            color: #007bff;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        /* Added Hover Effects and Border to Event Items */
        .event-item {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #ddd; /* Border for event items */
            background-color: #ffffff;
        }

        .event-item:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
            border: 1px solid #007bff; /* Highlight border on hover */
        }

        /* Styled Event Title */
        .event-item h5 {
            font-size: 22px;
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }

        /* Styled Paragraphs for Event Description and Date */
        .event-item p {
            margin: 5px 0;
            color: #666;
        }

        .event-item .date-time {
            font-weight: bold;
            color: #007bff;
        }

        /* Alert Style */
        .alert {
            margin-bottom: 20px;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            /* Sidebar for Small Screens */
            .sidebar {
                width: 200px;
                position: relative;
                height: auto;
            }
            .content {
                margin-left: 0;
                padding: 20px;
            }
            .sidebar .logo-section img {
                width: 60px;
                height: 60px;
            }
            .sidebar .profile-picture {
                width: 60px;
                height: 60px;
            }
            .sidebar nav a {
                padding: 8px;
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            /* Sidebar for Very Small Screens */
            .sidebar {
                width: 100%;
                padding: 10px;
            }
            .content {
                margin-left: 0;
                padding: 10px;
            }
            .sidebar .logo-section img {
                width: 50px;
                height: 50px;
            }
            .sidebar .profile-picture {
                width: 50px;
                height: 50px;
            }
            .sidebar nav a {
                font-size: 12px;
                padding: 6px;
            }
            .event-list {
                padding: 15px;
            }
            .event-item {
                padding: 15px;
            }
        }

         /* Small Screens (Sidebar at the top) */
        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 10px;
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
        }
        /* Modal responsiveness */
        @media (max-width: 576px) {
            .modal-dialog {
                max-width: 100%;
                margin: 15px;
            }

            .modal-body {
                padding: 10px;
            }

            .form-control {
                font-size: 14px;
                padding: 8px;
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

/* Navbar button styles */
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
        display: none; 
    }

    .sidebar {
        transform: translateX(0); 
    }

    .content {
        margin-left: 250px; 
    }
}

/* Sidebar fully expanded on small screens */
@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%; /* Occupy full width */
        height: 100vh; /* Full height */
        background-color: #66a3ff;
        transform: translateX(-100%); /* Initially hidden */
        z-index: 1050; /* Stay above other elements */
        transition: transform 0.3s ease-in-out;
    }

    .sidebar.show {
        transform: translateX(0); /* Slide into view */
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

    <div class="text-center mb-4">
    <img src="<?php echo $_SESSION['profile_picture'] ?? 'uploads/profile_pictures/default.jpg'; ?>" alt="Profile Picture" class="profile-picture">
    <h6><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h6>
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
    <div class="event-list">
        <h3>My Scheduled Events</h3>

        <?php if (count($scheduled_events) > 0): ?>
            <?php foreach ($scheduled_events as $event): ?>
                <div class="event-item d-flex justify-content-between align-items-center">
                    <div>
                        <h5><?php echo htmlspecialchars($event['title']); ?></h5>
                        <p class="date-time"><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?> | 
                        <strong>Time:</strong> <?php echo htmlspecialchars($event['time']); ?> - <?php echo htmlspecialchars($event['end_time']); ?></p>
                        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    </div>
                    <!-- Edit and Delete Buttons -->
                    <div class="ml-4 d-flex flex-column align-items-center">
                    <a href="edit_schedule.php?id=<?php echo $event['id']; ?>" title="Edit Event">
                    <img src="../images/edit-button.png" alt="Edit" style="width: 24px; height: 24px; margin-bottom: 10px;">
                    </a>
                    <a href="delete_schedule.php?id=<?php echo $event['id']; ?>" title="Delete Event" onclick="return confirm('Are you sure you want to delete this event?');">
                    <img src="../images/trash.png" alt="Delete" style="width: 24px; height: 24px;">
                    </a>
                </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                <strong>No events scheduled yet!</strong> You have not scheduled any events at the moment.
            </div>
        <?php endif; ?>
    </div>
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