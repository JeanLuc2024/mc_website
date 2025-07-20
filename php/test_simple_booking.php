<?php
/**
 * Test Simple Booking System
 * 
 * This script tests the simple booking handler directly.
 */

echo "<h2>🧪 Testing Simple Booking System...</h2>";

// Test 1: Check if the booking file exists
echo "<h3>1. File Existence Check:</h3>";
if (file_exists('booking_simple.php')) {
    echo "<p style='color: green;'>✅ booking_simple.php exists</p>";
} else {
    echo "<p style='color: red;'>❌ booking_simple.php not found</p>";
    exit;
}

// Test 2: Database connection
echo "<h3>2. Database Connection Test:</h3>";
try {
    $conn = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8", 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Solution:</strong> Ensure XAMPP MySQL is running and database 'mc_website' exists</p>";
    exit;
}

// Test 3: Simulate POST request
echo "<h3>3. Simulating Booking Submission:</h3>";

// Backup original POST and SERVER data
$original_post = $_POST;
$original_server = $_SERVER;

// Set up test data
$_POST = [
    'name' => 'Test Client',
    'email' => 'test@example.com',
    'phone' => '+250123456789',
    'event_date' => date('Y-m-d', strtotime('+7 days')),
    'event_time' => '14:00',
    'event_type' => 'Wedding',
    'event_location' => 'Kigali Convention Centre',
    'guests' => '150',
    'package' => 'Premium',
    'message' => 'This is a test booking',
    'terms' => 'on'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<p><strong>Test Data:</strong></p>";
echo "<ul>";
foreach ($_POST as $key => $value) {
    echo "<li><strong>{$key}:</strong> " . htmlspecialchars($value) . "</li>";
}
echo "</ul>";

// Capture output from booking script
echo "<h4>Booking Script Response:</h4>";
ob_start();
include 'booking_simple.php';
$response = ob_get_clean();

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; border: 1px solid #dee2e6;'>";
echo "<h5>Raw Response:</h5>";
echo "<pre style='background: #ffffff; padding: 10px; border-radius: 4px; overflow-x: auto;'>" . htmlspecialchars($response) . "</pre>";
echo "</div>";

// Try to decode JSON
$json_data = json_decode($response, true);

if ($json_data) {
    echo "<div style='background: " . ($json_data['success'] ? '#d4edda' : '#f8d7da') . "; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid " . ($json_data['success'] ? '#28a745' : '#dc3545') . ";'>";
    echo "<h4>" . ($json_data['success'] ? '✅ SUCCESS!' : '❌ ERROR') . "</h4>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($json_data['message']) . "</p>";
    
    if (isset($json_data['booking_ref'])) {
        echo "<p><strong>Booking Reference:</strong> " . htmlspecialchars($json_data['booking_ref']) . "</p>";
    }
    echo "</div>";
    
    // If successful, check if booking was saved to database
    if ($json_data['success'] && isset($json_data['booking_ref'])) {
        echo "<h4>Database Verification:</h4>";
        try {
            $check_sql = "SELECT * FROM bookings WHERE booking_ref = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([$json_data['booking_ref']]);
            $booking = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($booking) {
                echo "<p style='color: green;'>✅ Booking found in database</p>";
                echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
                echo "<h5>Database Record:</h5>";
                echo "<ul>";
                echo "<li><strong>ID:</strong> " . htmlspecialchars($booking['id']) . "</li>";
                echo "<li><strong>Name:</strong> " . htmlspecialchars($booking['name']) . "</li>";
                echo "<li><strong>Email:</strong> " . htmlspecialchars($booking['email']) . "</li>";
                echo "<li><strong>Event:</strong> " . htmlspecialchars($booking['event_type']) . "</li>";
                echo "<li><strong>Date:</strong> " . htmlspecialchars($booking['event_date']) . "</li>";
                echo "<li><strong>Status:</strong> " . htmlspecialchars($booking['status']) . "</li>";
                echo "<li><strong>Created:</strong> " . htmlspecialchars($booking['created_at']) . "</li>";
                echo "</ul>";
                echo "</div>";
                
                // Clean up test booking
                $delete_sql = "DELETE FROM bookings WHERE booking_ref = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->execute([$json_data['booking_ref']]);
                echo "<p style='color: blue;'>🧹 Test booking cleaned up from database</p>";
            } else {
                echo "<p style='color: red;'>❌ Booking not found in database</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Database check error: " . $e->getMessage() . "</p>";
        }
    }
    
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ffc107;'>";
    echo "<h4>⚠️ Invalid JSON Response</h4>";
    echo "<p>The booking script did not return valid JSON. This indicates a PHP error.</p>";
    echo "<p><strong>Common causes:</strong></p>";
    echo "<ul>";
    echo "<li>PHP syntax errors</li>";
    echo "<li>Database connection issues</li>";
    echo "<li>Missing required extensions</li>";
    echo "<li>Output before JSON (HTML/text)</li>";
    echo "</ul>";
    echo "</div>";
}

// Restore original data
$_POST = $original_post;
$_SERVER = $original_server;

// Test 4: Check table structure
echo "<h3>4. Database Table Check:</h3>";
try {
    $tables_sql = "SHOW TABLES LIKE 'bookings'";
    $tables_result = $conn->query($tables_sql);
    
    if ($tables_result->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Bookings table exists</p>";
        
        // Show table structure
        $desc_sql = "DESCRIBE bookings";
        $desc_result = $conn->query($desc_sql);
        $columns = $desc_result->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>Table Structure:</h5>";
        echo "<table style='width: 100%; border-collapse: collapse; font-size: 14px;'>";
        echo "<tr style='background: #e9ecef;'>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Field</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Type</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Null</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Key</th>";
        echo "</tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Key']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>⚠️ Bookings table does not exist (will be created automatically)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Table check error: " . $e->getMessage() . "</p>";
}

// Summary and next steps
echo "<h3>📋 Summary & Next Steps:</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🔧 How to Test the Booking Form:</h4>";
echo "<ol>";
echo "<li><strong>Open the booking form:</strong> <a href='../booking.html' target='_blank'>http://localhost/mc_website/booking.html</a></li>";
echo "<li><strong>Fill out all required fields</strong> with test data</li>";
echo "<li><strong>Submit the form</strong> and watch for success/error messages</li>";
echo "<li><strong>Check your email</strong> (izabayojeanlucseverin@gmail.com) for notifications</li>";
echo "<li><strong>Verify in admin panel:</strong> <a href='../admin/bookings.php' target='_blank'>Admin Bookings</a></li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h4>⚠️ If Issues Persist:</h4>";
echo "<ul>";
echo "<li><strong>Check XAMPP:</strong> Ensure Apache and MySQL services are running</li>";
echo "<li><strong>Check database:</strong> Verify 'mc_website' database exists in phpMyAdmin</li>";
echo "<li><strong>Check PHP errors:</strong> Look in XAMPP/logs/php_error_log</li>";
echo "<li><strong>Check Apache errors:</strong> Look in XAMPP/logs/error.log</li>";
echo "<li><strong>Browser console:</strong> Check for JavaScript errors (F12)</li>";
echo "</ul>";
echo "</div>";

$conn = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Booking Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1000px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        h4, h5 { color: inherit; margin-bottom: 10px; }
        p { line-height: 1.6; }
        ul, ol { line-height: 1.8; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        pre { font-size: 12px; max-height: 200px; overflow-y: auto; }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Content will be inserted here by PHP -->
    </div>
</body>
</html>
