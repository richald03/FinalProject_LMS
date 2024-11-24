<?php
session_start();
include '../db.php';

// Redirect if not logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Fetch all quizzes from the database
$sql_quizzes = "SELECT * FROM quizzes ORDER BY due_date ASC, due_time ASC";
$result_quizzes = $conn->query($sql_quizzes);

$quizzes = [];
if ($result_quizzes->num_rows > 0) {
    while ($row = $result_quizzes->fetch_assoc()) {
        $quizzes[] = $row;
    }
}

// Handle Comment Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['quiz_id'], $_POST['comment'])) {
        $quiz_id = intval($_POST['quiz_id']);
        $comment = trim($_POST['comment']);
        $existing_comments = '';

        // Fetch existing comments to prevent duplication
        $comment_query = "SELECT comments FROM quizzes WHERE id = ?";
        $stmt = $conn->prepare($comment_query);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $stmt->bind_result($existing_comments);
        $stmt->fetch();
        $stmt->close();

        // Check if comment is not empty and not already in the comments
        if (!empty($comment) && strpos($existing_comments, $comment) === false) {
            // Append new comment with proper formatting
            $update_sql = "UPDATE quizzes SET comments = CONCAT(IFNULL(comments, ''), '\n- ', ?) WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $comment, $quiz_id);
            $stmt->execute();
        }
    }

    // Handle 'Mark as Done' checkbox
    if (isset($_POST['quiz_id']) && isset($_POST['is_done'])) {
        $quiz_id = intval($_POST['quiz_id']);
        $is_done = intval($_POST['is_done']);

        $update_sql = "UPDATE quizzes SET is_done = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ii", $is_done, $quiz_id);
        $stmt->execute();
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
    <title>Quizzes</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .quiz-card {
            border: 2px solid black;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            position: relative;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .quiz-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .quiz-title {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .quiz-description {
            margin-top: 10px;
        }

        .due-date-time {
            margin-top: 10px;
            font-size: 0.9rem;
        }

        .comments {
            margin-top: 10px;
            font-size: 0.9rem;
            font-style: italic;
            white-space: pre-wrap; /* Preserves line breaks and spaces */
        }

        .back-to-dashboard {
            position: absolute;
            top: 15px;
            left: 15px;
        }

        /* Mark as Done checkbox styling */
        .mark-done-checkbox {
            position: absolute;
            bottom: 10px;
            right: 10px;
        }

        .checkbox-label {
            margin-left: 5px;
            font-size: 0.9rem;
        }

        .comment-section {
            margin-top: 15px;
        }

        .comment-input {
            margin-bottom: 10px;
        }

        @media (max-width: 576px) {
            .quiz-title {
                font-size: 1.2rem;
            }

            .quiz-description, .due-date-time {
                font-size: 0.85rem;
            }

            .quiz-card {
                padding: 15px;
            }

            .mark-done-checkbox {
                bottom: 5px;
                right: 5px;
            }

            .checkbox-label {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 768px) {
            .quiz-title {
                font-size: 1.3rem;
            }

            .quiz-card {
                padding: 18px;
            }
        }

        @media (min-width: 992px) {
            .quiz-card {
                padding: 25px;
            }
        }
    </style>
</head>

<body>

<!-- Back to Dashboard Button -->
<a href="view_courses.php" class="back-to-dashboard">
    <img src="../images/back.png" alt="Back to Dashboard" style="height: 24px; width: 24px;">
</a>

<div class="container mt-4">
    <h2 class="text-center mb-4">Quizzes</h2>

    <?php if (count($quizzes) > 0): ?>
        <?php foreach ($quizzes as $quiz): ?>
            <div class="quiz-card">
                <h4 class="quiz-title"><?= htmlspecialchars($quiz['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="quiz-description"><?= htmlspecialchars($quiz['description'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="due-date-time">
                    <strong>Due Date:</strong> <?= date('F j, Y', strtotime($quiz['due_date'])) ?>
                    <strong>Time:</strong> <?= date('h:i A', strtotime($quiz['due_time'])) ?>
                </p>

                <?php if (!empty($quiz['file'])): ?>
                    <!-- Provide a link to download or view the quiz file -->
                    <p>
                        <a href="uploads/quizzes/<?= htmlspecialchars($quiz['file'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm" target="_blank">View Quiz File</a>
                    </p>
                <?php endif; ?>

                <?php if (!empty($quiz['comments'])): ?>
                    <p class="comments"><strong>Comments:</strong> <?= htmlspecialchars($quiz['comments'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <!-- Comment Section -->
                <div class="comment-section">
                    <form method="POST" class="comment-form">
                        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                        <textarea name="comment" class="form-control comment-input" rows="2" placeholder="Enter your comment here"></textarea>
                        <button type="submit" class="btn btn-success btn-sm">Reply</button>
                    </form>
                </div>

                <!-- Mark as Done Checkbox -->
                <form method="POST" style="margin: 0;">
                    <div class="mark-done-checkbox">
                        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                        <input type="hidden" name="is_done" value="<?= $quiz['is_done'] == 1 ? 0 : 1 ?>">
                        <input type="checkbox" id="mark_done_<?= $quiz['id'] ?>" class="form-check-input"
                            <?php echo $quiz['is_done'] == 1 ? 'checked' : ''; ?>
                            onchange="this.form.submit();">
                        <label for="mark_done_<?= $quiz['id'] ?>" class="checkbox-label">Mark as Done</label>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">No quizzes found.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>