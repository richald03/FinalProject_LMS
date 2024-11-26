<?php
session_start();

// Redirect to login if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: /FinalProject_LMS/login.php");
    exit();
}

// Include the database connection file
require '../db.php';

// Handle form submission for creating a department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_department'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO departments (department_name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);

    if ($stmt->execute()) {
        header("Location: manage_department.php"); // Redirect to the same page to reflect the new department
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle the delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_department'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Reset AUTO_INCREMENT to 1 after deleting all records
        $conn->query("ALTER TABLE departments AUTO_INCREMENT = 1");

        header("Location: manage_department.php"); // Redirect to the same page to reflect the changes
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Pagination settings
$limit = 5; // Number of entries to show in a page.
if (isset($_GET["page"])) {
    $page = $_GET["page"];
} else {
    $page = 1;
}
$start_from = ($page - 1) * $limit;

// Search functionality
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM departments WHERE department_name LIKE ? OR description LIKE ? ORDER BY id ASC LIMIT ?, ?");
    $search_term = "%" . $search_query . "%";
    $stmt->bind_param("ssii", $search_term, $search_term, $start_from, $limit);
} else {
    $stmt = $conn->prepare("SELECT * FROM departments ORDER BY id ASC LIMIT ?, ?");
    $stmt->bind_param("ii", $start_from, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

$total_query = $conn->prepare("SELECT COUNT(*) FROM departments WHERE department_name LIKE ? OR description LIKE ?");
$total_query->bind_param("ss", $search_term, $search_term);
$total_query->execute();
$total_result = $total_query->get_result();
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

    .search-form input[type="search"] {
        width: 300px; /* Adjust the width as needed */
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
            display: block; /* Switch to block layout for small screens */
        }
        .right-panel,
        .left-panel {
            width: 100%; /* Reset width on small screens */
        }
        .search-form input[type="search"] {
            width: 100%; /* Reset width on small screens */
        }
    }
    
    .container-fluid {
        padding-left: 270px; /* Space for the sidebar */
    }

    .right-panel {
        padding: 20px;
    }

    .form-control {
        width: 500px; /* Fixed width for form elements */
        margin: 0; /* Center align the form elements */
    }

    .title {
        background-color: #f0f0f0;
        text-align: center;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .search-form {
        width: 100%; /* Fixed width for search form */
        margin-bottom: 20px; /* Adjust to place above the table */
    }

    .table {
        margin-top: 20px; /* Adjust to create space between search form and table */
    }

    .table th.department-col, .table td.department-col {
        width: 350px; 
    }
    .table th.programs-col, .table td.programs-col {
        width: 350px; 
    }
    .table th.description-col, .table td.description-col {
        width: 350px; 
    }

    .table-responsive {
        overflow-x: auto;
    }

    /* Adjust column widths for smaller screens */
    @media (max-width: 767.98px) {
        .table td.programs-col,
        .table td.description-col {
            width: auto; /* Let the columns adapt based on content */
        }
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
        .form-control,
        .search-form {
            width: 100%; /* Reset width on small screens */
        }
        .table {
            width: 100%; /* Reset width on small screens */
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
        <h1>Manage Department</h1>
    </div>

    <!-- Right Panel for Form and Search Bar -->
    <div class="right-panel">
        <h3>Create Department</h3>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="name" class="form-label">Department Name</label>
                <input type="text" name="name" class="form-control" id="name" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" class="form-control" id="description" rows="3"></textarea>
            </div>
            <button type="submit" name="create_department" class="btn btn-primary">Create</button>
        </form>
        
        <!-- Search Bar -->
        <form class="search-form d-flex mt-4" method="GET" action="">
            <input class="form-control me-2" type="search" name="search" placeholder="Search" aria-label="Search" value="<?= htmlspecialchars($search_query) ?>">
            <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
        <!-- Table -->
         <div class="table-responsive">
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th class="department-col">Department Name</th>
                        <th class="programs-col">Registered Programs</th>
                        <th class="description-col">Description</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()): 
                        // Fetch the registered programs for each department
                        $programs = [];
                        $program_stmt = $conn->prepare("SELECT program_name FROM programs WHERE department_id = ?");
                        $program_stmt->bind_param("i", $row['id']);
                        $program_stmt->execute();
                        $program_result = $program_stmt->get_result();
                        while ($program_row = $program_result->fetch_assoc()) {
                            $programs[] = $program_row['program_name'];
                        }
                        $program_stmt->close();
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td class="department-col"><?= htmlspecialchars($row['department_name']) ?></td>
                            <td class="programs-col"><?= htmlspecialchars(implode(", ", $programs)) ?></td>
                            <td class="description-col"><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td>
                                <a href="view_department.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">View</a>
                                <a href="edit_department.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this department?');">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="delete_department" class="btn btn-danger btn-sm">Delete</button>
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
                    <a class="page-link" href="?page=1&search=<?= htmlspecialchars($search_query) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item<?php if($i == $page) echo ' active'; ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search_query) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $total_pages ?>&search=<?= htmlspecialchars($search_query) ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
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