<?php
session_start();
require_once 'db_connection.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: equipment.php");
    exit();
}

$equipment_id = (int)$_GET['id'];

$sql = "SELECT e.*, c.name as category_name FROM equipment e
        JOIN categories c ON e.category_id = c.category_id
        WHERE e.equipment_id = ? AND e.status = 'available'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Equipment not found or not available for borrowing.";
    header("Location: equipment.php");
    exit();
}

$equipment = $result->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $borrow_date = sanitize($_POST['borrow_date']);
    $due_date = sanitize($_POST['due_date']);
    $purpose = sanitize($_POST['purpose']);

    $borrow_datetime = new DateTime($borrow_date);
    $due_datetime = new DateTime($due_date);
    $now = new DateTime();

    if ($borrow_datetime > $due_datetime) {
        $error = "Due date must be after the borrow date.";
    } else {
        $user_id = $_SESSION['user_id'];

        $sql = "INSERT INTO borrowings (equipment_id, user_id, borrow_date, due_date, status, notes, approval_status)
                VALUES (?, ?, ?, ?, 'pending', ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisss", $equipment_id, $user_id, $borrow_date, $due_date, $purpose);

        if ($stmt->execute()) {
            $success = "Equipment borrow request submitted successfully. An administrator will review your request shortly.";

            header("refresh:2;url=my_borrowings.php");
        } else {
            $error = "Error processing your request. Please try again.";
        }
    }
}

include 'borrow_equipment.html';
?>