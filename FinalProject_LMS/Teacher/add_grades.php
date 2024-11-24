<?php
include '../db.php';
session_start();

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

// Fetch available courses from the database
$courses_sql = "SELECT id, name FROM courses";
$courses_result = $conn->query($courses_sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course'];
    $prelim = floatval($_POST['prelim']);
    $midterm = floatval($_POST['midterm']);
    $final = floatval($_POST['final']);

    // Check if grades exceed 100
    if ($prelim > 100 || $midterm > 100 || $final > 100) {
        echo "<script>alert('Grades cannot be greater than 100.');</script>";
    } else {
        // Check if the student already has grades for this course
        $check_sql = "SELECT * FROM grades WHERE student_id = ? AND course_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $student_id, $course_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Grades already exist for this student and course
            echo "<script>alert('Grades for this student in this course already exist.');</script>";
        } else {
            // Adjusted average calculation: Prelim + Midterm + Final / 3
            $average = ($prelim + $midterm + $final) / 3;

            // Determine grade equivalent based on the computed average
            if ($average >= 94) {
                $grade_equivalent = '1.00';
            } elseif ($average >= 88.5) {
                $grade_equivalent = '1.25';
            } elseif ($average >= 83) {
                $grade_equivalent = '1.50';
            } elseif ($average >= 77.5) {
                $grade_equivalent = '1.75';
            } elseif ($average >= 72) {
                $grade_equivalent = '2.00';
            } elseif ($average >= 66.5) {
                $grade_equivalent = '2.25';
            } elseif ($average >= 61) {
                $grade_equivalent = '2.50';
            } elseif ($average >= 55.5) {
                $grade_equivalent = '2.75';
            } elseif ($average >= 50) {
                $grade_equivalent = '3.00';
            } else {
                $grade_equivalent = '5.00';
            }

            // Insert grades into the grades table
            $insert_sql = "INSERT INTO grades (student_id, course_id, prelim, midterm, final, average, grade_equivalent) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iiidsss", $student_id, $course_id, $prelim, $midterm, $final, $average, $grade_equivalent);
            $insert_stmt->execute();

            // Redirect to grading page (after successful insert)
            echo "<script>alert('Grades added successfully.'); window.location.href = 'grading.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Grades</title>
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
    <h1 class="text-center mb-4">Add Grades for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h1>

    <div class="form-section">
        <form method="POST" id="gradesForm">
            <!-- Select Course -->
            <div class="mb-4">
                <label for="course" class="form-label">Course</label>
                <select id="course" name="course" class="form-select" required>
                    <option value="">Select a course</option>
                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Prelim Grade -->
            <div class="mb-4">
                <label for="prelim" class="form-label">Prelim Grade</label>
                <input type="number" step="0.01" min="0" max="100" id="prelim" name="prelim" class="form-control" oninput="calculateEquivalent()" required>
            </div>

            <!-- Midterm Grade -->
            <div class="mb-4">
                <label for="midterm" class="form-label">Midterm Grade</label>
                <input type="number" step="0.01" min="0" max="100" id="midterm" name="midterm" class="form-control" oninput="calculateEquivalent()" required>
            </div>

            <!-- Final Grade -->
            <div class="mb-4">
                <label for="final" class="form-label">Final Grade</label>
                <input type="number" step="0.01" min="0" max="100" id="final" name="final" class="form-control" oninput="calculateEquivalent()" required>
            </div>

            <!-- Grade Equivalent -->
            <div class="remarks" id="remarks"></div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Submit Grades</button>
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

        // New average calculation: (Prelim + Midterm + Final) / 3
        const average = (prelim + midterm + final) / 3;

        let gradeEquivalent = '';
        let remarksClass = '';  // Variable for determining red or green style

        if (final < 50) {
            gradeEquivalent = '5.00';
            remarksClass = 'failed';  // Red for failed
            document.getElementById('remarks').innerHTML = `<span class="remarks ${remarksClass}">Final grade is below 50. Student failed.</span>`;
        } else {
            if (average >= 94) {
                gradeEquivalent = '1.00';
                remarksClass = 'passed';  // Green for passed
            } else if (average >= 88.5) {
                gradeEquivalent = '1.25';
                remarksClass = 'passed';
            } else if (average >= 83) {
                gradeEquivalent = '1.50';
                remarksClass = 'passed';
            } else if (average >= 77.5) {
                gradeEquivalent = '1.75';
                remarksClass = 'passed';
            } else if (average >= 72) {
                gradeEquivalent = '2.00';
                remarksClass = 'passed';
            } else if (average >= 66.5) {
                gradeEquivalent = '2.25';
                remarksClass = 'passed';
            } else if (average >= 61) {
                gradeEquivalent = '2.50';
                remarksClass = 'passed';
            } else if (average >= 55.5) {
                gradeEquivalent = '2.75';
                remarksClass = 'passed';
            } else if (average >= 50) {
                gradeEquivalent = '3.00';
                remarksClass = 'passed';
            } else {
                gradeEquivalent = '5.00';
                remarksClass = 'failed';
            }

            document.getElementById('remarks').innerHTML = `<span class="remarks ${remarksClass}">Grade Equivalent: ${gradeEquivalent}</span>`;
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>