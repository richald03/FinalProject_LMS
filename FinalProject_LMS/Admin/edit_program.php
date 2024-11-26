<?php
session_start();

// Redirect to login if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: /FinalProject_LMS/login.php");
    exit();
}

// Include the database connection file
require '../db.php';

// Fetch departments for the dropdown
$departments = [];
$dept_stmt = $conn->prepare("SELECT id, department_name FROM departments");
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();
while ($dept_row = $dept_result->fetch_assoc()) {
    $departments[] = $dept_row;
}
$dept_stmt->close();

// Handle form submission for editing a program
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_program'])) {
    $id = $_POST['id'];
    $name = $_POST['program_name'];
    $description = $_POST['description'];
    $department_id = $_POST['department_id'];

    $stmt = $conn->prepare("UPDATE programs SET program_name = ?, program_description = ?, department_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $name, $description, $department_id, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Changes saved successfully.'); window.location.href='manage_program.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch program details for editing
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $program = $result->fetch_assoc();

    if (!$program) {
        echo "Program not found.";
        exit();
    }

    $stmt->close();
} else {
    echo "No program ID provided.";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Program</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Edit Program</h2>
    <form method="POST" action="edit_program.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($program['id']) ?>">
        <div class="form-group">
            <label for="program_name">Program Name</label>
            <input type="text" class="form-control" id="program_name" name="program_name" value="<?= htmlspecialchars($program['program_name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($program['program_description']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="department_id">Department</label>
            <select class="form-control" id="department_id" name="department_id" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= $department['id'] ?>" <?= $department['id'] == $program['department_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($department['department_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="edit_program" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
    </form>
</div>

<script>
function cancelEdit() {
    if (confirm('Are you sure you want to cancel?')) {
        alert('Changes have been canceled.');
        window.location.href = 'manage_program.php';
    }
}
</script>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
