<?php
// Database connection
$dsn = 'mysql:host=localhost;dbname=lms_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Fetch all courses from the database
$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);

// Handle adding new course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_name'], $_POST['course_description']) && !isset($_GET['edit_id'])) {
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];

    $stmt = $pdo->prepare("INSERT INTO courses (name, description) VALUES (?, ?)");
    $stmt->execute([$course_name, $course_description]);

    header("Location: course_management.php");
    exit;
}

// Handle editing course
if (isset($_GET['edit_id'])) {
    $course_id = $_GET['edit_id'];

    // Fetch the course details based on the provided id
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    // When the form is submitted for editing the course
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_name'], $_POST['course_description'])) {
        // Get the updated data from the form
        $course_name = $_POST['course_name'];
        $course_description = $_POST['course_description'];

        // Update the course details in the database
        $stmt = $pdo->prepare("UPDATE courses SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$course_name, $course_description, $course_id]);

        // Redirect to course management page after saving changes
        header("Location: course_management.php");
        exit;
    }
}

// Handle deleting course
if (isset($_GET['delete_id'])) {
    $course_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);

    header("Location: course_management.php");
    exit;
}

// Function to generate a random color
function getRandomColor() {
    $colors = [
        '#ffadad', '#ffd6a5', '#fdffb6', '#caffbf', '#9bf6ff', '#a0c4ff', '#bdb2ff', '#ffc6ff', '#f7c4b1', '#d0f0c0',
        '#ffb3e6', '#ffcc99', '#ffdf00', '#ff6f61', '#ff9e9e', '#ffb8b8', '#e6e6fa', '#f5f5dc', '#fafad2', '#ffe4e1',
        '#ffe4b5', '#ffdead', '#faf0e6', '#e0ffff', '#f0fff0', '#f0f8ff', '#f5fffa', '#f8f8ff', '#fffaf0', '#ffffe0',
        '#d8bfd8', '#dda0dd', '#ee82ee', '#e6e6fa', '#c5e1a5', '#e1bee7', '#ffccbc', '#b2dfdb', '#ffcdd2', '#d1c4e9',
        '#c8e6c9', '#ffecb3', '#bbdefb', '#ffab91', '#ff80ab', '#ce93d8', '#9fa8da', '#90caf9', '#a5d6a7', '#ffeb3b',
        '#fff176', '#aed581', '#81c784', '#4db6ac', '#81d4fa', '#80deea', '#b39ddb', '#e57373', '#a1887f', '#bcaaa4',
        '#e1bee7', '#ffcc80', '#ffb74d', '#ffab91', '#fff59d', '#e0e0e0', '#ffebee', '#fce4ec', '#f3e5f5', '#ede7f6',
        '#e0f7fa', '#e0f2f1', '#f1f8e9', '#fff3e0', '#ffccbc', '#ffe0b2'
    ];      
    return $colors[array_rand($colors)];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>

.card-title {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    font-size: 22px; 
    font-weight: 700; 
    color: #000000 !important; 
    line-height: 1.4; 
    margin-bottom: 15px; 
    text-transform: capitalize; 
}

.card-text {
    font-family: 'Roboto', sans-serif;
    font-size: 16px;
    font-weight: 400; 
    color: #555555; 
    line-height: 1.6; 
    letter-spacing: 0.5px; 
    margin-bottom: 20px; 
}

.card {
    background-color: #ffffff; 
    border: 1px solid #e0e0e0; 
    border-radius: 8px; 
    padding: 20px; 
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
    height: 350px; 
}

.btn-spacing {
    display: flex;
    flex-wrap: wrap;
    gap: 10px; 
    justify-content: flex-start; 
    margin-top: 15px;
}

/* Adjust button size and alignment */
.btn-custom {
    background-color: #007bff;
    color: #fff;
    padding: 8px 15px; 
}

.btn-custom:hover {
    background-color: #0056b3;
}

.btn-danger, .btn-secondary {
    padding: 8px 15px;
}

.btn-sm {
    font-size: 14px; 
    padding: 6px 12px; 
}
        /* Sidebar Styles */
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
            background-color: #66a3ff;
        }
        .sidebar .dropdown .dropdown-item {
            color: #fff;
            padding: 8px 20px;
        }
        .sidebar .dropdown .dropdown-item:hover {
            background-color: #0056b3;
        }

        /* Content area styles */
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        /* Card Styles */
        .container {
            margin-top: 20px;
        }
        .card {
            border: 1px solid #ddd; 
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            padding: 20px;
            min-height: 250px; 
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-title {
            color: #007bff;
        }
        .btn-custom {
            background-color: #007bff;
            color: #fff;
            margin-bottom: 10px;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .btn-spacing {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .top-right-button {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column">
    <div class="logo-section text-center mb-4">
        <img src="images/logo.png" alt="Logo">
    </div>

    <div class="text-center mb-4">
        <img src="profile-placeholder.png" alt="Profile Picture" class="profile-picture">
        <h3>Teacher's Panel</h3>
        <p>Teacher</p>
    </div>

    <nav>
        <a href="teacher_dashboard.php" class="nav-link">Dashboard</a>
        <a href="course_management.php" class="nav-link">Course Management</a>
        <a href="student_management.php" class="nav-link">Student Management</a>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="gradingDropdown" role="button">Assignment/Grading</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#assignments.php">Assignments</a>
                <a class="dropdown-item" href="#grading.php">Grading</a>
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

<!-- Main Content Area -->
<div class="content">
    <div class="container position-relative">
        <!-- Add New Course Button on Top-Right -->
        <button class="btn btn-primary top-right-button" data-toggle="modal" data-target="#addCourseModal">Add New Course</button>

        <h2 class="text-center mb-4">Course Management</h2>

        <!-- Course List -->
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4">
                    <div class="card mb-4" style="background-color: <?= getRandomColor() ?>;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($course['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($course['description']) ?></p>
                            <div class="btn-spacing">
                                <!-- Edit Course Button -->
                                <button class="btn btn-custom btn-sm" data-toggle="modal" data-target="#editCourseModal<?= $course['id'] ?>">Edit Course</button>
                                <a href="course_management.php?delete_id=<?= $course['id'] ?>" class="btn btn-danger btn-sm">Delete Course</a>
                                <a href="manage_assignments.php?course_id=<?= $course['id'] ?>" class="btn btn-secondary btn-sm">Assignments</a>
                                <a href="manage_modules.php?course_id=<?= $course['id'] ?>" class="btn btn-secondary btn-sm">Modules</a>
                                <a href="manage_quizzes.php?course_id=<?= $course['id'] ?>" class="btn btn-secondary btn-sm">Quizzes</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Course Modal -->
                <div class="modal fade" id="editCourseModal<?= $course['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="editCourseModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="course_management.php?edit_id=<?= $course['id'] ?>" method="POST">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="course_name">Course Name</label>
                                        <input type="text" class="form-control" id="course_name" name="course_name" value="<?= htmlspecialchars($course['name']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="course_description">Course Description</label>
                                        <textarea class="form-control" id="course_description" name="course_description" required><?= htmlspecialchars($course['description']) ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Add New Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" role="dialog" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="course_management.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="course_name">Course Name</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" required>
                    </div>
                    <div class="form-group">
                        <label for="course_description">Course Description</label>
                        <textarea class="form-control" id="course_description" name="course_description" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>