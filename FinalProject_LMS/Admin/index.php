<?php
session_start();

// Redirect to login if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: /FinalProject_LMS/login.php");
    exit();
}

// Include the database connection file
require '../db.php';

// Initialize counts to avoid undefined variable warnings
$student_count = 0;
$teacher_count = 0;
$course_count = 0;
$department_count = 0;
$program_count = 0;

// Query to count students
$student_count_query = "SELECT COUNT(*) AS student_count FROM users WHERE user_type = 'student'";
if ($student_count_result = $conn->query($student_count_query)) {
    $student_count = $student_count_result->fetch_assoc()['student_count'];
}

// Query to count teachers
$teacher_count_query = "SELECT COUNT(*) AS teacher_count FROM users WHERE user_type = 'teacher'";
if ($teacher_count_result = $conn->query($teacher_count_query)) {
    $teacher_count = $teacher_count_result->fetch_assoc()['teacher_count'];
}

// Query to count courses
$course_count_query = "SELECT COUNT(*) AS course_count FROM courses";
if ($course_count_result = $conn->query($course_count_query)) {
    $course_count = $course_count_result->fetch_assoc()['course_count'];
}

// Query to count programs
$program_count_query = "SELECT COUNT(*) AS program_count FROM programs";
if ($program_count_result = $conn->query($program_count_query)) {
    $program_count = $program_count_result->fetch_assoc()['program_count'];
}

// Query to count departments
$department_count_query = "SELECT COUNT(*) AS department_count FROM departments";
if ($department_count_result = $conn->query($department_count_query)) {
    $department_count = $department_count_result->fetch_assoc()['department_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TipTopLearn - Admin Panel</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        border: 2px solid #007bff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            height: 100%; /* Ensure cards have consistent height */

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
    .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
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

        /* Small Screens (Sidebar at the top) */

        .table img {
            width: 100%;
            max-width: 120px;
            height: auto;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
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

@media (max-width: 430px) {
    #sidebar {
        top: 0;
        left: 0;
        width: 77.5%; 
        height: 100%;
        background-color: #66a3ff;  
    }

    .sidebar a {
        width: 390px;
    }
}

@media (max-width: 380px) {
    #sidebar {
        top: 0;
        left: 0;
        width: 70%; 
        height: 100%;
        background-color: #66a3ff;  
    }

    .sidebar a {
        width: 340px;
    }
}

/* Sidebar fuly expanded on small screens */
@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%; 
        height: 100vh;
        background-color: #66a3ff;
        transform: translateX(-100%); 
        z-index: 1050; 
        transition: transform 0.3s ease-in-out;
    }

    .sidebar.show {
        transform: translateX(0); 
    }

    .content {
        display: block; 
        transition: opacity 0.3s ease-in-out;
    }

    .content.hide {
        display: none; 
    }
}


    /* Custom Styles for Layout */
    .main-content {
        margin-left: 250px;
        padding: 20px;
    }
    .dashboard-title {
        text-align: center;
        background-color: #f0f0f0; 
        padding: 20px;
        margin-bottom: 20px;
        font-size: 24px;
    }
    .card-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    .card {
        flex: 1 1 calc(25% - 40px); 
        margin: 10px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .card img.avatar {
        border-radius: 50%;
        width: 100px;
        height: 100px;
        margin-bottom: 15px;
    }
    .card button {
        margin-top: 10px;
    }

    /* Responsive Design: Adjust card size for smaller screens */
    @media (max-width: 1200px) {
        .card {
            flex: 1 1 calc(33.333% - 40px); 
        }
    }
    @media (max-width: 900px) {
        .card {
            flex: 1 1 calc(50% - 40px); 
        }
    }
    @media (max-width: 600px) {
        .card {
            flex: 1 1 calc(100% - 40px); 
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
<div class="main-content">
    <div class="dashboard-title">Dashboard</div>
    <div class="card-container">
        <!-- Students Card -->
        <div class="card">
            <img src="../images/student_icon.png" alt="Students" class="avatar">
            <div class="card-body">
                <h5 class="card-title">Students</h5>
                <p class="card-text"><?php echo $student_count; ?></p>
            </div>
        </div>

        <!-- Teachers Card -->
        <div class="card">
            <img src="../images/teacher_icon.png" alt="Teachers" class="avatar">
            <div class="card-body">
                <h5 class="card-title">Teachers</h5>
                <p class="card-text"><?php echo $teacher_count; ?></p>
            </div>
        </div>

        <!-- Courses Card -->
        <div class="card">
            <img src="../images/course_icon.png" alt="Courses" class="avatar">
            <div class="card-body">
                <h5 class="card-title">Courses</h5>
                <p class="card-text"><?php echo $course_count; ?></p>
            </div>
        </div>

        <!-- Program Card -->
        <div class="card">
            <img src="../images/program_icon.png" alt="Programs" class="avatar">
            <div class="card-body">
                <h5 class="card-title">Programs</h5>
                <p class="card-text"><?php echo $program_count; ?></p>
            </div>
        </div>

        <!-- Departments Card -->
        <div class="card">
            <img src="../images/department_icon.png" alt="Departments" class="avatar">
            <div class="card-body">
                <h5 class="card-title">Departments</h5>
                <p class="card-text"><?php echo $department_count; ?></p>
            </div>
        </div>
    </div>

        <!-- Canvas Elements for Charts -->
        <div class="charts-container">
        <canvas id="studentsChart" width="400" height="400"></canvas>
        <canvas id="teachersChart" width="400" height="400"></canvas>
        <canvas id="coursesChart" width="400" height="400"></canvas>
        <canvas id="programsChart" width="400" height="400"></canvas>
        <canvas id="departmentsChart" width="400" height="400"></canvas>
    </div>
</div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>

document.getElementById('burgerToggle').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show'); // Toggle 'show' class
    });

$(document).ready(function() {
    // Fetch data and initialize the student chart
    $.ajax({
        url: 'fetch_student_data.php',
        method: 'GET',
        success: function(data) {
            console.log("Student data fetched:", data); // Log data
            var parsedData = JSON.parse(data);
            var ctx = document.getElementById('studentsChart').getContext('2d');
            var studentChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: parsedData.programs,
                    datasets: [{
                        label: 'Students',
                        data: parsedData.counts,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Total Enrollees in Each Program'
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Error fetching student data:", status, error);
        }
    });

    // Fetch data and initialize the teachers chart
    $.ajax({
        url: 'fetch_teacher_data.php',
        method: 'GET',
        success: function(data) {
            console.log("Teacher data fetched:", data); // Log data
            var parsedData = JSON.parse(data);
            var ctx = document.getElementById('teachersChart').getContext('2d');
            var teacherChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: parsedData.departments,
                    datasets: [{
                        label: 'Teachers',
                        data: parsedData.counts,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Total Teachers in Each Department'
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Error fetching teacher data:", status, error);
        }
    });

    // Fetch data and initialize the courses chart
    $.ajax({
        url: 'fetch_course_data.php',
        method: 'GET',
        success: function(data) {
            console.log("Course data fetched:", data); // Log data
            var parsedData = JSON.parse(data);
            var ctx = document.getElementById('coursesChart').getContext('2d');
            var courseChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: parsedData.departments,
                    datasets: [{
                        label: 'Courses',
                        data: parsedData.counts,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Total Courses in Each Department'
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Error fetching course data:", status, error);
        }
    });

    // Fetch data and initialize the programs chart
    $.ajax({
        url: 'fetch_program_data.php',
        method: 'GET',
        success: function(data) {
            console.log("Program data fetched:", data); // Log data
            var parsedData = JSON.parse(data);
            var ctx = document.getElementById('programsChart').getContext('2d');
            var programChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: parsedData.departments,
                    datasets: [{
                        label: 'Programs',
                        data: parsedData.counts,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Total Programs in Each Department'
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Error fetching program data:", status, error);
        }
    });

    // Fetch data and initialize the departments chart
    $.ajax({
        url: 'fetch_department_data.php',
        method: 'GET',
        success: function(data) {
            console.log("Department data fetched:", data); // Log data
            var parsedData = JSON.parse(data);
            var ctx = document.getElementById('departmentsChart').getContext('2d');
            var departmentChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: parsedData.departments,
                    datasets: [{
                        label: 'Departments',
                        data: parsedData.counts,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Total Departments'
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Error fetching department data:", status, error);
        }
    });
});
</script>

</body>
</html>