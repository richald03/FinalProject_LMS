<?php
session_start();

// Redirect to login if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection file
require '../db.php';

// Handle the delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Reset AUTO_INCREMENT to 1 after deleting all records
        $conn->query("ALTER TABLE users AUTO_INCREMENT = 1");

        // Redirect to manage_student.php after deletion
        header("Location: manage_student.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Pagination settings
$limit = 10; // Number of entries to show in a page.
if (isset($_GET["page"])) {
    $page = $_GET["page"];
} else {
    $page = 1;
}

$start_from = ($page - 1) * $limit;

// Search and filter functionality
$search_query = "";
$filter_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}
if (isset($_GET['filter'])) {
    $filter_query = $_GET['filter'];
}

$sql = "SELECT u.*, p.program_name, d.department_name 
        FROM users u 
        LEFT JOIN programs p ON u.program_id = p.id 
        LEFT JOIN departments d ON u.department_id = d.id 
        WHERE u.user_type='student'";
$params = [];
$types = '';

if ($search_query) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.student_id LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    $types .= 'ssss';
}

if ($filter_query) {
    $sql .= " AND p.program_name = ?";
    $params[] = $filter_query;
    $types .= 's';
}

$sql .= " ORDER BY u.id ASC LIMIT ?, ?";
$params[] = $start_from;
$params[] = $limit;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Count total rows for pagination
$total_query = "SELECT COUNT(*) FROM users u 
                LEFT JOIN programs p ON u.program_id = p.id 
                WHERE u.user_type='student'";
$params = [];
$types = '';

if ($search_query) {
    $total_query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.student_id LIKE ?)";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    $types .= 'ssss';
}

if ($filter_query) {
    $total_query .= " AND p.program_name = ?";
    $params[] = $filter_query;
    $types .= 's';
}

$total_stmt = $conn->prepare($total_query);
if ($types) {
    $total_stmt->bind_param($types, ...$params);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TipTopLearn - Admin Panel</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<style>
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
    .search-form input[type="search"], .search-form select {
        width: 300px;
        margin-right: 10px; /* Add spacing between elements */
    }
    .title {
        background-color: #f0f0f0;
        text-align: center;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    @media (max-width: 767.98px) {
        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }
        .container-fluid {
            padding-left: 0;
        }
        .right-panel, .left-panel {
            width: 100%;
        }
        .search-form input[type="search"], .search-form select {
            width: 100%;
            margin-bottom: 10px; /* Adjust spacing for small screens */
        }
        .search-form a {
            margin-top: 10px; /* Add spacing for small screens */
        }
    }
    .container-fluid {
        padding-left: 270px;
    }
    .right-panel {
        padding: 20px;
    }
    .form-control {
        width: 500px;
        margin: 0;
    }
    .search-form {
        width: 100%;
        margin-bottom: 20px;
    }
    .table {
        margin-top: 20px;
    }
    .table-responsive {
        overflow-x: auto;
    }
    @media (max-width: 767.98px) {
        .table td.student-col, .table td.email-col, .table td.program-col {
            width: auto;
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

<div class="container-fluid">
    <!-- Title -->
    <div class="title">
        <h1>Manage Students</h1>
    </div>

    <!-- Right Panel for Search Bar and Table -->
    <div class="right-panel">
        <!-- Search Bar with Create Button and Filter Dropdown -->
        <form class="search-form d-flex mt-4" method="GET" action="">
            <input class="form-control me-2" type="search" name="search" placeholder="Search" aria-label="Search" value="<?= htmlspecialchars($search_query) ?>">
            <select name="filter" class="form-control ms-2" onchange="this.form.submit()">
                <option value="">All Programs</option>
                <?php
                $programs = $conn->query("SELECT DISTINCT program_name FROM programs");
                while ($program = $programs->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($program['program_name']) ?>"<?= ($filter_query == $program['program_name']) ? ' selected' : '' ?>><?= htmlspecialchars($program['program_name']) ?></option>
                <?php endwhile; ?>
            </select>
            <a href="register.php" class="btn btn-primary ms-2">Add Student</a>
        </form>
        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Program</th>
                        <th>Program ID</th>
                        <th>Gender</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['student_id']) ?></td>
                            <td><?= htmlspecialchars($row['first_name']) ?></td>
                            <td><?= htmlspecialchars($row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['program_name']) ?></td>
                            <td><?= htmlspecialchars($row['program_id']) ?></td>
                            <td><?= htmlspecialchars($row['gender']) ?></td>
                            <td>
                                <a href="view_student.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">View</a>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="delete_student" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item">
                    <a class="page-link" href="?page=1&search=<?= htmlspecialchars($search_query) ?>&filter=<?= htmlspecialchars($filter_query) ?>" aria-label="First">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item<?php if($i == $page) echo ' active'; ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search_query) ?>&filter=<?= htmlspecialchars($filter_query) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $total_pages ?>&search=<?= htmlspecialchars($search_query) ?>&filter=<?= htmlspecialchars($filter_query) ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<script>
function fetchPrograms() {
    $.ajax({
        url: 'fetch_programs.php',
        method: 'GET',
        success: function(data) {
            updateProgramOptions(data);
        }
    });
}

function fetchDepartments() {
    $.ajax({
        url: 'fetch_departments.php',
        method: 'GET',
        success: function(data) {
            updateDepartmentOptions(data);
        }
    });
}

function updateProgramOptions(programs) {
    var selectElements = document.querySelectorAll('select[name="program"]');
    selectElements.forEach(function(select) {
        select.innerHTML = '<option value="">Select Program</option>';
        programs.forEach(function(program) {
            var option = document.createElement('option');
            option.value = program.program_name;
            option.textContent = program.program_name;
            select.appendChild(option);
        });
    });
}

function updateDepartmentOptions(departments) {
    var selectElements = document.querySelectorAll('select[name="department"]');
    selectElements.forEach(function(select) {
        select.innerHTML = '<option value="">Select Department</option>';
        departments.forEach(function(department) {
            var option = document.createElement('option');
            option.value = department.department_name;
            option.textContent = department.department_name;
            select.appendChild(option);
        });
    });
}

// Fetch programs and departments on page load
fetchPrograms();
fetchDepartments();

// Optionally, you can set an interval to fetch updates periodically
setInterval(fetchPrograms, 60000); // Fetch every 60 seconds
setInterval(fetchDepartments, 60000); // Fetch every 60 seconds

</script>


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
