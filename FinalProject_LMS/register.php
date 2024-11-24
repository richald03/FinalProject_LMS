<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $userType = $_POST['userType']; 
    $gender = $_POST['gender'];

    // Student/Teacher-specific fields
    $studentID = isset($_POST['studentID']) ? $_POST['studentID'] : '';
    $employeeID = isset($_POST['employeeID']) ? $_POST['employeeID'] : '';

    // Check if the email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert the user into the database
        if ($userType === 'student') {
            $stmt = $conn->prepare("INSERT INTO users (user_type, first_name, last_name, email, password, student_id, gender, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssssss", $userType, $firstName, $lastName, $email, $hashedPassword, $studentID, $gender);
        } elseif ($userType === 'teacher') {
            $stmt = $conn->prepare("INSERT INTO users (user_type, first_name, last_name, email, password, employee_id, gender, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssssss", $userType, $firstName, $lastName, $email, $hashedPassword, $employeeID, $gender);
        }

        // Execute and check if successful
        if ($stmt->execute()) {
            // Redirect to login page after successful registration
            header('Location: index.php');
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
    
    // Close the statement
    $stmt->close();
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
        body {
            background: url(images/bg.jpg);
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            font-family: 'Arial', sans-serif;
        }

        .register-container {
            width: 100%;
            max-width: 900px;
            margin: 50px auto;
            padding: 40px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            max-width: 150px;
        }

        .highlight-link {
            color: #007bff;
            font-weight: bold;
            text-decoration: underline;
        }

        .highlight-link:hover {
            color: #0056b3;
        }

        /* Styling input fields with a thin black border and subtle focus effect */
        input.form-control, select.form-select {
            border: 1px solid #000;
            border-radius: 4px;
        }

        input.form-control:focus, select.form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        button[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            width: 100%;
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .form-row {
            margin-bottom: 15px;
        }

        .col-form-label {
            font-weight: bold;
        }
    </style>
</head>
<body onload="toggleFields()">

<div class="register-container">
    <div class="logo">
        <img src="images/logo.png" alt="LMS Logo">
    </div>

    <h2 class="text-center mb-4">Register for LMS</h2>

    <!-- Show error message if exists -->
    <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

    <form method="POST" action="">
        <div class="row">
            <!-- Left Column (4 fields) -->
            <div class="col-md-6">
                <!-- User Type Selection -->
                <div class="form-row">
                    <label for="userType" class="col-form-label">Register as</label>
                    <select name="userType" class="form-select" id="userType" onchange="toggleFields()" required>
                        <option value="student" selected>Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>

                <!-- First Name -->
                <div class="form-row">
                    <label for="firstName" class="col-form-label">First Name</label>
                    <input type="text" name="firstName" class="form-control" placeholder="Enter your first name" required>
                </div>

                <!-- Last Name -->
                <div class="form-row">
                    <label for="lastName" class="col-form-label">Last Name</label>
                    <input type="text" name="lastName" class="form-control" placeholder="Enter your last name" required>
                </div>

                <!-- Email Address -->
                <div class="form-row">
                    <label for="email" class="col-form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>

                <!-- Password -->
                <div class="form-row">
                    <label for="password" class="col-form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
            </div>

            <!-- Right Column (4 fields) -->
            <div class="col-md-6">
                <!-- Student Specific Fields -->
                <div id="studentFields">
                    <div class="form-row">
                        <label for="studentID" class="col-form-label">Student ID</label>
                        <input type="text" name="studentID" class="form-control" placeholder="Enter your Student ID">
                    </div>
                </div>

                <!-- Teacher Specific Fields -->
                <div id="teacherFields" style="display: none;">
                    <div class="form-row">
                        <label for="employeeID" class="col-form-label">Employee ID</label>
                        <input type="text" name="employeeID" class="form-control" placeholder="Enter your Employee ID">
                    </div>
                </div>

                <!-- Gender -->
                <div class="form-row">
                    <label for="gender" class="col-form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
</div>

<script>
    function toggleFields() {
        var userType = document.getElementById('userType').value;
        document.getElementById('studentFields').style.display = (userType === 'student') ? 'block' : 'none';
        document.getElementById('teacherFields').style.display = (userType === 'teacher') ? 'block' : 'none';
    }
</script>

</body>
</html>