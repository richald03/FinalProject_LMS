<?php
require '../db.php';

$program_id = isset($_GET['program_id']) ? $_GET['program_id'] : null;
$query = isset($_GET['query']) ? $_GET['query'] : '';

if ($program_id) {
    $sql = "SELECT studentID, first_name, last_name FROM users WHERE user_type = 'student' AND program_id = ? AND (first_name LIKE ? OR last_name LIKE ? OR studentID LIKE ?)";
    $stmt = $conn->prepare($sql);
    $search_term = '%' . $query . '%';
    $stmt->bind_param('isss', $program_id, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = [];

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    $stmt->close();
    echo json_encode($students);
} else {
    echo json_encode([]);
}
?>
