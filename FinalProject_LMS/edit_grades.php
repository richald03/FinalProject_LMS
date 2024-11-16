<?php
include 'db.php';
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: index.php');
    exit();
}

// Get the student ID from the URL
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch student details from the database
$sql = "SELECT id, student_id, first_name, last_name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Fetch the grades for this student
$grades_sql = "SELECT * FROM grades WHERE student_id = ? LIMIT 1";
$grades_stmt = $conn->prepare($grades_sql);
$grades_stmt->bind_param("i", $student_id);
$grades_stmt->execute();
$grades_result = $grades_stmt->get_result();
$grades = $grades_result->fetch_assoc();

// ** Check if grades are null or empty **
if (!$grades) {
    // If no grades are found, redirect back to the grading page or another page
    header("Location: grading.php?status=nogrades");
    exit();
}

// Fetch available courses from the database
$courses_sql = "SELECT id, name FROM courses";
$courses_result = $conn->query($courses_sql);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course'];
    $prelim = floatval($_POST['prelim']);
    $midterm = floatval($_POST['midterm']);
    $final = floatval($_POST['final']);

    // If final grade is below 50, automatically fail
    if ($final < 50) {
        $average = 0;
        $grade_equivalent = '5.00';
        $descriptive_reading = 'Failed';
    } else {
        // Adjusted average calculation with the new weights
        $average_midterm = ($prelim * 0.15) + $midterm;
        $average = ($average_midterm * 0.25) + ($final * 0.60);

        // Determine the grade equivalent based on the computed average
        if ($average >= 94) {
            $grade_equivalent = '1.00';
            $descriptive_reading = 'Excellent';
        } elseif ($average >= 88.5) {
            $grade_equivalent = '1.25';
            $descriptive_reading = 'Superior';
        } elseif ($average >= 83) {
            $grade_equivalent = '1.50';
            $descriptive_reading = 'Meritorious';
        } elseif ($average >= 77.5) {
            $grade_equivalent = '1.75';
            $descriptive_reading = 'Very Good';
        } elseif ($average >= 72) {
            $grade_equivalent = '2.00';
            $descriptive_reading = 'Good';
        } elseif ($average >= 66.5) {
            $grade_equivalent = '2.25';
            $descriptive_reading = 'Very Satisfactory';
        } elseif ($average >= 61) {
            $grade_equivalent = '2.50';
            $descriptive_reading = 'Satisfactory';
        } elseif ($average >= 55.5) {
            $grade_equivalent = '2.75';
            $descriptive_reading = 'Fair';
        } elseif ($average >= 50) {
            $grade_equivalent = '3.00';
            $descriptive_reading = 'Passing';
        } else {
            $grade_equivalent = '5.00';
            $descriptive_reading = 'Failed';
        }
    }

    // Update grades in the database
    $update_sql = "UPDATE grades SET course_id = ?, prelim = ?, midterm = ?, final = ?, average = ?, grade_equivalent = ?, descriptive_reading = ? 
                WHERE student_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("iidssssi", $course_id, $prelim, $midterm, $final, $average, $grade_equivalent, $descriptive_reading, $student_id);
    $update_stmt->execute();

    // Redirect to grading page
    header("Location: grading.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 800px;
            margin-top: 40px;
        }
        .form-section {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: bold;
            font-size: 1rem;
            color: #495057;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            box-shadow: none;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
        }
        .remarks {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
        .remarks.passed {
            color: green;
        }
        .remarks.failed {
            color: red;
        }
        .btn-primary, .btn-secondary {
            border-radius: 8px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }
        .form-control {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center mb-4">Edit Grades for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h1>

    <div class="form-section">
        <form method="POST" id="gradesForm">
            <!-- Select Course -->
            <div class="mb-4">
                <label for="course" class="form-label">Course</label>
                <select id="course" name="course" class="form-select" required>
                    <option value="">Select a course</option>
                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo $grades['course_id'] == $course['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($course['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Prelim Grade -->
            <div class="mb-4">
                <label for="prelim" class="form-label">Prelim Grade</label>
                <input type="number" step="0.01" min="0" max="100" id="prelim" name="prelim" class="form-control" value="<?php echo $grades['prelim']; ?>" oninput="calculateEquivalent()" required>
            </div>

            <!-- Midterm Grade -->
            <div class="mb-4">
                <label for="midterm" class="form-label">Midterm Grade</label>
                <input type="number" step="0.01" min="0" max="100" id="midterm" name="midterm" class="form-control" value="<?php echo $grades['midterm']; ?>" oninput="calculateEquivalent()" required>
            </div>

            <!-- Final Grade -->
            <div class="mb-4">
                <label for="final" class="form-label">Final Grade</label>
                <input type="number" step="0.01" min="0" max="100" id="final" name="final" class="form-control" value="<?php echo $grades['final']; ?>" oninput="calculateEquivalent()" required>
            </div>

            <!-- Grade Equivalent and Descriptive Reading -->
            <div class="remarks" id="remarks">
                Grade Equivalent: <?php echo $grades['grade_equivalent']; ?> - <?php echo $grades['descriptive_reading']; ?>
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="grading.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>

<script>
function calculateEquivalent() {
    const prelim = parseFloat(document.getElementById('prelim').value) || 0;
    const midterm = parseFloat(document.getElementById('midterm').value) || 0;
    const final = parseFloat(document.getElementById('final').value) || 0;

    const remarksElement = document.getElementById('remarks');

    // Automatically fail if final grade is below 50
    if (final < 50) {
        remarksElement.textContent = `Grade Equivalent: 5.00 - Failed`;
        remarksElement.className = 'remarks failed';  // Apply the 'failed' class for red color
        return;
    }

    // Adjusted average calculation with the new weights
    const average_midterm = (prelim * 0.15) + midterm;
    const average = (average_midterm * 0.25) + (final * 0.60);

    let grade_equivalent = '';
    let descriptive_reading = '';

    if (average >= 94) {
        grade_equivalent = '1.00';
        descriptive_reading = 'Excellent';
    } else if (average >= 88.5) {
        grade_equivalent = '1.25';
        descriptive_reading = 'Superior';
    } else if (average >= 83) {
        grade_equivalent = '1.50';
        descriptive_reading = 'Meritorious';
    } else if (average >= 77.5) {
        grade_equivalent = '1.75';
        descriptive_reading = 'Very Good';
    } else if (average >= 72) {
        grade_equivalent = '2.00';
        descriptive_reading = 'Good';
    } else if (average >= 66.5) {
        grade_equivalent = '2.25';
        descriptive_reading = 'Very Satisfactory';
    } else if (average >= 61) {
        grade_equivalent = '2.50';
        descriptive_reading = 'Satisfactory';
    } else if (average >= 55.5) {
        grade_equivalent = '2.75';
        descriptive_reading = 'Fair';
    } else if (average >= 50) {
        grade_equivalent = '3.00';
        descriptive_reading = 'Passing';
    } else {
        grade_equivalent = '5.00';
        descriptive_reading = 'Failed';
    }

    remarksElement.textContent = `Grade Equivalent: ${grade_equivalent} - ${descriptive_reading}`;
    // Apply passed or failed class based on the average grade
    remarksElement.className = 'remarks ' + (average >= 50 ? 'passed' : 'failed');
}
</script>

</body>
</html>