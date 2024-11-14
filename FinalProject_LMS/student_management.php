<?php
include 'db.php';
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

// Query to fetch all student data from the 'students' table, excluding teachers
$sql = "SELECT student_id, first_name, last_name, email, gender FROM users WHERE user_type = 'student'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url(images/bg.jpg);
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            font-family: 'Arial', sans-serif;
            padding: 30px;
        }

        .container {
            background: rgba(255, 255, 255, 0.85);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #4e73df; /* Light blue color */
            font-size: 2.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .sub-title {
            text-align: center;
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 1rem;
            color: #495057;
        }

        table th {
            background-color: #4e73df; /* Blue background */
            color: black; /* Black text color */
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f8f9fc;
        }

        .back-btn {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 1rem;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }

        .alert {
            font-size: 1rem;
            color: #d9534f;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>My Students</h1>
    <p class="sub-title">Here is the list of all students enrolled in the system.</p>

    <?php
    // Check if there are any students in the database
    if ($result->num_rows > 0) {
        echo "<table class='table table-striped'>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                    </tr>
                </thead>
                <tbody>";

        // Display each student's information (only students, no teacher data)
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['student_id']) . "</td>
                    <td>" . htmlspecialchars($row['first_name']) . "</td>
                    <td>" . htmlspecialchars($row['last_name']) . "</td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>" . htmlspecialchars($row['gender']) . "</td>
                </tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<p class='alert'>No students found.</p>";
    }

    // Close the connection
    $conn->close();
    ?>

    <!-- Back to Dashboard Button -->
    <a href="teacher_dashboard.php" class="back-btn">Back to Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>