<?php
/**
 * Clean Failed Submissions
 * 
 * This script removes any incomplete or failed booking submissions
 */

require_once 'config.php';

// Get database connection
$conn = connectDB();

if (!$conn) {
    die("Database connection failed");
}

try {
    // Clear any incomplete bookings (those without proper booking_ref or with empty required fields)
    $cleanup_sql = "DELETE FROM bookings WHERE 
                    booking_ref IS NULL OR booking_ref = '' OR
                    name IS NULL OR name = '' OR
                    email IS NULL OR email = '' OR
                    phone IS NULL OR phone = '' OR
                    event_date IS NULL OR
                    event_time IS NULL OR
                    event_type IS NULL OR event_type = '' OR
                    event_location IS NULL OR event_location = '' OR
                    guests IS NULL OR guests <= 0";
    
    $result = $conn->exec($cleanup_sql);
    echo "✅ Cleaned up {$result} incomplete booking records\n";
    
    // Show remaining bookings
    $count_sql = "SELECT COUNT(*) FROM bookings";
    $count = $conn->query($count_sql)->fetchColumn();
    echo "📊 Remaining valid bookings: {$count}\n";
    
} catch (PDOException $e) {
    echo "❌ Error cleaning database: " . $e->getMessage() . "\n";
}

$conn = null;
?>
