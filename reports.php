<?php
session_start();
require_once 'db_connection.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$report_type = isset($_GET['report_type']) ? sanitize($_GET['report_type']) : 'equipment_status';
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$report_data = [];
$chart_data = [];

switch ($report_type) {
    case 'equipment_status':
        $sql = "SELECT status, COUNT(*) as count FROM equipment GROUP BY status ORDER BY count DESC";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
            $chart_data[] = [
                'label' => ucfirst($row['status']),
                'value' => $row['count'],
                'color' => getColorForStatus($row['status'])
            ];
        }
        break;
    case 'borrowing_activity':
        $sql = "SELECT DATE(borrow_date) as date, COUNT(*) as count 
                FROM borrowings 
                WHERE borrow_date BETWEEN ? AND ? 
                GROUP BY DATE(borrow_date) 
                ORDER BY date ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
            $chart_data[] = [
                'date' => date('M d', strtotime($row['date'])),
                'count' => $row['count']
            ];
        }
        break;
    case 'overdue_items':
        $sql = "SELECT b.borrowing_id, e.name as equipment_name, e.equipment_code, 
                u.first_name, u.last_name, u.email, 
                b.borrow_date, b.due_date, 
                DATEDIFF(NOW(), b.due_date) as days_overdue
                FROM borrowings b
                JOIN equipment e ON b.equipment_id = e.equipment_id
                JOIN users u ON b.user_id = u.user_id
                WHERE b.status = 'active' AND b.due_date < NOW()
                ORDER BY days_overdue DESC";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        break;
    case 'popular_equipment':
        $sql = "SELECT e.equipment_id, e.name, c.name as category_name, 
                COUNT(b.borrowing_id) as borrow_count
                FROM equipment e
                JOIN categories c ON e.category_id = c.category_id
                LEFT JOIN borrowings b ON e.equipment_id = b.equipment_id";
        if ($category_id > 0) {
            $sql .= " WHERE e.category_id = $category_id";
        }
        $sql .= " GROUP BY e.equipment_id
                ORDER BY borrow_count DESC
                LIMIT 20";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
            if (count($chart_data) < 10) {
                $chart_data[] = [
                    'name' => $row['name'],
                    'count' => (int)$row['borrow_count']
                ];
            }
        }
        break;
    case 'user_activity':
        $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.role,
                COUNT(b.borrowing_id) as borrow_count
                FROM users u
                LEFT JOIN borrowings b ON u.user_id = b.user_id
                GROUP BY u.user_id
                ORDER BY borrow_count DESC
                LIMIT 20";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
            if (count($chart_data) < 10) {
                $chart_data[] = [
                    'name' => $row['first_name'] . ' ' . $row['last_name'],
                    'count' => (int)$row['borrow_count']
                ];
            }
        }
        break;
}

function getColorForStatus($status) {
    switch ($status) {
        case 'available': return '#27ae60';
        case 'borrowed': return '#3498db';
        case 'maintenance': return '#f39c12';
        case 'retired': return '#e74c3c';
        default: return '#95a5a6';
    }
}

// Check if this is an AJAX request
if(isset($_GET['ajax']) && $_GET['ajax'] == 'true') {
    header('Content-Type: application/json');
    echo json_encode([
        'report_data' => $report_data,
        'chart_data' => $chart_data
    ]);
    exit(); // Stop execution after sending JSON
} else {
    // For regular page load, include the HTML template
    include 'reports.html';
}
?>