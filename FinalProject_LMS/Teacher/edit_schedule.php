<?php
session_start();
include 'db.php'; // Ensure database connection is included

// Ensure the user is a teacher and logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Get the teacher's ID
$teacher_id = $_SESSION['user_id'];

// Check if an event ID is provided
if (!isset($_GET['id'])) {
    echo "Invalid event ID.";
    exit();
}

$event_id = intval($_GET['id']);

// Fetch the event details
$query = "SELECT * FROM scheduled_events WHERE id = ? AND teacher_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $event_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Event not found or you do not have permission to edit this event.";
    exit();
}

$event = $result->fetch_assoc();

// Update the event if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging: Check the POST data
    var_dump($_POST); // This will show all form data

    $title = $_POST['title'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $description = $_POST['description'] ?? '';

    // Debugging: Check the description value
    echo "Description received: " . htmlspecialchars($description) . "<br>";

    // Validate fields to prevent blank entries
    if (empty($title) || empty($date) || empty($time) || empty($end_time) || empty($description)) {
        echo "All fields are required.";
    } else {
        // Prepare the update query
        $update_query = "UPDATE scheduled_events SET title = ?, date = ?, time = ?, end_time = ?, description = ? WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($update_query);

        // Check if the description is being updated properly
        if ($stmt) {
            $stmt->bind_param("ssssssi", $title, $date, $time, $end_time, $description, $event_id, $teacher_id);

            if ($stmt->execute()) {
                // Redirect to the scheduling page after successful update
                header("Location: scheduling.php");
                exit();
            } else {
                echo "Error executing query: " . $stmt->error;
            }
        } else {
            echo "Error preparing query: " . $conn->error;
        }
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Schedule Event</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .btn {
            background-color: #007bff;
            color: white;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        textarea {
            resize: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Schedule Event</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Title:</label>
                <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($event['title']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date:</label>
                <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars($event['date']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="time" class="form-label">Start Time:</label>
                <input type="time" name="time" id="time" class="form-control" value="<?php echo htmlspecialchars($event['time']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="end_time" class="form-label">End Time:</label>
                <input type="time" name="end_time" id="end_time" class="form-control" value="<?php echo htmlspecialchars($event['end_time']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea name="description" id="description" class="form-control" rows="5" required><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>
            <button type="submit" class="btn">Update Event</button>
        </form>
    </div>
</body>
</html>