<?php
session_start();
require_once 'db_connection.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);
    $department = sanitize($_POST['department']);
    $phone = sanitize($_POST['phone']);
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all required fields.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else if ($password != $confirm_password) {
        $error = "Passwords do not match.";
    } else if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        $query = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email address is already registered. Please use a different email.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_query = "INSERT INTO users (first_name, last_name, email, password, role, department, phone) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sssssss", $first_name, $last_name, $email, $hashed_password, $role, $department, $phone);
            
            if ($insert_stmt->execute()) {
                $success = "Registration successful! You can now log in.";
                header("refresh:2;url=login.php");
            } else {
                $error = "Error during registration. Please try again.";
            }
            
            $insert_stmt->close();
        }
        
        $stmt->close();
    }
}

include 'register.html';
?>
