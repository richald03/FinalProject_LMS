<?php
session_start();
include 'db.php'; 

// Redirect if not logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Fetch all assignments from the database
$sql_assignments = "SELECT * FROM assignments ORDER BY due_date ASC"; // No `course_id` filtering needed
$result_assignments = $conn->query($sql_assignments);

$assignments = [];
if ($result_assignments->num_rows > 0) {
    while ($row = $result_assignments->fetch_assoc()) {
        $assignments[] = $row;
    }
}

$error_message = ''; // Variable to store error messages
$success_message = ''; // Variable to store success messages

// Handle the checkbox state update if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle 'Mark as Done' checkbox
    if (isset($_POST['assignment_id']) && isset($_POST['is_done'])) {
        $assignment_id = $_POST['assignment_id'];
        $is_done = $_POST['is_done'];

        // Update the database with the new state
        $update_sql = "UPDATE assignments SET is_done = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ii", $is_done, $assignment_id);
        $stmt->execute();
    }

    // Define upload directory (use relative path)
    $upload_dir = __DIR__ . '/uploads/assignments/'; // This ensures the path is correct
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
    }

    // Check if file upload is happening
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
        $file_name = $_FILES['assignment_file']['name'];
        $file_tmp = $_FILES['assignment_file']['tmp_name'];
        $file_size = $_FILES['assignment_file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Define allowed file types
        $allowed_extensions = ['pdf', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov', 'mp3', 'wav'];
        if (in_array($file_ext, $allowed_extensions)) {
            $file_path = $upload_dir . basename($file_name); // Save the file to the designated path

            // Check if the file already exists (optional)
            if (file_exists($file_path)) {
                $error_message = 'The file already exists. Please upload a different file.';
            } else {
                // Move the uploaded file to the uploads directory
                if (move_uploaded_file($file_tmp, $file_path)) {
                    // Update the database with the file path
                    $update_sql = "UPDATE assignments SET file = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("si", $file_path, $_POST['assignment_id']);
                    $stmt->execute();
                    $success_message = 'File successfully uploaded and submitted!';
                } else {
                    $error_message = 'Failed to upload the file. Please try again.';
                }
            }
        } else {
            $error_message = 'Invalid file type. Please upload a PDF, DOCX, TXT, image, or video file.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - To Do List</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .assignment-card {
            border: 2px solid black;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            position: relative;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .assignment-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .assignment-title {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .assignment-description {
            margin-top: 10px;
        }

        .due-date {
            margin-top: 5px;
            font-size: 0.9rem;
        }

        /* Back to Dashboard Button */
        .back-to-dashboard {
            position: absolute;
            top: 15px;
            left: 15px;
        }

        /* Style for the checkbox at the bottom right */
        .mark-done-checkbox {
            position: absolute;
            bottom: 10px;
            right: 10px;
        }

        .checkbox-label {
            font-size: 0.9rem;
            margin-left: 5px;
        }

        /* Style for the checkbox when it is checked */
        .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
        }

        /* Add some space and make the checkbox and label bigger on smaller screens */
        @media (max-width: 576px) {
            .assignment-card {
                padding: 15px;
            }

            .assignment-title {
                font-size: 1.25rem;
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
    <h2 class="text-center mb-4">Assignments - To Do</h2>

    <!-- Display Success or Error Messages only for file upload -->
    <?php if ($error_message && isset($_FILES['assignment_file'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <?php if ($success_message && isset($_FILES['assignment_file'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <!-- Display Assignments -->
    <?php if (count($assignments) > 0): ?>
        <form method="POST" enctype="multipart/form-data">
        <?php foreach ($assignments as $assignment): ?>
            <div class="assignment-card">
                <h4 class="assignment-title"><?= htmlspecialchars($assignment['title']) ?></h4>
                <p class="assignment-description"><?= htmlspecialchars($assignment['description']) ?></p>
                <p class="due-date"><strong>Due Date:</strong> <?= date('F j, Y', strtotime($assignment['due_date'])) ?></p>

                <!-- Mark as Done Checkbox positioned at the bottom right of the card -->
                <label class="mark-done-checkbox">
                    <input type="hidden" name="is_done" value="0">
                    <input type="checkbox" name="is_done" value="1" 
                        id="mark_done_<?= $assignment['id'] ?>" class="form-check-input" 
                        <?php echo $assignment['is_done'] == 1 ? 'checked' : ''; ?>
                        onchange="this.form.assignment_id.value='<?= $assignment['id'] ?>'; this.form.submit();">
                    <span class="checkbox-label">Mark as Done</span>
                </label>

                <!-- File upload input for 'Turn In' -->
                <div class="mt-3">
                    <input type="file" name="assignment_file" class="form-control" accept=".pdf, .docx, .txt, .jpg, .jpeg, .png, .gif, .mp4, .avi, .mov, .mp3, .wav">
                    <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                    <button type="submit" class="btn btn-success mt-2">Turn In</button>
                </div>
            </div>
        <?php endforeach; ?>
        </form>
    <?php else: ?>
        <p class="text-center">No assignments found.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>