<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../index.php");
    exit();
}
// Get the teacher's current user ID
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];

    // Validate name and email (basic validation)
    if (empty($first_name) || empty($last_name)) {
        $errors[] = "Fields cannot be empty.";
    }
    // Check if the new password matches the confirm password
    if (!empty($new_password) && $new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if there are no errors
    if (empty($errors)) {
        // If password is updated, hash it
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        } else {
            $hashed_password = null; // No change to password
        }

        // Handle profile picture upload
        $profile_picture_path = null;
        if (isset($_FILES['profile-picture']) && $_FILES['profile-picture']['error'] == 0) {
            $profile_picture = $_FILES['profile-picture'];
            $target_dir = "../uploads/profile_pictures/"; 
            $target_file = $target_dir . basename($profile_picture["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if the file is a valid image
            if (getimagesize($profile_picture["tmp_name"]) === false) {
                $errors[] = "File is not an image.";
            }

            // Check file size (max 5MB)
            if ($profile_picture["size"] > 5000000) {
                $errors[] = "Sorry, your file is too large.";
            }

            // Allow only certain file formats (jpg, jpeg, png, gif)
            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            }

            // Upload the file if no errors
            if (empty($errors)) {
                if (move_uploaded_file($profile_picture["tmp_name"], $target_file)) {
                    $profile_picture_path = $target_file; 
                } else {
                    $errors[] = "Sorry, there was an error uploading your file.";
                }
            }
        }

        // If there are no errors, update the database
        if (empty($errors)) {
            $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?";
            $params = [$first_name, $last_name, $email];

            // If password is updated, add it to the SQL query
            if (!empty($hashed_password)) {
                $sql .= ", password = ?";
                $params[] = $hashed_password;
            }

            // If profile picture is uploaded, add it to the SQL query
            if (!empty($profile_picture_path)) {
                $sql .= ", profile_picture = ?";
                $params[] = $profile_picture_path;
            }

            $sql .= " WHERE id = ?"; // Ensure you update only the current user
            $params[] = $user_id;

            // Prepare the SQL statement
            if ($stmt = $conn->prepare($sql)) {
                // Bind the parameters to the statement
                $stmt->bind_param(str_repeat('s', count($params) - 1) . 'i', ...$params);

                // Execute the query
                if ($stmt->execute()) {
                    // Update session variables with the new profile details
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['last_name'] = $last_name;
                    $_SESSION['email'] = $email;
                    if (!empty($profile_picture_path)) {
                        $_SESSION['profile_picture'] = $profile_picture_path;
                    }

                    // Set success message in the session
                    $_SESSION['success_message'] = "Profile updated successfully!";
                    header("Location: ../Student/update_profile.php");
                    exit();
                } else {
                    $errors[] = "Error updating profile: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $errors[] = "Error preparing statement: " . $conn->error;
            }
        }
    }
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
    <title>Update Profile</title>
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

    /* Responsive Styles */
    @media (max-width: 1024px) {
        .sidebar {
            width: 200px;
        }

        .content {
            margin-left: 200px;
        }
    }

    @media (max-width: 768px) {
        /* Sidebar and Content Layout */
        .sidebar {
            width: 100%;
            position: static;
            height: auto;
            padding: 15px;
        }

        .content {
            margin-left: 0;
        }

        /* Form adjustments */
        .update-profile-form {
            width: 90%;
            padding: 20px;
        }

        .cancel-btn {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        /* Profile Picture in Sidebar */
        .sidebar .profile-picture {
            width: 60px;
            height: 60px;
        }

        .sidebar .logo-section img {
            width: 60px;
            height: 60px;
        }

        .sidebar nav a {
            padding: 8px;
        }

        .update-profile-form {
            width: 100%;
            padding: 15px;
        }

        /* Buttons */
        .cancel-btn {
            padding: 10px;
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
    <!-- Update Profile Section -->
    <div class="update-profile-form">
        <h2>Update Profile</h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Handle errors and show them to the user -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
        <p><?php echo $error; ?></p>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="first-name">First Name</label>
                <input type="text" class="form-control" id="first-name" name="first_name" placeholder="Enter your first name" value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="last-name">Last Name</label>
                <input type="text" class="form-control" id="last-name" name="last_name" placeholder="Enter your last name" value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="new-password">New Password</label>
                <input type="password" class="form-control" id="new-password" name="new-password" placeholder="Enter a new password">
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="Confirm your new password">
            </div>

            <div class="form-group">
                <label for="profile-picture">Profile Picture</label>
                <input type="file" class="form-control-file" id="profile-picture" name="profile-picture">
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
            <button type="button" class="cancel-btn mt-3" id="cancel-button">Cancel</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // JavaScript to show the relevant sections when navigation is clicked
    document.querySelectorAll('.sidebar nav a').forEach(function(link) {
        link.addEventListener('click', function() {
            // Hide all content sections
            document.querySelectorAll('.content > div').forEach(function(section) {
                section.style.display = 'none';
            });
            // Show the section corresponding to the clicked link
            const sectionId = link.getAttribute('href').substring(1);
            document.getElementById(sectionId).style.display = 'block';
        });
    });

    // Cancel button to go back to the dashboard
    document.getElementById('cancel-button').addEventListener('click', function() {
        window.location.href = 'student_dashboard.php';  // Go back to the dashboard page
    });

    document.getElementById('burgerToggle').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show'); // Toggle 'show' class
    });

</script>

</body>
</html>