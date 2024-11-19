<?php
include 'db.php';
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: index.php');
    exit();
}

// Query to fetch student data along with grades and course, sorted by last name
$sql = "SELECT u.id, u.student_id, u.first_name, u.last_name, 
            g.prelim, g.midterm, g.final, g.grade_equivalent, 
            c.name AS course_name
        FROM users u
        LEFT JOIN grades g ON u.id = g.student_id
        LEFT JOIN courses c ON g.course_id = c.id
        WHERE u.user_type = 'student'
        ORDER BY u.last_name ASC"; 

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Add favicon -->
    <link rel="icon" href="../images/edit-icon.png" type="image/png">

    <style>
        body {
            background: url(../images/bg.jpg) no-repeat center center fixed;
            background-size: cover;
            font-family: 'Arial', sans-serif;
            padding: 30px;
            height: 100vh;
        }

        .container {
            background: rgba(255, 255, 255, 0.85);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #4e73df;
            font-size: 2.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table th, table td  {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 1rem;
            color: #495057;
        }
        table th {
            background-color: #4e73df;
            color: black;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f8f9fc;
        }

        .back-btn {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 1rem;
            display: inline-block;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }

        .edit-btn, .delete-btn {
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .edit-btn {
            background-color: #28a745;
        }

        .edit-btn:hover {
            background-color: #218838;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        /* Icon Style */
        .edit-btn img, .delete-btn img {
            width: 16px;
            height: 16px;
            margin-right: 5px;
        }

    </style>
</head>
<body>

<div class="container">
    <h1>Student Grading</h1>
    <p class="sub-title">List of students with their grades for Prelim, Midterm, and Final.</p>

    <?php
    // Check if there are any students in the database
    if ($result->num_rows > 0) {
        echo "<div class='table-responsive'>
                <table class='table table-striped'>
                <thead>
                    <tr>
                        <th>ID#</th>
                        <th>Fullname</th>
                        <th>Course</th>
                        <th>Prelim</th>
                        <th>Midterm</th>
                        <th>Final</th>
                        <th>Grade Equivalent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>";

        $previous_student_id = null; // To track the last displayed student ID

        while ($row = $result->fetch_assoc()) {
            // Check if the student ID is the same as the previous row
            if ($row['student_id'] !== $previous_student_id) {
                // Display the full row for a new student
                echo "<tr>
                    <td>" . htmlspecialchars($row['student_id']) . "</td>
                    <td>
                        <a href='add_grades.php?id=" . $row['id'] . "' 
                        style='text-decoration:none; color:black;' 
                        onmouseover='this.style.color=\"red\";' 
                        onmouseout='this.style.color=\"black\";'>
                    " . htmlspecialchars($row['last_name']) . ", " . htmlspecialchars($row['first_name']) . "</a>
                    </td>
                    <td>" . (isset($row['course_name']) ? htmlspecialchars($row['course_name']) : '--') . "</td>
                    <td>" . (isset($row['prelim']) ? htmlspecialchars($row['prelim']) : '--') . "</td>
                    <td>" . (isset($row['midterm']) ? htmlspecialchars($row['midterm']) : '--') . "</td>
                    <td>" . (isset($row['final']) ? htmlspecialchars($row['final']) : '--') . "</td>
                    <td>" . (isset($row['grade_equivalent']) ? htmlspecialchars($row['grade_equivalent']) : '--') . "</td>
                    <td>
                        <a href='edit_grades.php?id=" . $row['id'] . "' class='edit-btn'>
                            <img src='../images/edit-button.png' alt='Edit' /> 
                        </a>
                        <a href='delete_grades.php?id=" . $row['id'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this grade?\");'>
                            <img src='../images/trash.png' alt='Delete Grades' />
                        </a>
                    </td>
                </tr>";
            } else {
                // For the same student, display only grades
                echo "<tr>
                    <td></td> <!-- Empty cell for ID -->
                    <td></td> <!-- Empty cell for Fullname -->
                    <td>" . (isset($row['course_name']) ? htmlspecialchars($row['course_name']) : '--') . "</td>
                    <td>" . (isset($row['prelim']) ? htmlspecialchars($row['prelim']) : '--') . "</td>
                    <td>" . (isset($row['midterm']) ? htmlspecialchars($row['midterm']) : '--') . "</td>
                    <td>" . (isset($row['final']) ? htmlspecialchars($row['final']) : '--') . "</td>
                    <td>" . (isset($row['grade_equivalent']) ? htmlspecialchars($row['grade_equivalent']) : '--') . "</td>
                    <td></td> <!-- Empty cell for Actions -->
                </tr>";
            }
            // Update the previous_student_id
            $previous_student_id = $row['student_id'];
        }

        echo "</tbody></table></div>";
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