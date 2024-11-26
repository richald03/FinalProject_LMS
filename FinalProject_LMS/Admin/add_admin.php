<?php
include '../db.php';  // Include your database connection

$adminPassword = "admin_test1"; // Replace with your desired admin password
$hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (user_type, first_name, last_name, email, password, gender, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$userType = 'admin';
$firstName = '';
$lastName = '';
$email = 'mainadmin@gmail.com';  // Replace with your admin's email
$gender = 'other';
$stmt->bind_param("ssssss", $userType, $firstName, $lastName, $email, $hashedPassword, $gender);
$stmt->execute();
$stmt->close();

echo "Admin user added successfully!";
$conn->close();
?>
