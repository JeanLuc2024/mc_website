<?php
/**
 * Database Check Script
 * 
 * This file checks the database connection and tables for the MC website.
 */

// Include database configuration
require_once 'config.php';

echo "Starting database check...\n";

// Get database connection
$conn = connectDB();

// Check if database connection was successful
if ($conn) {
    echo "Database connection successful!\n";
    
    try {
        // Check if bookings table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'bookings'");
        if ($stmt->rowCount() > 0) {
            echo "Bookings table exists!\n";
            
            // Check table structure
            $stmt = $conn->query("DESCRIBE bookings");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "Bookings table has " . count($columns) . " columns.\n";
            
            // Check for any records
            $stmt = $conn->query("SELECT COUNT(*) FROM bookings");
            $count = $stmt->fetchColumn();
            echo "Bookings table has " . $count . " records.\n";
        } else {
            echo "ERROR: Bookings table does not exist!\n";
        }
    } catch(PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "ERROR: Database connection failed!\n";
}

echo "Database check completed.\n";

// Close connection
$conn = null;
?>