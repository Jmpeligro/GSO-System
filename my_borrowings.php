<?php
session_start();
require_once 'db_connection.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT b.*, e.name as equipment_name, e.equipment_code 
        FROM borrowings b
        JOIN equipment e ON b.equipment_id = e.equipment_id
        WHERE b.user_id = ?
        ORDER BY b.borrow_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (isset($_GET['cancel']) && !empty($_GET['cancel'])) {
    $borrowing_id = (int)$_GET['cancel'];

    $check_sql = "SELECT * FROM borrowings WHERE borrowing_id = ? AND user_id = ? AND approval_status = 'pending'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $borrowing_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $delete_sql = "DELETE FROM borrowings WHERE borrowing_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $borrowing_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Borrowing request cancelled successfully.";
        } else {
            $_SESSION['error'] = "Error cancelling request.";
        }
    } else {
        $_SESSION['error'] = "Invalid request or request already processed.";
    }
    
    header("Location: my_borrowings.php");
    exit();
}

include 'my_borrowings.html';
?>