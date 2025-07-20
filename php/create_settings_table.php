<?php
/**
 * Create Settings Table Script
 * 
 * This script creates the settings table in the database if it doesn't exist
 * and populates it with default values.
 */

// Include database configuration
require_once 'config.php';

// Get database connection
$conn = connectDB();

// Check if database connection was successful
if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

try {
    // Read SQL file
    $sql = file_get_contents('settings_table.sql');
    
    // Execute SQL statements
    $conn->exec($sql);
    
    echo "<p>Settings table created successfully!</p>";
    echo "<p><a href='../admin/settings.php'>Go to Settings Page</a></p>";
    
} catch(PDOException $e) {
    die("Error creating settings table: " . $e->getMessage());
}

// Close connection
$conn = null;
?>