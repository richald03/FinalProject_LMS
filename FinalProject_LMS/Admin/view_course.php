<?php
session_start();

// Redirect to login if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection file
require '../db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TipTopLearn - View Courses</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

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
        background-color: #66a3ff; /* Match the sidebar background */
    }
    .sidebar .dropdown .dropdown-item {
        color: #fff;
        padding: 8px 20px;
    }
    .sidebar .dropdown .dropdown-item:hover {
        background-color: #0056b3;
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
        flex: 1 1 calc(20% - 40px); 
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
            flex: 1 1 calc(25% - 40px); 
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
<div class="main-content">
    <h2 class="dashboard-title">View Courses</h2>
    <div class="card-container">
        <?php
        // Fetch courses from the database
        $query = "SELECT id, course_name FROM course";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<div class="card">';
                echo '<img src="../images/course_icon.png" alt="Course Image" class="avatar">';
                echo '<h4>' . htmlspecialchars($row['course_name']) . '</h4>';
                echo '<button class="btn btn-primary" data-toggle="modal" data-target="#detailsModal" data-course-id="' . $row['id'] . '" data-course-name="' . htmlspecialchars($row['course_name']) . '">View Details</button>';
                echo '</div>';
            }
        } else {
            echo '<p>No courses found.</p>';
        }

        // Close the database connection
        mysqli_close($conn);
        ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Course Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6>Department and Program</h6>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Department Name</th>
                                </tr>
                            </thead>
                            <tbody id="departmentTable">
                                <!-- Content will be loaded via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Program Name</th>
                                </tr>
                            </thead>
                            <tbody id="programTable">
                                <!-- Content will be loaded via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <h6>Teachers</h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                        </tr>
                    </thead>
                    <tbody id="teacherTable">
                        <!-- Content will be loaded via JavaScript -->
                    </tbody>
                </table>
                <h6>Students</h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                        </tr>
                    </thead>
                    <tbody id="studentTable">
                        <!-- Content will be loaded via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#detailsModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var courseName = button.data('course-name');
        var courseId = button.data('course-id');

        var modal = $(this);
        modal.find('.modal-title').text(courseName);

        // Fetch department, program, teachers, and students related to the course
        $.ajax({
            url: 'fetch_course_details.php',
            method: 'GET',
            data: { course_id: courseId },
            success: function(response) {
                console.log("AJAX response:", response);
                var courseDetails = JSON.parse(response);

                // Display department name
                var departmentContent = '<tr><td>' + courseDetails.department_name + '</td></tr>';
                $('#departmentTable').html(departmentContent);

                // Display program name
                var programContent = '<tr><td>' + courseDetails.program_name + '</td></tr>';
                $('#programTable').html(programContent);

                // Display teachers
                var teacherContent = '';
                courseDetails.teachers.forEach(function(item) {
                    teacherContent += '<tr><td>' + item.full_name + '</td></tr>';
                });
                $('#teacherTable').html(teacherContent);

                // Display students
                var studentContent = '';
                courseDetails.students.forEach(function(item) {
                    studentContent += '<tr><td>' + item.full_name + '</td></tr>';
                });
                $('#studentTable').html(studentContent);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + error);
            }
        });
    });
});

    document.getElementById('burgerToggle').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show'); 
    });

</script>

<!-- Bootstrap JS and jQuery -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>