<?php

include '../db.php';  

$success = "";
$errors = [];

// Fetch programs and departments
$programs = $conn->query("SELECT * FROM programs");
$departments = $conn->query("SELECT * FROM departments");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_users'])) {
    // Get the form data
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $userType = $_POST['userType'] ?? ''; 
    $gender = $_POST['gender'] ?? '';
    $program_name = $_POST['program'] ?? '';
    $department_name = $_POST['department'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $employee_id = $_POST['employee_id'] ?? '';

    // Validate password
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $errors[] = 'Password must contain at least 8 characters, including uppercase, lowercase letters, numbers, and special characters.';
    }

    // Validate required fields for students
    if ($userType === 'student') {
        if (empty($student_id)) {
            $errors[] = 'Student ID is required.';
        } elseif (empty($program_name)) {
            $errors[] = 'Program is required.';
        } elseif (empty($gender)) {
            $errors[] = 'Gender is required.';
        } else {
            // Check for unique student_id
            $stmt = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = 'Student ID already exists.';
                $student_id = ''; // Clear only the student_id field
            }
            $stmt->close();
        }
    }

    // Validate required fields for teachers
    if ($userType === 'teacher') {
        if (empty($employee_id)) {
            $errors[] = 'Employee ID is required.';
        } elseif (empty($department_name)) {
            $errors[] = 'Department is required.';
        } elseif (empty($gender)) {
            $errors[] = 'Gender is required.';
        } else {
            // Check for unique employee_id
            $stmt = $conn->prepare("SELECT id FROM users WHERE employee_id = ?");
            $stmt->bind_param("s", $employee_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = 'Employee ID already exists.';
                $employee_id = ''; // Clear only the employee_id field
            }
            $stmt->close();
        }
    }

    if (empty($errors)) {
        // Fetch program_id and department_id based on names
        $program_id = null;
        $department_id = null;

        if (!empty($program_name)) {
            $stmt = $conn->prepare("SELECT id FROM programs WHERE program_name = ?");
            $stmt->bind_param("s", $program_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $program_id = $result->fetch_assoc()['id'];
            }
            $stmt->close();
        }

        if (!empty($department_name)) {
            $stmt = $conn->prepare("SELECT id FROM departments WHERE department_name = ?");
            $stmt->bind_param("s", $department_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $department_id = $result->fetch_assoc()['id'];
            }
            $stmt->close();
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert the user into the database
        if ($userType === 'student') {
            $stmt = $conn->prepare("INSERT INTO users (user_type, first_name, last_name, email, password, student_id, program, program_id, gender, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssssssis", $userType, $firstName, $lastName, $email, $hashedPassword, $student_id, $program_name, $program_id, $gender);
        } elseif ($userType === 'teacher') {
            $stmt = $conn->prepare("INSERT INTO users (user_type, first_name, last_name, email, password, employee_id, department, department_id, gender, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssssssis", $userType, $firstName, $lastName, $email, $hashedPassword, $employee_id, $department_name, $department_id, $gender);
        }

        // Execute and check if successful
        if ($stmt->execute()) {
            $success = "Registration successful!";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close(); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LMS Registration</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
.register-container {
    margin-left: 270px; /* Space for the sidebar */
    padding: 20px;
}
.register-container .logo {
    text-align: center;
    margin-bottom: 20px;
}
.register-container h2 {
    text-align: center;
    margin-bottom: 20px;
    padding: 10px;
    background-color: #f0f8ff; /* Light blue background */
    border-radius: 5px;
}
.form-control, .form-select {
    width: 100%; /* Make it responsive */
    max-width: 700px; /* Maximum width */
}

.form-button {
    display: block;
    margin: 20px auto; /* Centers the button and adds spacing */
    width: 100%; /* Make the button responsive */
    max-width: 700px; /* Maximum width */
    padding: 10px; /* Adjust padding for better button appearance */
    background-color: #007bff; /* Button background color */
    color: #fff; /* Button text color */
    border: none; /* Remove border */
    border-radius: 5px; /* Rounded corners */
    text-align: center; /* Center text */
    text-decoration: none; /* Remove underline from text */
}
.form-button:hover {
    background-color: #0056b3; /* Change button color on hover */
}

@media (max-width: 767.98px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    .register-container {
        margin-left: 0;
        padding: 10px;
    }
    .form-control, .form-select {
        width: 100%; /* Make it responsive */
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
<body onload="toggleFields()">

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

<div class="register-container form-spacing">
    <h2 class="text-center mb-4">Register for LMS</h2>

    <?php if (!empty($errors)) { foreach ($errors as $error) { echo "<div class='alert alert-danger'>$error</div>"; } } ?>
    <?php if (!empty($success)) { echo "<div id='successMessage' class='alert alert-success'>$success</div>"; } ?>

    <!-- Error messages div -->
    <div id="errorMessages" class="alert alert-danger" role="alert" style="display: none;"></div>
    <!-- Password error messages -->
    <div id="passwordError" class="alert alert-danger" role="alert" style="display: none;"></div>

    <!-- Registration form -->
    <form class="register-form" onsubmit="return validateForm()" method="POST">
        <div class="row">
            <div class="col-md-6">
                <div class="form-row">
                    <label for="userType" class="col-form-label">Register as</label>
                    <select name="userType" class="form-select" id="userType" onchange="toggleFields()" required>
                        <option value="student" selected>Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="admin">Admin</option>
                        </select>
            </div>

            <div class="form-row">
                <label for="firstName" class="col-form-label">First Name</label>
                <input type="text" name="firstName" class="form-control" placeholder="Enter your first name" required>
            </div>

            <div class="form-row">
                <label for="lastName" class="col-form-label">Last Name</label>
                <input type="text" name="lastName" class="form-control" placeholder="Enter your last name" required>
            </div>

            <div class="form-row">
                <label for="email" class="col-form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="form-row">
                <label for="password" class="col-form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" id="password" required>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Student Specific Fields -->
            <div id="studentFields" class="form-row">
                <label for="student_id" class="col-form-label">Student ID</label>
                <input type="text" name="student_id" class="form-control" placeholder="Enter your Student ID" id="student_id">
            </div>
            <div class="form-row" id="studentFields1">
                <label for="program" class="col-form-label">Program</label>
                <select name="program" class="form-select" id="program">
                    <option value="">Select Program</option>
                    <?php while ($program = $programs->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($program['program_name']) ?>"><?= htmlspecialchars($program['program_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Teacher Specific Fields -->
            <div id="teacherFields" class="form-row" style="display: none;">
                <label for="employee_id" class="col-form-label">Employee ID</label>
                <input type="text" name="employee_id" class="form-control" placeholder="Enter your Employee ID" id="employee_id">
            </div>
            <div class="form-row" id="teacherFields1" style="display: none;">
                <label for="department" class="col-form-label">Department</label>
                <select name="department" class="form-select" id="department">
                    <option value="">Select Department</option>
                    <?php while ($department = $departments->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($department['department_name']) ?>"><?= htmlspecialchars($department['department_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-row">
                <label for="gender" class="col-form-label">Gender</label>
                <select name="gender" class="form-select" id="gender" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Adjusted Button -->
    <button type="submit" name="create_users" class="btn btn-primary form-button">Register</button>
</form>
</div>

<script>
    function validatePassword() {
        const password = document.getElementById('password').value;
        const passwordError = document.getElementById('passwordError');
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

        if (!passwordPattern.test(password)) {
            passwordError.textContent = 'Password must contain at least 8 characters, including uppercase, lowercase letters, numbers, and special characters.';
            passwordError.style.display = 'block';
            return false;
        } else {
            passwordError.style.display = 'none';
            return true;
        }
    }

    function validateForm() {
        let isValid = true;
        let errorMessages = [];

        // Clear previous error messages
        document.getElementById('errorMessages').innerHTML = '';
        document.getElementById('errorMessages').style.display = 'none';

        // Fetch input values
        let userType = document.getElementById('userType').value;
        let student_id = document.getElementById('student_id').value;
        let employee_id = document.getElementById('employee_id').value;
        let program = document.getElementById('program').value;
        let department = document.getElementById('department').value;
        let gender = document.getElementById('gender').value;

        // Real-time Password Validation
        if (!validatePassword()) {
            isValid = false;
        }

        // Student ID, Program, and Gender validation for students
        if (userType === 'student') {
            if (!student_id) {
                isValid = false;
                errorMessages.push('Student ID is required.');
            } else if (!program) {
                isValid = false;
                errorMessages.push('Program is required.');
            } else if (!gender) {
                isValid = false;
                errorMessages.push('Gender is required.');
            }
        }

        // Employee ID, Department, and Gender validation for teachers
        if (userType === 'teacher') {
            if (!employee_id) {
                isValid = false;
                errorMessages.push('Employee ID is required.');
            } else if (!department) {
                isValid = false;
                errorMessages.push('Department is required.');
            } else if (!gender) {
                isValid = false;
                errorMessages.push('Gender is required.');
            }
        }

        // Display error messages if validation fails
        if (errorMessages.length > 0) {
            document.getElementById('errorMessages').innerHTML = errorMessages.join('<br>');
            document.getElementById('errorMessages').style.display = 'block';
        }

        return isValid;
    }

    document.getElementById('password').addEventListener('input', validatePassword);

    function toggleFields() {
        const userType = document.getElementById('userType').value;
        const studentFields = document.getElementById('studentFields');
        const studentFields1 = document.getElementById('studentFields1');
        const teacherFields = document.getElementById('teacherFields');
        const teacherFields1 = document.getElementById('teacherFields1');

        if (userType === 'student') {
            studentFields.style.display = 'block';
            studentFields1.style.display = 'block';
            teacherFields.style.display = 'none';
            teacherFields1.style.display = 'none';
        } else if (userType === 'teacher') {
            studentFields.style.display = 'none';
            studentFields1.style.display = 'none';
            teacherFields.style.display = 'block';
            teacherFields1.style.display = 'block';
        } else {
            studentFields.style.display = 'none';
            studentFields1.style.display = 'none';
            teacherFields.style.display = 'none';
            teacherFields1.style.display = 'none';
        }
    }

    function autoDismissSuccessMessage() {
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000);
        }
    }

    window.onload = function() {
        toggleFields();
        autoDismissSuccessMessage();
        
    };

    document.getElementById('burgerToggle').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show'); // Toggle 'show' class
    });

</script>


<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>