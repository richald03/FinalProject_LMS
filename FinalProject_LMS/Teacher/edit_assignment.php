<?php
session_start();
include '../db.php';

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Handle the form submission for editing the assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $assignment_id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    // Handle file upload (if any)
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_error = $_FILES['file']['error'];

    if ($file_error === 0) {
        // Generate a unique name for the file and move it to the uploads folder
        $file_new_name = uniqid('', true) . "_" . $file_name;
        $file_destination = 'uploads/assignments/' . $file_new_name;
        move_uploaded_file($file_tmp, $file_destination);
    } else {
        $file_new_name = null; // If no new file is uploaded, keep it null
    }

    // Update the assignment in the database
    $update_sql = "UPDATE assignments SET title = ?, description = ?, due_date = ?, file = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $title, $description, $due_date, $file_new_name, $assignment_id);

    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Assignment updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update assignment.";
    }
    $update_stmt->close();

    // Redirect back to assignments page
    header("Location: edit_assignment.php");
    exit();
}

// Fetch the assignment details for editing
if (isset($_GET['id'])) {
    $assignment_id = $_GET['id'];
    $fetch_sql = "SELECT * FROM assignments WHERE id = ?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param("i", $assignment_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();

    if ($result->num_rows > 0) {
        $assignment = $result->fetch_assoc();
    } else {
        $_SESSION['error_message'] = "Assignment not found.";
        header("Location: edit_assignment.php");
        exit();
    }

    $fetch_stmt->close();
} else {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: assignment.php");
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
    $first_name = 'Student'; 
    $last_name = '';         
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assignment</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Main Content Styling */
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
            margin-bottom: 10px;
        }

        .sidebar .logo-section h2 {
            font-size: 24px;
            color: #333;
        }

        .sidebar .profile-picture {
            border-radius: 50%;
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
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

        /* Make Sidebar Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px;
            }

            .sidebar .logo-section img {
                width: 60px;
                height: 60px;
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

        .content {
            margin-left: 270px;
            padding: 20px;
        }

        .form-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #f8f9fc;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            color: #4e73df;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Form field styling */
        .form-group label {
            font-weight: bold;
            color: #4e73df;
        }

        .form-group input,
        .form-group textarea {
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ddd;
            background-color: #f7f7f7;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4e73df;
        }

        .form-group small {
            color: #6c757d;
        }

        .btn-custom-save {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            width: 100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .btn-custom-save:hover {
            background-color: #218838;
        }

        .alert {
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .form-container {
                width: 100%;
                padding: 20px;
            }

            .content {
                margin-left: 0;
                padding: 20px;
            }

            .btn-custom-save {
                font-size: 14px;
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
        display: none; 
    }

    .sidebar {
        transform: translateX(0); 
    }

    .content {
        margin-left: 250px;
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
        <a href="teacher_dashboard.php" class="nav-link">Dashboard</a>
        <a href="course_management.php" class="nav-link">Course Management</a>
        <a href="student_management.php" class="nav-link">Student Management</a>

        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="gradingDropdown" role="button">Assignment/Grading</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="assignments.php">Assignments</a>
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

<!-- Main Content -->
<div class="content">
    <div class="container form-container">
        <h2>Edit Assignment</h2>

        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <!-- Edit Assignment Form -->
        <form action="edit_assignment.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $assignment['id']; ?>">

            <div class="form-group">
                <label for="title">Assignment Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($assignment['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Assignment Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($assignment['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" value="<?= $assignment['due_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="file">Upload Assignment File (optional)</label>
                <input type="file" class="form-control-file" id="file" name="file">
                <small class="form-text text-muted">Leave blank if you don't want to change the file.</small>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-custom-save">Save Changes</button>
            </div>
        </form>
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
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>