<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check user credentials
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];

            // Redirect based on the user role
            if ($user['user_type'] === 'admin') {
                header('Location: admin_dashboard.php');
            } elseif ($user['user_type'] === 'teacher') {
                header('Location: ../FinalProject_LMS/Teacher/teacher_dashboard.php');
            } elseif ($user['user_type'] === 'student') {
                header('Location: student_dashboard.php');
            }
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "No user found with this email!";
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url(images/bg.jpg);
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.85);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1); 
            border: 1px solid black; 
        }

        .logo img {
            width: 150px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        h2 {
            font-size: 1.5rem;
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-control {
            border-radius: 8px; 
            margin-bottom: 20px;
            padding: 12px 15px;
            border: 1px solid black; /
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); 
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: #2575fc; 
            box-shadow: 0 0 10px rgba(37, 117, 252, 0.4); 
        }

        .btn-primary {
            background-color: #2575fc;
            border-radius: 8px; 
            padding: 12px 20px;
            font-size: 1.1rem;
            width: 100%;
            color: white;
            border: 1px solid black;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); 
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #1a61c1;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3); 
        }

        .alert-danger {
            background-color: rgba(255, 0, 0, 0.2);
            color: red;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }

        .form-check-label {
            font-size: 0.9rem;
        }

        .position-relative {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 71%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #555;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo">
        <img src="images/logo.png" alt="LMS Logo"> <!-- Replace with your actual logo -->
    </div>

    <h2>Login to LMS</h2>

    <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="Enter email" required>
        </div>
        <div class="mb-3 position-relative">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" id="password" required>
            <i class="fa fa-eye-slash toggle-password" id="togglePassword"></i>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');

    togglePassword.addEventListener('click', function() {
        // Toggle the type attribute
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);

        // Toggle the icon class
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>

<!-- FontAwesome for eye icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</body>
</html>