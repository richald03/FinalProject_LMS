<?php
include '../db.php';
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

// Fetch only courses with grades for this student
$courses_sql = "SELECT c.id, c.name 
                FROM courses c 
                INNER JOIN grades g ON c.id = g.course_id 
                WHERE g.student_id = ?";
$courses_stmt = $conn->prepare($courses_sql);
$courses_stmt->bind_param("i", $student_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fetch_grades']) && isset($_POST['course_id'])) {
        $course_id = floatval($_POST['course_id']);
        $sql = "SELECT prelim, midterm, final FROM grades WHERE course_id = ? AND student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $course_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode([]);
        }
        exit(); // Stop further processing for AJAX
    }

    // Form submission logic for saving grades for specific course
    $course_id = floatval($_POST['course']);
    $prelim = floatval($_POST['prelim']);
    $midterm = floatval($_POST['midterm']);
    $final = floatval($_POST['final']);

    // New grade calculation logic: (Prelim + Midterm + Final) / 3
    $average = ($prelim + $midterm + $final) / 3;

    // Determine grade equivalent based on the average
    if ($final < 50) {
        $grade_equivalent = '5.00';
    } else {
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
    }

    // Update grades in the database for the specific course and student
    $update_sql = "UPDATE grades 
                SET prelim = ?, midterm = ?, final = ?, average = ?, grade_equivalent = ? 
                WHERE student_id = ? AND course_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ddddsii", $prelim, $midterm, $final, $average, $grade_equivalent, $student_id, $course_id);
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        .btn-primary {
            border-radius: 8px;
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-secondary {
            border-radius: 8px;
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }
        .mb-4 {
            margin-bottom: 1.5rem;
        }
        .form-control {
            margin-bottom: 20px;
        }
        select.form-select {
            border-radius: 8px;
            background-color: #f1f1f1;
            border-color: #ced4da;
        }
        select.form-select:focus {
            border-color: #007bff;
            background-color: #ffffff;
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
        @media (max-width: 576px) {
            .container {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center mb-4">Edit Grades for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h1>

    <div class="form-section">
        <form method="POST" id="gradesForm">
            <!-- Course Selection -->
            <div class="mb-4">
                <label for="course" class="form-label">Course</label>
                <select id="course" name="course" class="form-select" required>
                    <option value="">Select a course</option>
                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                        <option value="<?php echo $course['id']; ?>">
                            <?php echo htmlspecialchars($course['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Prelim Grade -->
            <div class="mb-4">
                <label for="prelim" class="form-label">Prelim Grade</label>
                <input type="number" step="0.01" min="0" max="100" id="prelim" name="prelim" class="form-control" value="" required>
            </div>

            <!-- Midterm Grade -->
            <div class="mb-4">
                <label for="midterm" class="form-label">Midterm Grade</label>
                <input type="number" step="0.01" min="0" max="100" id="midterm" name="midterm" class="form-control" value="" required>
            </div>

            <!-- Final Grade -->
            <div class="mb-4">
                <label for="final" class="form-label">Final Grade</label>
                <input type="number" step="0.01" min="0" max="100" id="final" name="final" class="form-control" value="" required>
            </div>

            <!-- Remarks -->
            <div id="remarks"></div>

            <!-- Submit and Back buttons -->
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="grading.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#course').change(function () {
        const courseId = $(this).val();
        if (courseId) {
            $.ajax({
                url: 'grading.php',
                type: 'POST',
                data: {
                    fetch_grades: true,
                    course_id: courseId
                },
                success: function (data) {
                    const grades = JSON.parse(data);
                    if (grades) {
                        $('#prelim').val(grades.prelim);
                        $('#midterm').val(grades.midterm);
                        $('#final').val(grades.final);
                    } else {
                        $('#prelim').val('');
                        $('#midterm').val('');
                        $('#final').val('');
                    }
                }
            });
        }
    });

    // Grade calculation on input change
    $('#prelim, #midterm, #final').on('input', function () {
        calculateEquivalent();
    });

    function calculateEquivalent() {
        const prelim = parseFloat($('#prelim').val()) || 0;
        const midterm = parseFloat($('#midterm').val()) || 0;
        const final = parseFloat($('#final').val()) || 0;

        // Calculate the average
        const average = (prelim + midterm + final) / 3;

        let gradeEquivalent = '';
        let remarksClass = '';  // Variable for determining red or green style

        if (final < 50) {
            gradeEquivalent = '5.00';
            remarksClass = 'failed';  // Red for failed
            $('#remarks').html(`<span class="remarks ${remarksClass}">Final grade is below 50. Student failed.</span>`);
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

            $('#remarks').html(`<span class="remarks ${remarksClass}">Grade Equivalent: ${gradeEquivalent}</span>`);
        }
    }
});
</script>

</body>
</html>