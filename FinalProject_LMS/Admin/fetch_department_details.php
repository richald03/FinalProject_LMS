<?php
require '../db.php';

if (isset($_GET['department_id'])) {
    $department_id = $_GET['department_id'];
    
    // Fetch programs and courses related to the department
    $query = "SELECT p.program_name, c.course_name 
              FROM programs p 
              LEFT JOIN courses c ON p.id = c.program_id 
              WHERE p.department_id = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $programs_courses = [];
        
        while ($row = $result->fetch_assoc()) {
            $programs_courses[] = $row;
        }
        
        $stmt->close();
        
        echo json_encode($programs_courses);
    } else {
        echo json_encode(["error" => "Query preparation failed: " . $conn->error]);
    }
} else {
    echo json_encode(["error" => "Department ID not set"]);
}
?>
