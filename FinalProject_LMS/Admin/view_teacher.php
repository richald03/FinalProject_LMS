<?php
session_start();

// Redirect to login if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection file
require '../db.php';

// Pagination variables
$limit = 15; // Number of teachers per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch teachers from the database
$teachers_query = "
    SELECT 
        t.employee_id, t.first_name, t.last_name, t.email, 
        c.course_name, d.department_name 
    FROM users t
    LEFT JOIN course_users cu ON t.id = cu.user_id 
    LEFT JOIN course c ON cu.course_id = c.id 
    LEFT JOIN departments d ON c.department_id = d.id 
    WHERE t.user_Type = 'teacher' 
    AND (t.employee_id LIKE ? OR t.first_name LIKE ? OR t.last_name LIKE ? OR t.email LIKE ?)
    LIMIT ? OFFSET ?";

$search_param = '%' . $search . '%';
$stmt = $conn->prepare($teachers_query);
$stmt->bind_param("ssssii", $search_param, $search_param, $search_param, $search_param, $limit, $offset);
$stmt->execute();
$teachers_result = $stmt->get_result();

// Count total teachers for pagination
$count_query = "
    SELECT COUNT(*) AS total_teachers 
    FROM users 
    WHERE user_Type = 'teacher' 
    AND (employee_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_teachers = $count_result->fetch_assoc()['total_teachers'];
$total_pages = ceil($total_teachers / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TipTopLearn - Admin Panel</title>
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
        background-color: #66a3ff; /* Match the sidebar background */
    }
    .sidebar .dropdown .dropdown-item {
        color: #fff;
        padding: 8px 20px;
    }
    .sidebar .dropdown .dropdown-item:hover {
        background-color: #0056b3;
    }

    /* Main content styles */
    .main-content {
        margin-left: 250px;
        padding: 20px;
    }

    /* Centering and styling the title */
    .main-content h2 {
        text-align: center;
        background-color: <?php echo '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT); ?>;
        padding: 10px;
        color: #fff;
    }

    /* Styling the search input field */
    .form-inline .form-control {
        position: relative;
    }
    .form-inline .form-control input[type="search"]::-webkit-search-cancel-button {
        -webkit-appearance: searchfield-cancel-button;
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
<div class="sidebar d-flex flex-column" id="sidebar" id="sidebar">
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
    <div class="container-fluid">
        <h2>View Teachers</h2>
        <!-- Search Form -->
        <form method="get" action="view_teacher.php" class="form-inline mb-4">
            <input type="search" name="search" class="form-control mr-2" placeholder="Search teachers..." value="<?php echo htmlspecialchars($search); ?>" aria-label="Search">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Department</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($teachers_result->num_rows > 0): ?>
                    <?php while ($row = $teachers_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['employee_id']; ?></td>
                            <td><?php echo $row['first_name']; ?></td>
                            <td><?php echo $row['last_name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['course_name']; ?></td>
                            <td><?php echo $row['department_name']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No teachers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination Links -->
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if ($page == 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=1&search=<?= htmlspecialchars($search) ?>" aria-label="First">
                        <span aria-hidden="true">&laquo;&laquo;</span>
                    </a>
                </li>
                <li class="page-item <?php if ($page == 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= htmlspecialchars($search) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php if ($page == $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= htmlspecialchars($search) ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <li class="page-item <?php if ($page == $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?= $total_pages ?>&search=<?= htmlspecialchars($search) ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
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