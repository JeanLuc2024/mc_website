<?php
/**
 * Create Booking History Table
 * 
 * This script creates a table to store completed bookings that have been responded to
 */

require_once 'config.php';

// Get database connection
$conn = connectDB();

if (!$conn) {
    die("Database connection failed");
}

try {
    // Create booking_history table
    $sql = "CREATE TABLE IF NOT EXISTS booking_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        original_booking_id INT NOT NULL,
        booking_ref VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        event_date DATE NOT NULL,
        event_time TIME NOT NULL,
        event_type VARCHAR(50) NOT NULL,
        event_location VARCHAR(255) NOT NULL,
        guests INT NOT NULL,
        package VARCHAR(50) NOT NULL,
        message TEXT,
        status VARCHAR(20) DEFAULT 'completed',
        admin_notes TEXT,
        moved_to_history_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        original_created_at TIMESTAMP NULL,
        INDEX idx_booking_ref (booking_ref),
        INDEX idx_email (email),
        INDEX idx_event_date (event_date),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "✅ Booking history table created successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Error creating booking history table: " . $e->getMessage() . "\n";
}

$conn = null;
?>
