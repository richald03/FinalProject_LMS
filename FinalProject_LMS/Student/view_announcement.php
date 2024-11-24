<?php
session_start();
include '../db.php';

// Redirect if not logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Fetch announcements from the database
$query = "
    SELECT scheduled_events.*, users.email
    FROM scheduled_events 
    LEFT JOIN users ON scheduled_events.teacher_id = users.id 
    ORDER BY scheduled_events.created_at DESC";  
$result = $conn->query($query);

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
    $first_name = 'Student'; // Default fallback
    $last_name = '';         // Default fallback
}

// Close connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement</title>
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

        /* New Announcement Card Design */
        .announcement-card {
            background-color: #f8f9fa;
            border: 2px solid #000;  
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .announcement-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .announcement-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #0056b3;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .announcement-details {
            font-size: 1.1rem;
            color: #333;
            margin-top: 15px;
        }

        .announcement-footer {
            font-size: 0.95rem;
            color: #6c757d;
            margin-top: 20px;
        }

        .footer-section {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .footer-section div {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .footer-section div strong {
            color: #343a40;
        }

        /* Responsive Design for the Announcement Cards */
        @media (max-width: 768px) {
            .announcement-card {
                padding: 15px;
            }

            .announcement-title {
                font-size: 1.6rem;
            }

            .announcement-details {
                font-size: 1rem;
            }

            .announcement-footer {
                font-size: 0.85rem;
            }

            .footer-section {
                flex-direction: column;
                margin-top: 10px;
            }

            .footer-section div {
                margin-bottom: 10px;
            }
        }

        @media (max-width: 576px) {
            .announcement-card {
                margin-left: 10px;
                margin-right: 10px;
            }

            .announcement-title {
                font-size: 1.4rem;
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
        display: none; /* Hide burger button on larger screens */
    }

    .sidebar {
        transform: translateX(0); /* Always visible on larger screens */
    }

    .content {
        margin-left: 250px; /* Content margin for larger screens */
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
        <a href="student_dashboard.php" class="nav-link">Dashboard</a>
        <a href="view_courses.php" class="nav-link">Courses</a>
        <a href="view_grades.php" class="nav-link">Grades</a>
        <a href="view_announcement.php" class="nav-link">Announcements</a>
        <a href="update_profile.php" class="nav-link">Update Profile</a>
    </nav>

    <!-- Logout Link -->
    <a href="logout.php" class="btn btn-danger mt-auto">Logout</a>
</div>

<!-- Content Section -->
<div class="content">
    <div class="container">
        <h2 class="text-center">Latest Announcements</h2>

        <?php if ($result->num_rows > 0): ?>
            <!-- Loop through the announcements and display each one -->
            <?php while ($announcement = $result->fetch_assoc()): ?>
                <div class="announcement-card">
                    <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                    <div class="announcement-details">
                        <p><?php echo nl2br(htmlspecialchars($announcement['description'])); ?></p>
                    </div>

                    <div class="announcement-footer">
                        <div class="footer-section">
                            <div>
                                <strong>Due Date:</strong> <?php echo $announcement['date']; ?>
                            </div>
                            <div>
                                <strong>Time:</strong> <?php echo $announcement['time']; ?> - <?php echo $announcement['end_time']; ?>
                            </div>
                        </div>

                        <div class="footer-section">
                            <div>
                                <strong>Posted By:</strong> <?php echo htmlspecialchars($announcement['email']); ?>
                            </div>
                            <div>
                                <strong>Posted On:</strong> 
                                <?php
                                    $created_at = strtotime($announcement['created_at']);
                                    echo date('g:i A, F j, Y', $created_at); // Format to 12-hour
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No announcements available at the moment.</p>
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