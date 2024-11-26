<?php
// Include the database connection file
require '../db.php';

// Query to fetch student counts by program
$query = "SELECT p.program_name, COUNT(u.id) AS student_count
          FROM users u
          JOIN programs p ON u.program_id = p.id
          WHERE u.user_type = 'student'
          GROUP BY p.program_name";
$result = $conn->query($query);

$programs = [];
$counts = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row['program_name'];
        $counts[] = $row['student_count'];
    }
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode(['programs' => $programs, 'counts' => $counts]);
?>
