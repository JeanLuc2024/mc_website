<?php
/**
 * Check for new notifications API endpoint
 * 
 * This endpoint checks for new notifications and returns the count.
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Include database configuration
require_once '../../php/config.php';

// Set content type
header('Content-Type: application/json');

try {
    // Get database connection
    $conn = connectDB();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Get unread notifications count
    $sql = "SELECT COUNT(*) as unread FROM notifications WHERE is_read = FALSE";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $unread_count = $stmt->fetch()['unread'];
    
    // Get latest notification timestamp
    $latest_sql = "SELECT MAX(created_at) as latest FROM notifications";
    $latest_stmt = $conn->prepare($latest_sql);
    $latest_stmt->execute();
    $latest_notification = $latest_stmt->fetch()['latest'];
    
    // Check if there are new notifications since last check
    $last_check = isset($_SESSION['last_notification_check']) ? $_SESSION['last_notification_check'] : '1970-01-01 00:00:00';
    $new_notifications = 0;
    
    if ($latest_notification && $latest_notification > $last_check) {
        $new_sql = "SELECT COUNT(*) as new_count FROM notifications WHERE created_at > :last_check";
        $new_stmt = $conn->prepare($new_sql);
        $new_stmt->bindParam(':last_check', $last_check);
        $new_stmt->execute();
        $new_notifications = $new_stmt->fetch()['new_count'];
    }
    
    // Update last check time
    $_SESSION['last_notification_check'] = date('Y-m-d H:i:s');
    
    // Return response
    echo json_encode([
        'success' => true,
        'unread_count' => $unread_count,
        'new_notifications' => $new_notifications,
        'latest_notification' => $latest_notification
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to check notifications'
    ]);
}
?>
