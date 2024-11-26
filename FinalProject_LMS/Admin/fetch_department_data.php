<?php
// Include the database connection file
require '../db.php';

// Query to fetch department counts
$query = "SELECT department_name, COUNT(id) AS department_count
          FROM departments
          GROUP BY department_name";
$result = $conn->query($query);

$departments = [];
$counts = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['department_name'];
        $counts[] = $row['department_count'];
    }
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode(['departments' => $departments, 'counts' => $counts]);
?>
