<?php
/**
 * Mark all notifications as read
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getNotificationDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    try {
        $sql = "UPDATE admin_notifications SET is_read = TRUE, read_at = NOW() WHERE is_read = FALSE";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        
        echo json_encode(['success' => $success]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
