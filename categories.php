<?php
session_start();
require_once 'db_connection.php';   

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$search_query = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$sql = "SELECT c.*, COUNT(e.equipment_id) as equipment_count 
        FROM categories c
        LEFT JOIN equipment e ON c.category_id = e.category_id
        WHERE 1=1";

if (!empty($search_query)) {
    $sql .= " AND (c.name LIKE '%$search_query%' OR c.description LIKE '%$search_query%')";
}

$sql .= " GROUP BY c.category_id ORDER BY c.name ASC";

$result = $conn->query($sql);

if (isset($_GET['delete']) && isAdmin()) {
    $delete_id = (int)$_GET['delete'];

    $check_sql = "SELECT COUNT(*) as count FROM equipment WHERE category_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $equipment_count = $check_result->fetch_assoc()['count'];

    if ($equipment_count == 0) {
        $delete_sql = "DELETE FROM categories WHERE category_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $delete_id);

        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Category deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting category.";
        }
    } else {
        $_SESSION['error'] = "Cannot delete category that contains equipment. Please reassign or delete the equipment first.";
    }

    header("Location: categories.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);

    if (empty($name)) {
        $_SESSION['error'] = "Category name is required.";
    } else {
        $check_sql = "SELECT COUNT(*) as count FROM categories WHERE name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $existing_count = $check_result->fetch_assoc()['count'];

        if ($existing_count > 0) {
            $_SESSION['error'] = "A category with this name already exists.";
        } else {
            $insert_sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ss", $name, $description);

            if ($insert_stmt->execute()) {
                $_SESSION['success'] = "Category added successfully.";
                header("Location: categories.php");
                exit();
            } else {
                $_SESSION['error'] = "Error adding category.";
            }
        }
    }
}

include 'categories.html';
?>