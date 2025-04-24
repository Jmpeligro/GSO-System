<?php
session_start();
require_once 'db_connection.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['delete_equipment']) && isAdmin()) {
    $equipment_id = (int)$_POST['equipment_id'];
    $check_sql = "SELECT status FROM equipment WHERE equipment_id = $equipment_id";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows > 0) {
        $equipment = $check_result->fetch_assoc();
        if ($equipment['status'] == 'borrowed') {
            $_SESSION['error'] = "Cannot delete equipment that is currently borrowed.";
        } else {
            $delete_sql = "DELETE FROM equipment WHERE equipment_id = $equipment_id";
            if ($conn->query($delete_sql) === TRUE) {
                $_SESSION['success'] = "Equipment deleted successfully!";
            } else {
                $_SESSION['error'] = "Error deleting equipment: " . $conn->error;
            }
        }
    } else {
        $_SESSION['error'] = "Equipment not found.";
    }
    header("Location: equipment.php");
    exit();
}

if (isset($_POST['update_status']) && isAdmin()) {
    $equipment_id = (int)$_POST['equipment_id'];
    $new_status = sanitize($_POST['new_status']);
    if (empty($new_status)) {
        $_SESSION['error'] = "No status selected.";
        header("Location: equipment.php");
        exit();
    }
    $allowed_statuses = ['available', 'maintenance', 'retired'];
    if (in_array($new_status, $allowed_statuses)) {
        $update_sql = "UPDATE equipment SET status = '$new_status' WHERE equipment_id = $equipment_id AND status != 'borrowed'";
        if ($conn->query($update_sql) === TRUE) {
            if ($conn->affected_rows > 0) {
                $_SESSION['success'] = "Equipment status updated to " . ucfirst($new_status);
            } else {
                $_SESSION['error'] = "Unable to update status - equipment may be borrowed.";
            }
        } else {
            $_SESSION['error'] = "Error updating status: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Invalid status selected.";
    }
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search_query = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$sql = "SELECT e.*, c.name as category_name 
        FROM equipment e
        JOIN categories c ON e.category_id = c.category_id
        WHERE 1=1";

if (!empty($status_filter)) {
    $sql .= " AND e.status = '$status_filter'";
}

if ($category_filter > 0) {
    $sql .= " AND e.category_id = $category_filter";
}

if (!empty($search_query)) {
    $search_term = '%' . $search_query . '%';
    $sql .= " AND (e.name LIKE ? OR e.equipment_code LIKE ? OR e.description LIKE ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql .= " ORDER BY e.name ASC";
    $result = $conn->query($sql);
}

$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = $conn->query($sql_categories);

include  'equipment.html';
?>