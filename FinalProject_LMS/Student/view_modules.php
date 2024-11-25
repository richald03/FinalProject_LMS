<?php
session_start();
include '../db.php';

// Redirect if not logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Fetch all modules from the database
$sql_modules = "SELECT * FROM modules ORDER BY name ASC"; // Modify as needed
$result_modules = $conn->query($sql_modules);

$modules = [];
if ($result_modules->num_rows > 0) {
    while ($row = $result_modules->fetch_assoc()) {
        $modules[] = $row;
    }
}

// Handle the "viewed" checkbox submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['module_id'])) {
    $module_id = intval($_POST['module_id']);
    // If the checkbox is checked, $viewed will be 1, if unchecked, it will be 0
    $viewed = isset($_POST['viewed']) ? 1 : 0;

    // Update the "viewed" status of the module in the database
    $update_sql = "UPDATE modules SET viewed = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $viewed, $module_id);
    $stmt->execute();
}

// Fetch the logged-in user's details
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT first_name, last_name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result_user = $stmt->get_result();

if ($result_user && $result_user->num_rows > 0) {
    $user_data = $result_user->fetch_assoc();
    $first_name = $user_data['first_name'];
    $last_name = $user_data['last_name'];
} else {
    $first_name = 'Student'; 
    $last_name = '';         
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modules</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .back-to-dashboard {
            position: absolute;
            top: 15px;
            left: 15px;
        }
        .module-card {
            border: 2px solid black;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            position: relative;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .module-file{
            display: flex;             
            flex-direction: column;   
            justify-content: center;  
            align-items: center;       
            text-align: center;        
        }

        .module-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .module-title {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .module-description {
            margin-top: 10px;
            flex-grow: 1;  /* Allow description to take up remaining space */
        }

        .module-file {
            margin-top: 15px;
        }

        /* Position the checkbox at the top right */
        .viewed-checkbox {
            position: absolute;
            top: 15px;
            right: 20px;
            margin: 0;
        }

        .viewed-label {
            font-size: 0.9rem;
        }

        /* Additional Styling for Responsiveness */
        @media (max-width: 768px) {
            .module-card {
                padding: 15px;
            }

            .module-title {
                font-size: 1.2rem;
            }

            .module-description {
                font-size: 1rem;
            }

            .module-file img, .module-file video {
                max-width: 100%;
                height: auto;
            }
        }

        @media (max-width: 576px) {
            .module-title {
                font-size: 1rem;
            }

            .module-description {
                font-size: 0.9rem;
            }

            .module-card {
                padding: 10px;
            }

            .module-file img, .module-file video {
                max-width: 100%;
                height: auto;
            }
        }
    </style>
</head>

<body>

<!-- Back to Dashboard Button -->
<a href="view_courses.php" class="back-to-dashboard">
    <img src="../images/back.png" alt="Back to Dashboard" style="height: 24px; width: 24px;">
</a>

<div class="container mt-4">
    <h2 class="text-center mb-4">Modules</h2>

    <?php if (count($modules) > 0): ?>
        <?php foreach ($modules as $module): ?>
            <div class="module-card">
                <h4 class="module-title"><?= htmlspecialchars($module['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="module-description"><?= htmlspecialchars($module['description'], ENT_QUOTES, 'UTF-8') ?></p>

                <!-- Check if a file exists and display it based on MIME type -->
                <?php if (!empty($module['file'])): ?>
                    <div class="module-file">
                        <?php
                        $file_url = "../Teacher/uploads/modules/" . htmlspecialchars($module['file'], ENT_QUOTES, 'UTF-8');
                        $file_info = pathinfo($file_url);
                        $file_extension = strtolower($file_info['extension']);
                        
                        // Display based on file extension
                        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                            // Image files
                            echo '<img src="' . $file_url . '" alt="Module File" class="img-fluid" />';
                        } elseif ($file_extension == 'pdf') {
                            // PDF files
                            echo '<embed src="' . $file_url . '" type="application/pdf" width="100%" height="400">';
                        } elseif (in_array($file_extension, ['doc', 'docx'])) {
                            // Word files
                            echo '<iframe src="https://docs.google.com/gview?url=' . urlencode($file_url) . '&embedded=true" style="width: 100%; height: 400px;" frameborder="0"></iframe>';
                        } elseif (in_array($file_extension, ['xls', 'xlsx'])) {
                            // Excel files
                            echo '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode($file_url) . '" width="100%" height="400" frameborder="0"></iframe>';
                        } elseif (in_array($file_extension, ['mp4', 'avi', 'mov', 'mkv'])) {
                            // Video files
                            echo '<video width="100%" height="auto" controls>
                                    <source src="' . $file_url . '" type="video/' . $file_extension . '">
                                    Your browser does not support the video tag.
                                </video>';
                        } else {
                            // Provide download link for other file types
                            echo '<a href="' . $file_url . '" target="_blank" class="btn btn-primary">Download File</a>';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Viewed Checkbox -->
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                    <div class="viewed-checkbox">
                        <input type="checkbox" id="viewed_<?= $module['id'] ?>" class="form-check-input"
                            name="viewed" value="1" <?= $module['viewed'] == 1 ? 'checked' : ''; ?>
                            onchange="this.form.submit();">
                        <label for="viewed_<?= $module['id'] ?>" class="viewed-label">Viewed</label>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">No modules found.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>