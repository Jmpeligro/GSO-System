<?php
session_start();
require_once 'db_connection.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$sql_equipment = "SELECT equipment_id, name, equipment_code FROM equipment 
                 WHERE status = 'available'
                 ORDER BY name";
$result_equipment = $conn->query($sql_equipment);

$sql_users = "SELECT user_id, first_name, last_name, email FROM users ORDER BY last_name, first_name";
$result_users = $conn->query($sql_users);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $equipment_id = (int)$_POST['equipment_id'];
    $user_id = (int)$_POST['user_id'];
    $borrow_date = sanitize($_POST['borrow_date']);
    $due_date = sanitize($_POST['due_date']);
    $notes = sanitize($_POST['notes']);

    $errors = [];
    
    if ($equipment_id <= 0) {
        $errors[] = "Please select valid equipment.";
    }
    
    if ($user_id <= 0) {
        $errors[] = "Please select a valid user.";
    }
    
    $borrow_datetime = new DateTime($borrow_date);
    $due_datetime = new DateTime($due_date);
    
    if ($due_datetime <= $borrow_datetime) {
        $errors[] = "Due date must be after the borrow date.";
    }

    $check_equipment = "SELECT status FROM equipment WHERE equipment_id = ?";
    $stmt = $conn->prepare($check_equipment);
    $stmt->bind_param("i", $equipment_id);
    $stmt->execute();
    $equipment_result = $stmt->get_result();
    
    if ($equipment_result->num_rows == 0) {
        $errors[] = "Equipment not found.";
    } else {
        $equipment_status = $equipment_result->fetch_assoc()['status'];
        if ($equipment_status !== 'available') {
            $errors[] = "Selected equipment is not available for borrowing.";
        }
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            $insert_borrowing = "INSERT INTO borrowings 
                                (equipment_id, user_id, borrow_date, due_date, status, notes, 
                                 approval_status, admin_issued_id)
                                VALUES (?, ?, ?, ?, 'active', ?, 'approved', ?)";
            $stmt = $conn->prepare($insert_borrowing);
            $stmt->bind_param("iisssi", $equipment_id, $user_id, $borrow_date, $due_date, $notes, $_SESSION['user_id']);
            $stmt->execute();
            
            $update_equipment = "UPDATE equipment SET status = 'borrowed', updated_at = NOW() 
                                WHERE equipment_id = ?";
            $stmt = $conn->prepare($update_equipment);
            $stmt->bind_param("i", $equipment_id);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['success'] = "Equipment borrowed successfully.";
            header("Location: borrowings.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Error processing borrowing: " . $e->getMessage();
        }
    }
}

include 'new_borrowing.html';
?>