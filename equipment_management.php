<?php
session_start();
require_once 'db_connection.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$name = $description = $equipment_code = $acquisition_date = '';
$category_id = $condition_status = $status = '';
$errors = [];
$success_message = '';

$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = $conn->query($sql_categories);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $equipment_code = sanitize($_POST['equipment_code']);
    $category_id = (int)$_POST['category_id'];
    $condition_status = sanitize($_POST['condition_status']);
    $status = sanitize($_POST['status']);
    $acquisition_date = sanitize($_POST['acquisition_date']);

    if (empty($name)) {
        $errors[] = "Equipment name is required";
    }

    if (empty($equipment_code)) {
        $errors[] = "Equipment code is required";
    } else {
        $check_code_sql = "SELECT equipment_id FROM equipment WHERE equipment_code = '$equipment_code'";
        $check_result = $conn->query($check_code_sql);
        if ($check_result->num_rows > 0) {
            $errors[] = "Equipment code already exists. Please use a different code.";
        }
    }

    if ($category_id <= 0) {
        $errors[] = "Please select a category";
    }

    if (empty($condition_status)) {
        $errors[] = "Condition status is required";
    }

    if (empty($status)) {
        $errors[] = "Equipment status is required";
    }

    if (empty($acquisition_date)) {
        $errors[] = "Acquisition date is required";
    } else {
        $date_parts = explode('-', $acquisition_date);
        if (count($date_parts) !== 3 || !checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0])) {
            $errors[] = "Invalid date format. Please use YYYY-MM-DD format.";
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO equipment (name, description, equipment_code, category_id, condition_status, status, acquisition_date) 
                VALUES ('$name', '$description', '$equipment_code', $category_id, '$condition_status', '$status', '$acquisition_date')";

        if ($conn->query($sql) === TRUE) {
            $success_message = "Equipment added successfully!";
            $name = $description = $equipment_code = $acquisition_date = '';
            $category_id = $condition_status = $status = '';
        } else {
            $errors[] = "Error: " . $conn->error;
        }
    }
}

include 'equipment_management.html';
?>
