<?php
/**
 * Delete Booking from History
 * 
 * This file handles deletion of bookings from the history table
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Include database configuration
require_once '../php/config.php';

// Set content type to JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_ref = trim($_POST['booking_ref'] ?? '');
    
    if (empty($booking_ref)) {
        echo json_encode(['success' => false, 'message' => 'Booking reference is required']);
        exit;
    }
    
    try {
        $conn = connectDB();
        if (!$conn) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit;
        }
        
        // Delete from booking_history
        $stmt = $conn->prepare("DELETE FROM booking_history WHERE booking_ref = ?");
        $result = $stmt->execute([$booking_ref]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Booking deleted from history successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Booking not found in history']);
        }
        
    } catch (PDOException $e) {
        error_log("Delete from history error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
