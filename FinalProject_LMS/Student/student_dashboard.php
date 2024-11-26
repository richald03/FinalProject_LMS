<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Query to fetch counts
$sql_courses = "SELECT COUNT(*) AS total_courses FROM courses";
$result_courses = $conn->query($sql_courses);
$row_courses = $result_courses->fetch_assoc();
$total_courses = $row_courses['total_courses'];

$sql_modules = "SELECT COUNT(*) AS total_modules FROM modules";
$result_modules = $conn->query($sql_modules);
$row_modules = $result_modules->fetch_assoc();
$total_modules = $row_modules['total_modules'];

$sql_assignments = "SELECT COUNT(*) AS total_assignments FROM assignments";
$result_assignments = $conn->query($sql_assignments);
$row_assignments = $result_assignments->fetch_assoc();
$total_assignments = $row_assignments['total_assignments'];

$sql_quizzes = "SELECT COUNT(*) AS total_quizzes FROM quizzes";
$result_quizzes = $conn->query($sql_quizzes);
$row_quizzes = $result_quizzes->fetch_assoc();
$total_quizzes = $row_quizzes['total_quizzes'];

// Teacher Search functionality
$search_term = '';
if (isset($_GET['search'])) {
    $search_term = $_GET['search'];
    $search_sql = "SELECT * FROM users WHERE user_type = 'teacher' AND (first_name LIKE '%$search_term%' OR last_name LIKE '%$search_term%')";
} else {
    $search_sql = "SELECT * FROM users WHERE user_type = 'teacher'";
}
$search_result = $conn->query($search_sql);


// Fetch the default profile picture from the database or use a static fallback
$query = "SELECT profile_picture FROM users WHERE user_type = 'default'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $profile_picture = $row['profile_picture'];
} else {
    $profile_picture = 'uploads/profile_pictures/default.jpg';
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

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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

@media (max-width: 420px) {

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 76.5%; /* Occupy full width */
    height: 102vh; /* Full height */
    background-color: #66a3ff;
    transform: translateX(-100%); /* Initially hidden */
    z-index: 1050; /* Stay above other elements */
    transition: transform 0.3s ease-in-out;
    
}

#sidebar .btn{
    margin-top: 300px;
    width: 400px;
}

.sidebar nav a {
    width: 370px;
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

    .sidebar nav a {
        width: 410px;
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
    <div id="dashboard">
        <div class="container my-4">
            <h1 class="text-center mb-4">Student Dashboard</h1>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <img src="../images/course.png" alt="Total Courses" class="favicon">
                    <h5>Total Courses</h5>
                    <p><?php echo $total_courses; ?></p>
                </div>
                <div class="dashboard-card">
                    <img src="../images/modules.png" alt="Total Modules" class="favicon">
                    <h5>Total Modules</h5>
                    <p><?php echo $total_modules; ?></p>
                </div>
                <div class="dashboard-card">
                    <img src="../images/assignment.png" alt="Total Assignments" class="favicon">
                    <h5>Assignments</h5>
                    <p><?php echo $total_assignments; ?></p>
                </div>
                <div class="dashboard-card">
                    <img src="../images/quizzes.png" alt="Total Quizzes" class="favicon">
                    <h5>Quizzes</h5>
                    <p><?php echo $total_quizzes; ?></p>
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
            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search teacher's name" style="width: 300px;">
        </div>
        
        <!-- Custom Search Button with Favicon -->
        <button type="submit" class="btn btn-custom ml-2">
            <!-- Image container for favicon -->
            <span class="search-icon-container">
                <img src="../images/search.png" alt="Search Icon" class="search-icon">
            </span>
        </button>
    </form>

    <!-- Teachers Table -->
    <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Profile Picture</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($search_result->num_rows > 0) {
                        while ($row = $search_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><img src='" . $row['profile_picture'] . "' alt='Profile Picture'></td>";
                            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>" . ucfirst($row['gender']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>No teachers found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
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