<?php
include 'db.php';

// Replace with actual user ID from your database
$user_id = 33;
$full_name = get_full_name($conn, $user_id);
if ($full_name) {
    echo "Full Name: " . $full_name;
} else {
    echo "Full Name not found for user ID: $user_id";
}

function get_full_name($conn, $user_id) {
    error_log("Fetching full name for user ID: $user_id");
    $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return null;
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    if (!$stmt) {
        error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        return null;
    }
    $result = $stmt->get_result();
    if (!$result) {
        error_log("Get result failed: (" . $stmt->errno . ") " . $stmt->error);
        return null;
    }
    $row = $result->fetch_assoc();
    $stmt->close();
    if ($row) {
        error_log("Fetched full name: " . $row['full_name']);
        return $row['full_name'];
    } else {
        error_log("No user found with ID: $user_id");
        return null;
    }
}
?>
