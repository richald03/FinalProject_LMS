<?php
session_start();

// Redirect to login if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: /FinalProject_LMS/login.php");
    exit();
}

// Include the database connection file
require '../db.php';

// Fetch programs, departments, teachers, and students from the database
$programs = [];
$departments = [];
$teachers = [];
$students = [];
$result_programs = mysqli_query($conn, "SELECT id, program_name FROM programs");
$result_departments = mysqli_query($conn, "SELECT id, department_name FROM departments");
$result_teachers = mysqli_query($conn, "SELECT id, first_name, last_name FROM users WHERE user_type = 'teacher'");
$result_students = mysqli_query($conn, "SELECT student_id, first_name, last_name, program_id FROM users WHERE user_type = 'student'");
while ($row = mysqli_fetch_assoc($result_programs)) {
    $programs[] = $row;
}
while ($row = mysqli_fetch_assoc($result_departments)) {
    $departments[] = $row;
}
while ($row = mysqli_fetch_assoc($result_teachers)) {
    $teachers[] = $row;
}
while ($row = mysqli_fetch_assoc($result_students)) {
    $students[] = $row;
}

// Function to get the full name of a user
function get_full_name_by_studentID($conn, $studentID) {
    error_log("Fetching full name for student ID: $studentID");
    $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE student_id = ?");
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return null;
    }
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    if (!$stmt) {
        error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        return null;
    }
    $result = $stmt->get_result();
    if (!$result) {
        error_log("Get result failed: (" . $stmt->errno . ") " . $stmt->error);
        return null;
    }
    $row = $result->fetch_assoc();
    $stmt->close();
    if ($row) {
        error_log("Fetched full name: " . $row['full_name']);
        return $row['full_name'];
    } else {
        error_log("No user found with student ID: $studentID");
        return null;
    }
}

$message = '';

// Handle course creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $department_id = $_POST['department'];
    $program_id = $_POST['program'];
    $teacher_id = $_POST['teacher_id'];
    $selected_students = isset($_POST['selected_students']) ? explode(',', $_POST['selected_students']) : [];

    // Log selected students
    error_log("Selected Students: " . json_encode($selected_students));

    // Check if course_code already exists
    $stmt = $conn->prepare("SELECT COUNT(1) AS count FROM course WHERE course_code = ?");
    $stmt->bind_param("s", $course_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['count'] > 0) {
        // Course code already exists
        $message = "Error: Course Code already exists. Please choose a different course code.";
    } else {
        // Insert into courses table
        $stmt = $conn->prepare("INSERT INTO course (course_name, course_code, department_id, program_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssii", $course_name, $course_code, $department_id, $program_id);
        $stmt->execute();
        $course_id = $stmt->insert_id; // Get the ID of the newly created course
        $stmt->close();

        // Get the full name of the teacher
        $teacher_full_name = get_full_name($conn, $teacher_id);
        error_log("Teacher ID: $teacher_id, Full Name: $teacher_full_name");

        if ($teacher_full_name !== null) {
            // Log before insert
            error_log("Inserting teacher with ID: $teacher_id, Full Name: $teacher_full_name, Course ID: $course_id");
            
            $stmt = $conn->prepare("INSERT INTO course_users (course_id, user_id, user_type, registered_name, created_at) VALUES (?, ?, 'teacher', ?, NOW())");
            $stmt->bind_param("iis", $course_id, $teacher_id, $teacher_full_name);
            if ($stmt->execute()) {
                error_log("Inserted teacher with ID: $teacher_id and name: $teacher_full_name into course: $course_id");
            } else {
                error_log("Error inserting teacher with ID: $teacher_id into course: $course_id. Error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Error fetching full name for teacher with ID: $teacher_id");
        }

        // Insert into course_users table for one student (for testing)
        if (!empty($selected_students)) {
            $studentID = $selected_students[0]; // Take the first student for simplicity
            $student_full_name = get_full_name_by_studentID($conn, $studentID);
            error_log("Student ID: $studentID, Full Name: $student_full_name");

            if ($student_full_name !== null) {
                // Log before insert
                error_log("Inserting student with ID: $studentID, Full Name: $student_full_name, Course ID: $course_id");
                
                $stmt = $conn->prepare("INSERT INTO course_users (course_id, user_id, user_type, registered_name, created_at) VALUES (?, ?, 'student', ?, NOW())");
                $stmt->bind_param("iis", $course_id, $studentID, $student_full_name);
                if ($stmt->execute()) {
                    error_log("Inserted student with ID: $studentID and name: $student_full_name into course: $course_id");
                } else {
                    error_log("Error inserting student with ID: $studentID into course: $course_id. Error: " . $stmt->error);
                }
                $stmt->close();
            } else {
                error_log("Error fetching full name for student with ID: $studentID");
            }
        }

        // Set success message
        $message = "Course Registration Successful!";
    }
}

// Handle course deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course'])) {
    $course_id = $_POST['course_id'];
    $stmt = $conn->prepare("DELETE FROM course WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
}

// Handle course updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $program_id = $_POST['program_id'];
    $department_id = $_POST['department_id'];
    $stmt = $conn->prepare("UPDATE course SET course_name = ?, course_code = ?, program_id = ?, department_id = ? WHERE id = ?");
    $stmt->bind_param("ssiii", $course_name, $course_code, $program_id, $department_id, $course_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch course for the table
$course = [];
$result_course = mysqli_query($conn, "SELECT id, course_name, course_code, department_id, created_at FROM course");
while ($row = mysqli_fetch_assoc($result_course)) {
    $course[] = $row;
}

// Pagination and Search functionality
$search_query = "";
$limit = 5; // Number of entries to show in a page
if (isset($_GET["page"])) {
    $page  = $_GET["page"];
} else {
    $page = 1;
}
$start_from = ($page - 1) * $limit;

if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM course WHERE course_name LIKE ? OR course_code LIKE ? ORDER BY id ASC LIMIT ?, ?");
    $search_term = "%" . $search_query . "%";
    $stmt->bind_param("ssii", $search_term, $search_term, $start_from, $limit);
} else {
    $stmt = $conn->prepare("SELECT * FROM course ORDER BY id ASC LIMIT ?, ?");
    $stmt->bind_param("ii", $start_from, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

$total_query = $conn->prepare("SELECT COUNT(*) FROM course WHERE course_name LIKE ? OR course_code LIKE ?");
$search_term = "%" . $search_query . "%";
$total_query->bind_param("ss", $search_term, $search_term);
$total_query->execute();
$total_result = $total_query->get_result();
$total_rows = $total_result->fetch_row()[0];
$total_pages = $total_rows > 0 ? ceil($total_rows / $limit) : 1;

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Courses</title>
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

    .main-content {
        margin-left: 270px;
        padding: 20px;
    }
    .manage-course-title {
        background-color: #ffcc66;
        text-align: center;
        padding: 10px;
        border-radius: 5px;
    }
    .form-row .form-group {
        flex: 1;
        margin-right: 10px;
    }
    .form-row .form-group:last-child {
        margin-right: 0;
    }
    .list-group-item-action {
        position: relative;
        z-index: 1050;
        display: block;
        padding: 10px 20px;
        margin-bottom: -1px;
        background-color: #fff;
        border: 1px solid #ddd;
    }
    #student_results {
        position: absolute;
        z-index: 1050;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        background-color: #fff;
        border: 1px solid #ddd;
        display: none;
    }
    .selected-students-table {
        width: 100%;
        margin-top: 10px;
    }
    .selected-students-table th, .selected-students-table td {
        text-align: left;
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }

    .search-form .form-control { 
        width: 300px; 
    }

    .message {
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        background-color: #f2f2f2;
        color: #333;
        font-size: 16px;
        display: none; /* Initially hidden */
    }
    .error {
        border-color: #e74c3c;
        background-color: #f9e2e2;
        color: #e74c3c;
    }
    .success {
        border-color: #2ecc71;
        background-color: #e2f9e6;
        color: #2ecc71;
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

<script>
    function showMessage(message, type) {
        var messageDiv = document.getElementById('messageDiv');
        messageDiv.innerHTML = message;
        messageDiv.className = 'message ' + type;
        messageDiv.style.display = 'block';
        setTimeout(function() {
            messageDiv.style.display = 'none';
        }, 3000);
    }

    <?php if (!empty($message)) : ?>
        document.addEventListener('DOMContentLoaded', function() {
            showMessage("<?php echo $message; ?>", "<?php echo strpos($message, 'Error') === 0 ? 'error' : 'success'; ?>");
        });
    <?php endif; ?>
</script>
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
    <h2 class="manage-course-title">Manage Course</h2>

    <!-- Display messages -->
    <div id="messageDiv" class="message"></div>

    <form id="course_form" action="manage_course.php" method="post">
        <div class="form-row">
            <div class="form-group">
                <label for="course_name">Course Name</label>
                <input type="text" class="form-control" id="course_name" name="course_name" required>
            </div>
            <div class="form-group">
                <label for="course_code">Course Code</label>
                <input type="text" class="form-control" id="course_code" name="course_code" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="program">Program</label>
                <select class="form-control" id="program" name="program" required onchange="filterStudentsByProgram()">
                    <option value="">Select Program</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?php echo $program['id']; ?>"><?php echo $program['program_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <select class="form-control" id="department" name="department" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo $department['id']; ?>"><?php echo $department['department_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="teacher_id">Teacher</label>
                <select class="form-control" id="teacher_id" name="teacher_id" required>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="student_search">Add Students</label>
                <input type="text" class="form-control" id="student_search" placeholder="Type student name or ID" onfocus="showStudentResults()" onblur="hideStudentResults()" oninput="searchStudents()">
                <div id="student_results" class="list-group mt-2"></div>
            </div>
        </div>
        <div class="form-group">
            <label for="selected_students">Selected Students</label>
            <table class="selected-students-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody id="selected_students">
                    <!-- Selected students will appear here -->
                </tbody>
            </table>
        </div>
        <button type="submit" name="create_course" class="btn btn-primary">Create Course</button>
    </form>

    <!-- Search Form -->
    <form class="search-form d-flex mt-4" method="GET" action="">
        <input class="form-control me-2" type="search" name="search" placeholder="Search" aria-label="Search" value="<?= htmlspecialchars($search_query) ?>">
        <button class="btn btn-outline-success" type="submit">Search</button>
    </form>

    <!-- Courses Table -->
    <div class="table-responsive">
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Course Code</th>
                    <th>Department ID</th>
                    <th>Created at</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['course_name']) ?></td>
                        <td><?= htmlspecialchars($row['course_code']) ?></td>
                        <td><?= htmlspecialchars($row['department_id']) ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td>
                            <a href="view_course.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">View</a>
                            <a href="edit_course.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                <input type="hidden" name="course_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="delete_course" class="btn btn-danger btn-sm">Delete</button>
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

<script>
function filterStudentsByProgram() {
    var programId = $('#program').val();
    if (!programId) return;

    $.ajax({
        url: 'fetch_students.php',
        method: 'GET',
        data: { program_id: programId },
        success: function(response) {
            console.log("Filtered students:", response);
            var students = JSON.parse(response);
            var studentResults = '';

            students.forEach(function(student) {
                studentResults += '<a href="#" class="list-group-item list-group-item-action" onclick="selectStudent(\'' + student.studentID + '\', \'' + student.first_name + ' ' + student.last_name + '\')">' + student.studentID + ' - ' + student.first_name + ' ' + student.last_name + '</a>';
            });

            $('#student_results').html(studentResults).show();
        },
        error: function(xhr, status, error) {
            console.error("Error fetching students:", status, error);
        }
    });
}

function searchStudents() {
    var query = $('#student_search').val();
    if (query.length < 3) {
        $('#student_results').hide();
        return;
    }

    var programId = $('#program').val();
    if (!programId) return;

    $.ajax({
        url: 'fetch_students.php',
        method: 'GET',
        data: { query: query, program_id: programId },
        success: function(response) {
            console.log("Searched students:", response);
            var students = JSON.parse(response);
            var studentResults = '';

            students.forEach(function(student) {
                studentResults += '<a href="#" class="list-group-item list-group-item-action" onclick="selectStudent(\'' + student.studentID + '\', \'' + student.first_name + ' ' + student.last_name + '\')">' + student.studentID + ' - ' + student.first_name + ' ' + student.last_name + '</a>';
            });

            $('#student_results').html(studentResults).show();
        },
        error: function(xhr, status, error) {
            console.error("Error searching students:", status, error);
        }
    });
}

function selectStudent(studentID, studentName) {
    // Check if the student is already selected
    if ($('#selected_students tr[data-student-id="' + studentID + '"]').length > 0) {
        return; // Student is already selected, do nothing
    }

    var selectedRow = '<tr data-student-id="' + studentID + '"><td>' + studentID + '</td><td>' + studentName + '</td></tr>';
    $('#selected_students').append(selectedRow);
    $('#student_results').hide();
}

function showStudentResults() {
    $('#student_results').show();
}

function hideStudentResults() {
    // Use setTimeout to allow selection before hiding
    setTimeout(function() {
        $('#student_results').hide();
    }, 200);
}

document.getElementById('burgerToggle').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show');
    });

</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>