<?php
session_start();
require_once 'db_connection.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$sql_available = "SELECT COUNT(*) as count FROM equipment WHERE status = 'available'";
$sql_borrowed = "SELECT COUNT(*) as count FROM equipment WHERE status = 'borrowed'";
$sql_maintenance = "SELECT COUNT(*) as count FROM equipment WHERE status = 'maintenance'";
$sql_categories = "SELECT COUNT(*) as count FROM categories";

$result_available = $conn->query($sql_available);
$result_borrowed = $conn->query($sql_borrowed);
$result_maintenance = $conn->query($sql_maintenance);
$result_categories = $conn->query($sql_categories);

$available_count = $result_available->fetch_assoc()['count'];
$borrowed_count = $result_borrowed->fetch_assoc()['count'];
$maintenance_count = $result_maintenance->fetch_assoc()['count'];
$categories_count = $result_categories->fetch_assoc()['count'];

$sql_recent = "SELECT b.borrowing_id, e.name as equipment_name, u.first_name, u.last_name, 
               b.borrow_date, b.due_date, b.status 
               FROM borrowings b
               JOIN equipment e ON b.equipment_id = e.equipment_id
               JOIN users u ON b.user_id = u.user_id
               ORDER BY b.borrow_date DESC LIMIT 5";
$result_recent = $conn->query($sql_recent);

include 'dashboard.html';
?>

<script>
const ctx = document.getElementById('equipmentStatusChart').getContext('2d');
const equipmentStatusChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Available', 'Borrowed', 'Maintenance'],
        datasets: [{
            data: [<?php echo $available_count; ?>, <?php echo $borrowed_count; ?>, <?php echo $maintenance_count; ?>],
            backgroundColor: ['#4CAF50', '#2196F3', '#FF9800'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>
