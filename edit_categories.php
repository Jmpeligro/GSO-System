<?php
session_start();
require_once 'db_connection.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Category ID is required.";
    header("Location: categories.php");
    exit();
}

$category_id = (int)$_GET['id'];

$sql = "SELECT * FROM categories WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Category not found.";
    header("Location: categories.php");
    exit();
}

$category = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    
    if (empty($name)) {
        $_SESSION['error'] = "Category name is required.";
    } else {
        $check_sql = "SELECT COUNT(*) as count FROM categories WHERE name = ? AND category_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $name, $category_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $existing_count = $check_result->fetch_assoc()['count'];
        
        if ($existing_count > 0) {
            $_SESSION['error'] = "Another category with this name already exists.";
        } else {
            $update_sql = "UPDATE categories SET name = ?, description = ? WHERE category_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $name, $description, $category_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Category updated successfully.";
                header("Location: categories.php");
                exit();
            } else {
                $_SESSION['error'] = "Error updating category.";
            }
        }
    }
}

$equipment_sql = "SELECT * FROM equipment WHERE category_id = ? ORDER BY name ASC";
$equipment_stmt = $conn->prepare($equipment_sql);
$equipment_stmt->bind_param("i", $category_id);
$equipment_stmt->execute();
$equipment_result = $equipment_stmt->get_result();

include 'edit_categories.html';
?>