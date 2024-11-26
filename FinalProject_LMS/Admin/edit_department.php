<?php
session_start();

// Redirect to login if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: /FinalProject_LMS/login.php");
    exit();
}

// Include the database connection file
require '../db.php';

// Handle form submission for editing a department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_department'])) {
    $id = $_POST['id'];
    $name = $_POST['department_name'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE departments SET department_name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $description, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Changes saved successfully.'); window.location.href='manage_department.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch department details for editing
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $department = $result->fetch_assoc();

    if (!$department) {
        echo "Department not found.";
        exit();
    }

    $stmt->close();
} else {
    echo "No department ID provided.";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Department</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Edit Department</h2>
    <form method="POST" action="edit_department.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($department['id']) ?>">
        <div class="form-group">
            <label for="department_name">Department Name</label>
            <input type="text" class="form-control" id="department_name" name="department_name" value="<?= htmlspecialchars($department['department_name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($department['description']) ?></textarea>
        </div>
        <button type="submit" name="edit_department" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
    </form>
</div>

<script>
function cancelEdit() {
    if (confirm('Are you sure you want to cancel?')) {
        alert('Changes have been canceled.');
        window.location.href = 'manage_department.php';
    }
}
</script>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
