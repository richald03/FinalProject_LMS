<?php
session_start();
include '../db.php';

// Redirect if not logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Fetch all courses from the database
$sql = "SELECT * FROM courses";
$courses = [];
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // Fetch courses as an associative array
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Function to generate a random color
function getRandomColor()
{
    $colors = [
'#FFEBEE', '#FCE4EC', '#F3E5F5', '#EDE7F6', '#E8EAF6', '#E1F5FE',
'#E0F7FA', '#E0F2F1', '#E8F5E9', '#F1F8E9', '#F9FBE7', '#FFFDE7',
'#FFF8E1', '#FFF3E0', '#FBE9E7', '#EFEBE9', '#FAFAFA', '#ECEFF1',
'#FFCDD2', '#F8BBD0', '#E1BEE7', '#D1C4E9', '#C5CAE9', '#B2DFDB',
'#C8E6C9', '#DCEDC8', '#F0F4C3', '#FFF9C4', '#FFECB3', '#FFE0B2',
'#FFCCBC', '#D7CCC8', '#F5F5F5', '#CFD8DC', '#FF8A80', '#FF80AB',
'#EA80FC', '#B388FF', '#A7FFEB', '#B9F6CA', '#CCFF90', '#F4FF81',
'#FFFF8D', '#FFE57F', '#FFD180', '#FF9E80', '#EF9A9A', '#F48FB1',
'#CE93D8', '#B39DDB', '#9FA8DA', '#90CAF9', '#81D4FA', '#80DEEA',
'#80CBC4', '#A5D6A7', '#C5E1A5', '#E6EE9C', '#FFF59D', '#FFE082',
'#FFCC80', '#FFAB91', '#BCAAA4', '#EEEEEE', '#B0BEC5', '#FF5252',
'#FF4081', '#E040FB', '#7C4DFF', '#69F0AE', '#B2FF59', '#EEFF41',
'#FFFF00', '#FFD740', '#FFAB40', '#FF6E40', '#FF1744', '#FFEB3B',
'#FFC107', '#FF9800', '#FF5722', '#E91E63', '#F06292', '#F8BBD0',
'#E57373', '#FF7043', '#FFB74D', '#FFD54F', '#F9A825', '#FFD600',
'#DCE775', '#81C784', '#66BB6A', '#4DB6AC', '#4DD0E1', '#9575CD',
'#7986CB', '#BA68C8', '#F48FB1', '#FFF176', '#D4E157', '#AED581'
    ];
    return $colors[array_rand($colors)];
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Courses</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

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

    /* Content area styles */
    .content {
        margin-left: 250px;
        padding: 20px;
    }

    .container {
        margin-top: 20px;
    }

    .card {
        border: 2px solid black;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease;
        padding: 30px;
        display: flex;
        flex-direction: column;
        height: 90%;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-title {
        color: black;
        font-weight: bold;
    }

    .card-body {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .card-buttons {
        margin-top: auto;
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .card-buttons a {
        flex-grow: 1;
        color: white;
        background-color: black;
        border: none;
    }

    .card-buttons a:hover {
        background-color: #333;
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
        <a href="view_grades.php" class="nav-link">Grades</a>
        <a href="view_announcement.php" class="nav-link">Announcements</a>
        <a href="update_profile.php" class="nav-link">Update Profile</a>
    </nav>

    <!-- Logout Link -->
    <a href="logout.php" class="btn btn-danger mt-auto">Logout</a>
</div>

<!-- Main Content Area -->
<div class="content">
    <div class="container">
        <h2 class="text-center mb-4">My Courses</h2>

        <!-- Course List -->
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4">
                    <div class="card mb-4" style="background-color: <?= getRandomColor() ?>;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($course['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($course['description']) ?></p>
                            <div class="card-buttons">
                            <a href="view_modules.php?course_id=<?= $course['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-chalkboard-teacher"></i> Modules
                            </a>
                            <a href="view_assignments.php?course_id=<?= $course['id'] ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-pencil-alt"></i> Assignments
                            </a>
                            <a href="view_quizzes.php?course_id=<?= $course['id'] ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-question-circle"></i> Quizzes
                    </a>
                </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('burgerToggle').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show'); // Toggle 'show' class
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</body>
</html>