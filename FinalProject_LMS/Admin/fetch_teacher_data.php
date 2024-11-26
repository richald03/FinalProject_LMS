<?php
// Include the database connection file
require '../db.php';

// Query to fetch teacher counts by department
$query = "SELECT d.department_name, COUNT(u.id) AS teacher_count
          FROM users u
          JOIN departments d ON u.department_id = d.id
          WHERE u.user_type = 'teacher'
          GROUP BY d.department_name";
$result = $conn->query($query);

$departments = [];
$counts = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['department_name'];
        $counts[] = $row['teacher_count'];
    }
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode(['departments' => $departments, 'counts' => $counts]);
?>
