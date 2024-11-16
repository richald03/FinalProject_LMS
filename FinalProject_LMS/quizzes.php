<?php
session_start();
include 'db.php'; // Include database connection

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Handle create quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_quiz'])) {
    $quiz_name = $_POST['quiz_name'];
    $question_title = $_POST['question_title']; // Renamed description to question_title
    $due_date = $_POST['due_date'];
    $due_time = $_POST['due_time'];
    $comments = $_POST['comments'];  // Get the comments input
    $course_id = $_POST['course_id'];

    // Handle file upload
    $file_name = null;
    if (isset($_FILES['quiz_file']) && $_FILES['quiz_file']['error'] == 0) {
        $file_name = basename($_FILES['quiz_file']['name']);
        $target_dir = "uploads/quizzes/";
        $target_file = $target_dir . $file_name;
        
        // Ensure the uploads directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move uploaded file to the target directory
        if (!move_uploaded_file($_FILES['quiz_file']['tmp_name'], $target_file)) {
            $_SESSION['error_message'] = "Failed to upload quiz file.";
            header("Location: quizzes.php?course_id=$course_id");
            exit();
        }
    }

    // Insert quiz data into the database, including the comments
    $sql = "INSERT INTO quizzes (course_id, name, description, due_date, due_time, file, comments) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $course_id, $quiz_name, $question_title, $due_date, $due_time, $file_name, $comments);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Quiz created successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to create quiz.";
    }
    $stmt->close();
}

// Handle delete quiz
if (isset($_GET['delete_quiz_id'])) {
    $delete_quiz_id = $_GET['delete_quiz_id'];
    $delete_sql = "DELETE FROM quizzes WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_quiz_id);
    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = "Quiz deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to delete quiz.";
    }
    $delete_stmt->close();
    header("Location: quizzes.php?course_id={$_GET['course_id']}");
    exit();
}

// Fetch quizzes for a specific course
$quizzes = [];
$course_id = $_GET['course_id']; // Ensure course_id is passed in the URL
$sql = "SELECT * FROM quizzes WHERE course_id = ? ORDER BY due_date, due_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $quizzes[] = $row;
}
$stmt->close();

// Array of light background colors
$light_colors = [
    "#F7E7C6", "#D1F2A5", "#FFDDC1", "#C1F0C1", "#E4E9E2", "#C1D3D7", "#F3D1DC", "#D8E9A8"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: bold;
        }

        .btn-custom {
            font-size: 14px;
            border-radius: 5px;
            text-transform: uppercase;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-create {
            background-color: #4CAF50;
            color: white;
        }

        .btn-create:hover {
            background-color: #45a049;
        }

        .btn-back {
            background-color: #007bff;
            color: white;
            margin-left: 20px;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }

        .quiz-view {
            margin-top: 40px;
        }

        .quiz-view .quiz-card {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .quiz-view .quiz-card h5 {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .quiz-view .quiz-card p {
            font-size: 1.1rem;
            color: #666;
        }

        .quiz-actions img {
            width: 20px;
            height: 20px;
            margin: 5px;
            cursor: pointer;
        }

        .quiz-actions img:hover {
            opacity: 0.7;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Create Quiz</h2>

    <!-- Success or Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Create Quiz Form -->
    <form method="POST" class="quiz-form" enctype="multipart/form-data">
        <div class="form-group">
            <label for="quiz_name">Quiz Name</label>
            <input type="text" id="quiz_name" name="quiz_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="question_title">Question</label>
            <input type="text" id="question_title" name="question_title" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="due_time">Due Time</label>
            <input type="time" id="due_time" name="due_time" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="comments">Comments</label> <!-- New comments field -->
            <textarea id="comments" name="comments" class="form-control" rows="4" placeholder="Optional comments about the quiz..."></textarea>
        </div>

        <div class="form-group">
            <label for="quiz_file">Upload Quiz File (Optional)</label>
            <input type="file" id="quiz_file" name="quiz_file" class="form-control">
        </div>

        <input type="hidden" name="course_id" value="<?= $course_id; ?>">

        <div class="button-group">
            <button type="submit" name="create_quiz" class="btn btn-custom btn-create">Create Quiz</button>
            <a href="course_management.php" class="btn btn-custom btn-back">
                <img src="images/back.png" alt="Back" style="width: 16px; height: 16px; margin-right: 8px;">
                Back
            </a>
        </div>
    </form>

    <!-- View Quizzes -->
    <div class="quiz-view">
        <h2>All Quizzes</h2>
        <?php if (count($quizzes) > 0): ?>
            <?php foreach ($quizzes as $quiz): ?>
                <!-- Generate a random light color for each quiz -->
                <?php $bg_color = $light_colors[array_rand($light_colors)]; ?>
                <div class="quiz-card" style="background-color: <?= $bg_color; ?>">
                    <h5><?= htmlspecialchars($quiz['name']); ?></h5>
                    <p><strong>Due Date:</strong> <?= $quiz['due_date']; ?> <strong>Time:</strong> <?= $quiz['due_time']; ?></p>
                    <p><strong>Comments:</strong> <?= $quiz['comments'] ? htmlspecialchars($quiz['comments']) : 'No comments.'; ?></p> <!-- Display comments -->
                    <div class="quiz-actions">
                        <a href="edit_quiz.php?id=<?= $quiz['id']; ?>&course_id=<?= $course_id; ?>"><img src="images/edit-button.png" alt="Edit"></a>
                        <a href="quizzes.php?delete_quiz_id=<?= $quiz['id']; ?>&course_id=<?= $course_id; ?>" onclick="return confirm('Are you sure you want to delete this quiz?');"><img src="images/trash.png" alt="Delete"></a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning text-center">No quizzes found.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>