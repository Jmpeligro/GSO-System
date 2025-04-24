<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db_connection.php';
?>

<header class="header">
    <div class="header-container">
        <div class="logo">
            <a href="index.php">PLP GSO Management</a>
        </div>
        
        <nav class="nav-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="equipment.php">Equipment</a>
            <a href="borrowings.php">Borrowings</a>
            <?php if (isAdmin()): ?>
            <a href="categories.html">Categories</a>
            <a href="users.php">Users</a>
            <a href="reports.php">Reports</a>
            <?php endif; ?>
        </nav>
        
        <div class="user-menu">
            <button class="user-menu-button" id="userMenuButton">
                <span><?php echo $_SESSION['name']; ?></span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <div class="user-menu-dropdown" id="userMenuDropdown">
                <?php if (!isAdmin()): ?>   
                    <a href="my_borrowings.php">My Borrowings</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userMenuButton = document.getElementById('userMenuButton');
    const userMenuDropdown = document.getElementById('userMenuDropdown');
    
    userMenuButton.addEventListener('click', function() {
        userMenuDropdown.classList.toggle('active');
    });
    
    document.addEventListener('click', function(event) {
        if (!userMenuButton.contains(event.target) && !userMenuDropdown.contains(event.target)) {
            userMenuDropdown.classList.remove('active');
        }
    });
});
</script>