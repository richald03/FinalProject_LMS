<?php
include 'db.php';
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $student_id = $_GET['id'];

    // Query to delete the grades associated with the student
    $sql = "DELETE FROM grades WHERE student_id= ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        // Redirect back to the grading page with a success message
        header("Location: grading.php?status=deleted");
    } else {
        // Redirect back with an error message
        header("Location: grading.php?status=error");
    }

    $stmt->close();
}

$conn->close();
?>