<?php
session_start();
include 'db.php';

// Fetch all courses from the database
$sql = "SELECT * FROM courses";
$courses = [];
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // Fetch courses as an associative array
    while($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Handle adding new course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_name'], $_POST['course_description']) && !isset($_GET['edit_id'])) {
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];

    // Prepare and execute INSERT statement
    $stmt = $conn->prepare("INSERT INTO courses (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $course_name, $course_description);  // "ss" indicates two string parameters
    $stmt->execute();
    $stmt->close();

    // Redirect to course management page after adding course
    header("Location: course_management.php");
    exit;
}

// Handle editing course
if (isset($_GET['edit_id'])) {
    $course_id = $_GET['edit_id'];

    // Fetch the course details based on the provided id
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);  // "i" indicates an integer parameter
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();

    // When the form is submitted for editing the course
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_name'], $_POST['course_description'])) {
        $course_name = $_POST['course_name'];
        $course_description = $_POST['course_description'];

        // Prepare and execute UPDATE statement
        $stmt = $conn->prepare("UPDATE courses SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $course_name, $course_description, $course_id);  
        $stmt->execute();
        $stmt->close();

        // Redirect to course management page after saving changes
        header("Location: course_management.php");
        exit;
    }
}

// Handle deleting course
if (isset($_GET['delete_id'])) {
    $course_id = $_GET['delete_id'];

    // Prepare and execute DELETE statement
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);  
    $stmt->execute();
    $stmt->close();

    // Redirect to course management page after deleting course
    header("Location: course_management.php");
    exit;
}

// Function to generate a random color
function getRandomColor() {
    $colors = [
    '#F0E1FF',  // Soft Lavender
    '#E0F7FA',  // Light Aqua
    '#FFE4E1',  // Misty Rose
    '#B2EBF2',  // Light Cyan
    '#D1C4E9',  // Lavender
    '#FFF8E1',  // Light Lemon
    '#FFCDD2',  // Soft Pink
    '#E8F5E9',  // Light Mint Green
    '#F1F8E9',  // Pale Green
    '#FFEB3B',  // Yellow Sunshine
    '#B3E5FC',  // Baby Blue
    '#FFCC80',  // Soft Apricot
    '#FFB74D',  // Light Orange
    '#C5E1A5',  // Pale Olive Green
    '#FFEBEE',  // Light Blush
    '#D1C4E9',  // Light Lavender
    '#C8E6C9',  // Soft Green
    '#FFE0B2',  // Warm Peach
    '#F3E5F5',  // Lavender Blush
    '#FFB3E6',  // Light Pink
    '#A5D6A7',  // Soft Mint
    '#B39DDB',  // Light Purple
    '#FCE4EC',  // Light Pink Blush
    '#B2DFDB',  // Aqua Blue
    '#FFDF00',  // Bright Yellow
    '#FF6F61',  // Coral Red
    '#F5F5F5',  // Light Grey
    '#F0F8FF',  // Alice Blue
    '#FFAB91',  // Light Coral
    '#FF9E9E',  // Soft Rose
    '#FFCC99',  // Light Peach
    '#E0FFFF',  // Light Cyan
    '#FFECBC',  // Light Butter
    '#FFDAE9',  // Soft Lavender Pink
    '#E6E6FA',  // Lavender Mist
    '#FFF59D',  // Light Yellow
    '#FFE4B5',  // Moccasin
    '#F7C4B1',  // Soft Peach
    '#E1BEE7',  // Lavender Mist
    '#FFFAF0',  // Floral White
    '#FFDFD4',  // Peachy Pink
    '#F0FFF0',  // Honeydew Green
    '#F0F9FF',  // Pale Sky Blue
    '#FFD6A5',  // Soft Peach
    '#E8EAF6',  // Soft Lavender Blue
    '#D0F0C0',  // Pale Mint Green
    '#C8E6C9',  // Mint Green
    '#FFF3E0',  // Light Cream
    '#E1DEE1',  // Misty Lavender
    '#FFEE58',  // Light Yellow-Green
    '#FFB8B8',  // Soft Rose
    '#FF9F9F',  // Pale Pink
    '#BBDEFB',  // Light Blue
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

        /* Content area styles */
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .container {
            margin-top: 20px;
        }

        .card {
            border: 1px solid #ddd; 
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 90%;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 10px; 
        }

        .btn-spacing {
            display: flex;
            flex-direction: column; 
            gap: 10px;
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

        /* Large Screens (Sidebar fixed on the left) */
        @media (min-width: 768px) {
            .sidebar {
                position: fixed;
                height: 100vh;
            }

            .content {
                margin-left: 250px; 
            }

            .top-right-button {
                position: absolute;
                top: 20px;
                right: 20px;
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
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column">
    <div class="logo-section text-center mb-4">
        <img src="images/logo.png" alt="Logo">
    </div>

    <div class="text-center mb-4">
        <img src="<?php echo $_SESSION['profile_picture'] ?? 'uploads/profile_pictures/default.jpg'; ?>" alt="Profile Picture" class="profile-picture">
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

<!-- Main Content Area -->
<div class="content">
    <div class="container position-relative">
        <!-- Add New Course Button on Top-Right -->
        <button class="btn btn-primary top-right-button" data-toggle="modal" data-target="#addCourseModal">Add New Course</button>

        <h2 class="text-center mb-4">My Courses</h2>

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
                        <button class="btn btn-custom btn-sm" data-toggle="modal" data-target="#editCourseModal<?= $course['id'] ?>">
                            <img src="images/edit.png" alt="Assignments" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;" />
                            Edit Course
                        </button>
                        
                        <!-- Delete Course Button with Confirmation -->
                        <a href="course_management.php?delete_id=<?= $course['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this course?');"> 
                            <img src="images/delete.png" alt="Assignments" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;" />
                            Delete Course
                            </a>
                        
                        <!-- Assignments Button with Favicon -->
                        <a href="create_assignment.php?course_id=<?= $course['id'] ?>" class="btn btn-secondary btn-sm">
                            <img src="images/assignment.png" alt="Assignments" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;" />
                            Assignments
                        </a>
                        
                        <!-- Modules Button with Favicon -->
                        <a href="module.php?course_id=<?= $course['id'] ?>" class="btn btn-secondary btn-sm">
                            <img src="images/module.png" alt="Modules" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;" />
                            Modules
                        </a>
                        
                        <!-- Quizzes Button with Favicon -->
                        <a href="quizzes.php?course_id=<?= $course['id'] ?>" class="btn btn-secondary btn-sm">
                            <img src="images/quiz.png" alt="Quizzes" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;" />
                            Quizzes
                        </a>
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
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>