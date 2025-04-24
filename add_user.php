<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

// Process form submission
if (isset($_POST['add_user'])) {
    // Get form data
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);
    $department = sanitize($_POST['department']);
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    
    // Validate form data
    $errors = [];
    
    if (empty($first_name) || empty($last_name)) {
        $errors[] = "Name fields are required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists
    $check_email = "SELECT COUNT(*) as count FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $errors[] = "Email address already in use";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($role)) {
        $errors[] = "Role is required";
    } elseif (!in_array($role, ['student', 'faculty', 'staff', 'admin'])) {
        $errors[] = "Invalid role selected";
    }
    
    if (empty($department)) {
        $errors[] = "Department/Course is required";
    }
    
    // If no errors, proceed with user creation
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user into database
        $sql = "INSERT INTO users (first_name, last_name, email, password, role, department, phone, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $first_name, $last_name, $email, $hashed_password, $role, $department, $phone);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "User added successfully";
            header("Location: users.php");
            exit();
        } else {
            $_SESSION['error'] = "Error adding user: " . $conn->error;
        }
    } else {
        // Set error messages
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Include the HTML template
include 'add_user.html';
?>