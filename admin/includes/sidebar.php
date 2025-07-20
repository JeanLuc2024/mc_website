<?php
/**
 * Admin Sidebar Component
 * 
 * This file contains the sidebar navigation component for the admin panel.
 * It should be included in all admin pages for consistent navigation.
 */

// Check if user is logged in (security check)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page
    header('Location: index.php');
    exit;
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Get unread counts if not already set
if (!isset($pending_bookings)) {
    // Include database configuration if not already included
    if (!function_exists('connectDB')) {
        require_once '../php/config.php';
    }

    // Get database connection
    $conn = connectDB();

    // Initialize variables
    $pending_bookings = 0;

    // Check if database connection was successful
    if ($conn) {
        try {
            // Get pending bookings count
            $stmt = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
            $pending_bookings = $stmt->fetchColumn();

        } catch(PDOException $e) {
            error_log("Sidebar error: " . $e->getMessage());
        }
    }
}
?>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>ValentinMC</h2>
        <p>Admin Panel</p>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo ($current_page == 'bookings.php') ? 'active' : ''; ?>">
                <a href="bookings.php">
                    <i class="fas fa-calendar-check"></i>
                    <span>Client Bookings</span>
                    <?php if ($pending_bookings > 0): ?>
                        <span class="badge"><?php echo $pending_bookings; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'email-client.php') ? 'active' : ''; ?>">
                <a href="email-client.php">
                    <i class="fas fa-envelope"></i>
                    <span>Email Clients</span>
                </a>
            </li>
            <!-- Content Management tab removed -->
            <li class="<?php echo ($current_page == 'view_history.php') ? 'active' : ''; ?>">
                <a href="view_history.php">
                    <i class="fas fa-history"></i>
                    <span>Email History</span>
                </a>
            </li>
            <!-- Settings tab removed -->
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <p>&copy; 2025 Byiringiro Valentin</p>
    </div>
</aside>