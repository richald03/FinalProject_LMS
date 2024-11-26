<?php
// Include the database connection file
require '../db.php';

// Query to fetch program counts by department
$query = "SELECT d.department_name, COUNT(p.id) AS program_count
          FROM programs p
          JOIN departments d ON p.department_id = d.id
          GROUP BY d.department_name";
$result = $conn->query($query);

$departments = [];
$counts = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['department_name'];
        $counts[] = $row['program_count'];
    }
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode(['departments' => $departments, 'counts' => $counts]);
?>
