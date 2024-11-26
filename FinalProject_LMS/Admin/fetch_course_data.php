<?php
// Include the database connection file
require '../db.php';

// Query to fetch course counts by department
$query = "SELECT d.department_name, COUNT(c.id) AS course_count
          FROM courses c
          JOIN departments d ON c.department_id = d.id
          GROUP BY d.department_name";
$result = $conn->query($query);

$departments = [];
$counts = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['department_name'];
        $counts[] = $row['course_count'];
    }
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode(['departments' => $departments, 'counts' => $counts]);
?>
