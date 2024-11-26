<?php
include '../db.php'; 

// Ensure a course ID is provided
if (!isset($_GET['course_id'])) {
    echo json_encode(['error' => 'Course ID is required']);
    exit();
}

$course_id = intval($_GET['course_id']);
$response = [];

// Fetch course details including department and program
$query = "
    SELECT 
        c.id AS course_id,
        c.course_name,
        c.course_code,
        d.department_name,
        p.program_name
    FROM 
        courses c
    JOIN 
        departments d ON c.department_id = d.id
    JOIN 
        programs p ON c.program_id = p.id
    WHERE 
        c.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Course not found']);
    exit();
}

$courseDetails = $result->fetch_assoc();
$response = array_merge($response, $courseDetails);
$stmt->close();

// Fetch registered teacher
$teacher_query = "
    SELECT 
        CONCAT(u.first_name, ' ', u.last_name) AS full_name
    FROM 
        course_users cu 
    JOIN 
        users u ON cu.user_id = u.id 
    WHERE 
        cu.course_id = ? AND cu.user_type = 'teacher'
";
$stmt = $conn->prepare($teacher_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$teacher_result = $stmt->get_result();

$teachers = [];
while ($row = $teacher_result->fetch_assoc()) {
    $teachers[] = $row;
}
$response['teachers'] = $teachers;
error_log("Fetched Teachers: " . json_encode($teachers));
$stmt->close();

// Fetch registered students
$student_query = "
    SELECT 
        CONCAT(u.first_name, ' ', u.last_name) AS full_name
    FROM 
        course_users cu 
    JOIN 
        users u ON cu.user_id = u.id 
    WHERE 
        cu.course_id = ? AND cu.user_type = 'student'
";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$student_result = $stmt->get_result();

$students = [];
while ($row = $student_result->fetch_assoc()) {
    $students[] = $row;
}
$response['students'] = $students;
error_log("Fetched Students: " . json_encode($students));
$stmt->close();

$conn->close();

echo json_encode($response);
?>