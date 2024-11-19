<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: ../index.php");
    exit();
}
// Query to fetch the number of students
$sql = "SELECT COUNT(*) AS total_students FROM users WHERE user_type = 'student'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_students = $row['total_students'];

// Query to fetch the number of courses
$sql_courses = "SELECT COUNT(*) AS total_courses FROM courses";
$result_courses = $conn->query($sql_courses);
$row_courses = $result_courses->fetch_assoc();
$total_courses = $row_courses['total_courses'];

// Query to fetch the number of assignments
$sql_assignments = "SELECT COUNT(*) AS total_assignments FROM assignments";
$result_assignments = $conn->query($sql_assignments);
$row_assignments = $result_assignments->fetch_assoc();
$total_assignments = $row_assignments['total_assignments'];

// Query to fetch the number of modules
$sql_modules = "SELECT COUNT(*) AS total_modules FROM modules";
$result_modules = $conn->query($sql_modules);
$row_modules = $result_modules->fetch_assoc();
$total_modules = $row_modules['total_modules'];

// Query to fetch the number of quizzes
$sql_quizzes = "SELECT COUNT(*) AS total_quizzes FROM quizzes";
$result_quizzes = $conn->query($sql_quizzes);
$row_quizzes = $result_quizzes->fetch_assoc();
$total_quizzes = $row_quizzes['total_quizzes'];

// Query to fetch the number of scheduled events
$sql_events = "SELECT COUNT(*) AS total_events FROM scheduled_events";
$result_events = $conn->query($sql_events);
$row_events = $result_events->fetch_assoc();
$total_events = $row_events['total_events'];

// Search functionality
$search_term = '';
if (isset($_GET['search'])) {
    $search_term = $_GET['search'];
    $search_sql = "SELECT * FROM users WHERE user_type = 'student' AND (first_name LIKE '%$search_term%' OR last_name LIKE '%$search_term%')";
} else {
    $search_sql = "SELECT * FROM users WHERE user_type = 'student'";
}

$search_result = $conn->query($search_sql);

// Close the connection
$conn->close();
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

        /* Custom border and card styling */
    .dashboard-card {
        border: 2px solid #007bff; /* Blue border */
        border-radius: 10px; /* Rounded corners */
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Soft shadow */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    /* Add hover effect to cards */
    .dashboard-card:hover {
        transform: translateY(-5px); /* Slight lift effect */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); /* Stronger shadow on hover */
    }

    /* Style for the icons inside the cards */
    .favicon {
        width: 50px;
        height: 50px;
        margin-bottom: 15px;
    }

    /* Title inside cards */
    .dashboard-card h5 {
        font-weight: bold;
        font-size: 1.2rem;
        margin-bottom: 10px;
    }

    /* Paragraph text styling */
    .dashboard-card p {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
    }

    /* Customize the container's layout and spacing */
    .container {
        padding: 20px;
    }

    /* Hover effect for the whole card */
    .dashboard-card {
        background-color: #fff;
        border: 2px solid #ddd;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: 0.3s;
    }

    /* Hover effect on the dashboard card */
    .dashboard-card:hover {
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        transform: translateY(-5px);
    }
        .search-bar {
            margin-bottom: 20px;
        }
        .favicon {
            width: 45px;
            height: 40px;
            margin-right: 3px;
        }

            /* Image container for favicon */
            .search-icon-container {
                display: inline-block;
                width: 25px; 
                height: 22px; 
        }

              /* Style the favicon icon */
            .search-icon {
                width: 100%;
                height: 100%;
                object-fit: contain; 
        }

        /* Real-Time Clock */
        .clock {
        position: fixed;
        top: 10px;
        right: 20px;
        background-color: #333;
        color: #fff;
        padding: 5px 15px;
        border-radius: 5px;
        font-size: 18px;
        font-weight: bold;
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

<div id="clock" class="clock">
    <!-- Clock with Date will be displayed here -->
</div>

<!-- Main Content -->
<div class="content">
    <div id="dashboard">
        <div class="container my-4">
            <h1 class="text-center mb-4">My Dashboard</h1>
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <img src="../images/student.png" alt="Total Students" class="favicon">
                        <h5>Total Students</h5>
                        <!-- Displaying the number of students dynamically -->
                        <p id="total-students"><?php echo $total_students; ?></p>
                    </div>
                </div>
                <!-- Total Courses Card -->
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <img src="../images/course.png" alt="Total Courses" class="favicon">
                        <h5>Total Courses</h5>
                        <!-- Displaying the number of courses dynamically -->
                        <p id="total-courses"><?php echo $total_courses; ?></p>
                    </div>
                </div>
                <!-- Total Assignments Card -->
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <img src="../images/assignment.png" alt="Total Assignments" class="favicon">
                        <h5>Number of Assignments</h5>
                        <!-- Displaying the number of assignments -->
                        <p id="total-assignments"><?php echo $total_assignments; ?></p>
                    </div>
                </div>
            </div>
            <div class="row text-center mt-4">
                <!-- Total Modules Card -->
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <img src="../images/modules.png" alt="Total Modules" class="favicon">
                        <h5>Number of Modules</h5>
                        <p id="total-modules"><?php echo $total_modules; ?></p>
                    </div>
                </div>
                <!-- Total Quizzes Card -->
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <img src="../images/quizzes.png" alt="Total Quizzes" class="favicon">
                        <h5>Number of Quizzes</h5>
                        <p id="total-quizzes"><?php echo $total_quizzes; ?></p>
                    </div>
                </div>
                <!-- Total Scheduled Events Card -->
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <img src="../images/schedule.png" alt="Total Scheduled Events" class="favicon">
                        <h5>Pending Schedules</h5>
                        <p id="total-events"><?php echo $total_events; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Search Bar and Student Table -->
<div class="mt-4">
    <!-- Search Form -->
    <form method="GET" action="" class="form-inline mb-4">
        <!-- Search by Name (Search Bar) -->
        <div class="form-group">
            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by student name" style="width: 300px;">
        </div>
        
        <!-- Custom Search Button with Favicon -->
        <button type="submit" class="btn btn-custom ml-2">
            <!-- Image container for favicon -->
            <span class="search-icon-container">
                <img src="../images/search.png" alt="Search Icon" class="search-icon">
            </span>
        </button>
    </form>

    <!-- Students Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Profile Picture</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Student ID</th>
                    <th>Gender</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if there are any results
                if ($search_result->num_rows > 0) {
                    // Loop through the results
                    while ($row = $search_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><img src='" . $row['profile_picture'] . "' alt='Profile Picture' class='rounded-circle' width='50' height='50'></td>";
                        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "<td>" . $row['student_id'] . "</td>";
                        echo "<td>" . ucfirst($row['gender']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No students found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>

    // Function to update the clock and date
function updateClock() {
    var now = new Date();
    
    // Get current time
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // 12-hour format
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;
    
    var timeString = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
    
    // Get current date
    var daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var day = daysOfWeek[now.getDay()];
    var date = now.getDate();
    var month = months[now.getMonth()];
    var year = now.getFullYear();
    
    var dateString = day + ', ' + month + ' ' + date + ', ' + year;

    // Update the clock and date div
    document.getElementById('clock').innerText = dateString + ' | ' + timeString;
}

// Update the clock every second
setInterval(updateClock, 1000);

// Initialize the clock on page load
updateClock();

    // Cancel button to go back to the dashboard
    document.getElementById('cancel-button').addEventListener('click', function() {
        document.getElementById('update-profile').style.display = 'none';
        document.getElementById('dashboard').style.display = 'block';
    });
</script>

</body>
</html>