<?php
session_start();
include '../db.php'; // Ensure database connection is included

// Ensure the user is a teacher and logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get the teacher's ID
$admin_id = $_SESSION['user_id'];

// Check if an event ID is provided
if (!isset($_GET['id'])) {
    echo "Invalid event ID.";
    exit();
}

$event_id = intval($_GET['id']);

// Delete the event
$query = "DELETE FROM scheduled_events WHERE id = ? AND admin_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $event_id, $admin_id);

if ($stmt->execute()) {
    header("Location: ../Admin/scheduling.php");
    exit();
} else {
    echo "Error deleting the event.";
}

$stmt->close();
$conn->close();
?>