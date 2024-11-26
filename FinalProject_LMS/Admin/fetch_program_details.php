<?php
require '../db.php';

if (isset($_GET['program_id'])) {
    $program_id = $_GET['program_id'];

    // Fetch department name and courses related to the program
    $query = "SELECT d.department_name, c.course_name
              FROM programs p
              JOIN departments d ON p.department_id = d.id
              LEFT JOIN courses c ON p.id = c.program_id
              WHERE p.id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $program_details = [];
        
        while ($row = $result->fetch_assoc()) {
            $program_details['department_name'] = $row['department_name'];
            $program_details['courses'][] = [
                'course_name' => $row['course_name'],
            ];
        }
        
        $stmt->close();

        echo json_encode($program_details);
    } else {
        echo json_encode(["error" => "Query preparation failed: " . $conn->error]);
    }
} else {
    echo json_encode(["error" => "Program ID not set"]);
}
?>
