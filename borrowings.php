<?php
session_start();
require_once 'db_connection.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$approval_filter = isset($_GET['approval_status']) ? sanitize($_GET['approval_status']) : '';
$user_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$equipment_filter = isset($_GET['equipment_id']) ? (int)$_GET['equipment_id'] : 0;
$search_query = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';

$sql = "SELECT b.*, e.name as equipment_name, e.equipment_code, 
         u.first_name, u.last_name, u.email
         FROM borrowings b
         JOIN equipment e ON b.equipment_id = e.equipment_id
         JOIN users u ON b.user_id = u.user_id
         WHERE 1=1";

if (!empty($status_filter)) {
    $sql .= " AND b.status = '$status_filter'";
}

if (!empty($approval_filter)) {
    $sql .= " AND b.approval_status = '$approval_filter'";
}

if ($user_filter > 0) {
    $sql .= " AND b.user_id = $user_filter";
}

if ($equipment_filter > 0) {
    $sql .= " AND b.equipment_id = $equipment_filter";
}

if (!empty($date_from)) {
    $sql .= " AND b.borrow_date >= '$date_from'";
}

if (!empty($date_to)) {
    $sql .= " AND b.borrow_date <= '$date_to 23:59:59'";
}

if (!empty($search_query)) {
    $sql .= " AND (e.name LIKE '%$search_query%' OR e.equipment_code LIKE '%$search_query%' OR 
              u.first_name LIKE '%$search_query%' OR u.last_name LIKE '%$search_query%' OR 
              u.email LIKE '%$search_query%')";
}

$sql .= " ORDER BY b.borrow_date DESC";

$result = $conn->query($sql);

$users_sql = "SELECT user_id, first_name, last_name FROM users ORDER BY last_name, first_name";
$users_result = $conn->query($users_sql);

$equipment_sql = "SELECT equipment_id, name FROM equipment ORDER BY name";
$equipment_result = $conn->query($equipment_sql);

if (isset($_GET['return']) && isLoggedIn()) {
    $borrowing_id = (int)$_GET['return'];

    $conn->begin_transaction();

    try {
        $get_equipment_sql = "SELECT equipment_id FROM borrowings WHERE borrowing_id = ?";
        $get_stmt = $conn->prepare($get_equipment_sql);
        $get_stmt->bind_param("i", $borrowing_id);
        $get_stmt->execute();
        $equipment_result = $get_stmt->get_result();

        if ($equipment_row = $equipment_result->fetch_assoc()) {
            $equipment_id = $equipment_row['equipment_id'];

            $update_borrowing_sql = "UPDATE borrowings SET 
                status = 'returned', 
                return_date = NOW(), 
                return_notes = 'Returned via system', 
                returned_by = ?
                WHERE borrowing_id = ?";

            $update_stmt = $conn->prepare($update_borrowing_sql);
            $update_stmt->bind_param("ii", $_SESSION['user_id'], $borrowing_id);
            $update_stmt->execute();

            $update_equipment_sql = "UPDATE equipment SET status = 'available' WHERE equipment_id = ?";
            $update_equipment_stmt = $conn->prepare($update_equipment_sql);
            $update_equipment_stmt->bind_param("i", $equipment_id);
            $update_equipment_stmt->execute();

            $conn->commit();
            $_SESSION['success'] = "Equipment returned successfully.";
        } else {
            throw new Exception("Borrowing record not found.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error returning equipment: " . $e->getMessage();
    }

    header("Location: borrowings.php");
    exit();
}

include 'borrowings.html';
?>