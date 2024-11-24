<?php
session_start();
include '../db.php';

// Ensure the user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../index.php");
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
    $first_name = 'Student'; // Default fallback
    $last_name = '';         // Default fallback
}

// Get the student ID from session
$student_id = $_SESSION['user_id'];

// Query to fetch grades for the logged-in student
$sql_grades = "SELECT g.prelim, g.midterm, g.final, g.average, g.grade_equivalent, c.name AS course_name
            FROM grades g
            JOIN courses c ON g.course_id = c.id
            WHERE g.student_id = ?";

$stmt = $conn->prepare($sql_grades);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Close the statement
$stmt->close();

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Grades</title>
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
            .content {
                position: relative;
                width: 100%;
                height: auto;
                padding: 10px;;
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
        /* Grades Table Styling */
        .grades-table {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
        }

        .grades-table th, .grades-table td {
            text-align: center;
            vertical-align: middle;
            padding: 12px 15px;
            border: 1px solid #ddd;
        }

        .grades-table th {
            background-color: #007bff;
            color: white;
        }

        .grades-table td {
            background-color: #f9f9f9;
        }

        .grades-table tr:nth-child(even) td {
            background-color: #f2f2f2;
        }

        .grades-table tr:hover td {
            background-color: #f1f1f1;
        }

        /* Add responsiveness to the table */
        @media (max-width: 768px) {
            .grades-table th, .grades-table td {
                padding: 8px 10px;
                font-size: 14px;
            }

            .grades-table {
                font-size: 12px;
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
        <a href="view_grades.php" class="nav-link"> Grades</a>
        <a href="view_announcement.php" class="nav-link">Announcements</a>
        <a href="update_profile.php" class="nav-link">Update Profile</a>
    </nav>

    <!-- Logout Link -->
    <a href="logout.php" class="btn btn-danger mt-auto">Logout</a>
</div>

<!-- Main Content -->
<div class="content">
    <div class="container">
        <h1 class="text-center my-4">My Grades</h1>

        <!-- Table displaying the grades -->
        <table class="table grades-table">
            <thead class="thead-dark">
                <tr>
                    <th>Course</th>
                    <th>Prelim</th>
                    <th>Midterm</th>
                    <th>Final</th>
                    <th>Average</th>
                    <th>Grade Equivalent</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['prelim']); ?></td>
                            <td><?php echo htmlspecialchars($row['midterm']); ?></td>
                            <td><?php echo htmlspecialchars($row['final']); ?></td>
                            <td><?php echo htmlspecialchars($row['average']); ?></td>
                            <td><?php echo htmlspecialchars($row['grade_equivalent']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No grades available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>