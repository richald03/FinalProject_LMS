<?php
session_start();

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
    <title>TipTopLearn - Teacher's Panel</title>
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

        /* Dropdown hover styles */
        .sidebar .dropdown:hover .dropdown-menu {
            display: block;
        }
        .sidebar .dropdown .dropdown-menu {
            display: none;
            position: static;
            float: none;
            background-color: #66a3ff; /* Match the sidebar background */
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
            margin-left: 250px;
            padding: 20px;
        }
        .update-profile-form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .update-profile-form h2 {
            color: #007bff;
            margin-bottom: 20px;
            text-align: center;
        }
        .cancel-btn {
            width: 100%;
            padding: 12px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            color: #333;
            font-size: 16px;
        }
        .cancel-btn:hover {
            background-color: #e1e1e1;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column">
    <div class="logo-section text-center mb-4">
        <img src="images/logo.png" alt="Logo">
        <h2>TipTopLearn</h2>
    </div>

    <div class="text-center mb-4">
        <img src="profile-placeholder.png" alt="Profile Picture" class="profile-picture">
        <h3>Teacher's Panel</h3>
        <p>Teacher</p>
    </div>

    <!-- Navigation Links inside Sidebar -->
    <nav>
        <a href="#dashboard" class="nav-link">Dashboard</a>
        <a href="#course-management" class="nav-link">Course Management</a>
        <a href="#student-management" class="nav-link">Student Management</a>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="gradingDropdown" role="button">Assignment/Grading</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#assignments">Assignments</a>
                <a class="dropdown-item" href="#grading">Grading</a>
            </div>
        </div>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="calendarDropdown" role="button">Calendar/Scheduling</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="calendar.php">Calendar</a>
                <a class="dropdown-item" href="#scheduling">Scheduling</a>
            </div>
        </div>

        <a href="#update-profile" class="nav-link">Update Profile</a>
    </nav>

    <!-- Logout Link -->
    <a href="logout.php" class="btn btn-danger mt-auto">Logout</a>
</div>

<!-- Main Content -->
<div class="content">
    <div id="dashboard" style="display:none;">
        <h2>Dashboard</h2>
        <p>Welcome to your dashboard.</p>
    </div>
    
    <div id="course-management" style="display:none;">
        <h2>Course Management</h2>
        <p>Manage your courses here.</p>
    </div>
    
    <div id="student-management" style="display:none;">
        <h2>Student Management</h2>
        <p>Manage students here.</p>
    </div>

    <!-- Update Profile Section -->
    <div id="update-profile" class="update-profile-form" style="display:none;">
        <h2>Update Profile</h2>
        <form>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" id="name" placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" class="form-control" id="email" placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="new-password">New Password</label>
                <input type="password" class="form-control" id="new-password" placeholder="Enter a new password">
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm-password" placeholder="Confirm your new password">
            </div>

            <div class="form-group">
                <label for="profile-picture">Profile Picture</label>
                <input type="file" class="form-control-file" id="profile-picture">
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
            <button type="button" class="cancel-btn mt-3" id="cancel-button">Cancel</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // JavaScript to show the relevant sections when navigation is clicked
    document.querySelectorAll('.sidebar nav a').forEach(function(link) {
        link.addEventListener('click', function() {
            // Hide all content sections
            document.querySelectorAll('.content > div').forEach(function(section) {
                section.style.display = 'none';
            });
            // Show the section corresponding to the clicked link
            const sectionId = link.getAttribute('href').substring(1);
            document.getElementById(sectionId).style.display = 'block';
        });
    });

    // Cancel button to go back to the dashboard
    document.getElementById('cancel-button').addEventListener('click', function() {
        document.getElementById('update-profile').style.display = 'none';
        document.getElementById('dashboard').style.display = 'block';
    });
</script>

</body>
</html>