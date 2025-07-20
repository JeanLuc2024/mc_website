<?php
/**
 * Get current notifications count and data
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

require_once '../../php/notification_system.php';

$unread_count = getUnreadNotificationsCount();
$recent_notifications = getRecentNotifications(5);

echo json_encode([
    'success' => true,
    'unread_count' => $unread_count,
    'notifications' => $recent_notifications
]);
?>
