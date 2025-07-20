<?php
/**
 * Create Bookings Table
 * 
 * This script ensures the bookings table exists with the correct structure.
 */

echo "<h2>🔧 Creating Bookings Table...</h2>";

// Database connection
$host = 'localhost';
$dbname = 'mc_website';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Create bookings table
$sql = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_location VARCHAR(500) NOT NULL,
    guests INT NOT NULL,
    package VARCHAR(100),
    message TEXT,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

try {
    $conn->exec($sql);
    echo "<p style='color: green;'>✅ Bookings table created/verified successfully</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating bookings table: " . $e->getMessage() . "</p>";
}

// Create notifications table
$sql_notifications = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

try {
    $conn->exec($sql_notifications);
    echo "<p style='color: green;'>✅ Notifications table created/verified successfully</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating notifications table: " . $e->getMessage() . "</p>";
}

// Test insert a sample booking
echo "<h3>Testing Booking Insert:</h3>";

$test_booking_ref = 'BK-TEST-' . time();
$test_sql = "INSERT INTO bookings (booking_ref, name, email, phone, event_date, event_time, 
            event_type, event_location, guests, package, message, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

try {
    $stmt = $conn->prepare($test_sql);
    $result = $stmt->execute([
        $test_booking_ref,
        'Test Client',
        'test@example.com',
        '+250123456789',
        date('Y-m-d', strtotime('+7 days')),
        '14:00:00',
        'Wedding',
        'Kigali Convention Centre',
        150,
        'Premium',
        'This is a test booking'
    ]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Test booking inserted successfully</p>";
        echo "<p><strong>Test Booking Reference:</strong> $test_booking_ref</p>";
        
        // Verify the booking was inserted
        $check_sql = "SELECT * FROM bookings WHERE booking_ref = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$test_booking_ref]);
        $booking = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($booking) {
            echo "<p style='color: green;'>✅ Test booking retrieved successfully</p>";
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h4>Retrieved Booking:</h4>";
            echo "<ul>";
            echo "<li><strong>Name:</strong> " . htmlspecialchars($booking['name']) . "</li>";
            echo "<li><strong>Email:</strong> " . htmlspecialchars($booking['email']) . "</li>";
            echo "<li><strong>Event:</strong> " . htmlspecialchars($booking['event_type']) . "</li>";
            echo "<li><strong>Date:</strong> " . htmlspecialchars($booking['event_date']) . "</li>";
            echo "<li><strong>Status:</strong> " . htmlspecialchars($booking['status']) . "</li>";
            echo "</ul>";
            echo "</div>";
        }
        
        // Clean up test booking
        $delete_sql = "DELETE FROM bookings WHERE booking_ref = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->execute([$test_booking_ref]);
        echo "<p style='color: blue;'>🧹 Test booking cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Failed to insert test booking</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Test booking error: " . $e->getMessage() . "</p>";
}

// Show table structure
echo "<h3>Table Structure:</h3>";

try {
    $desc_sql = "DESCRIBE bookings";
    $desc_stmt = $conn->query($desc_sql);
    $columns = $desc_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>Bookings Table Columns:</h4>";
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='padding: 8px; border: 1px solid #ddd;'>Field</th>";
    echo "<th style='padding: 8px; border: 1px solid #ddd;'>Type</th>";
    echo "<th style='padding: 8px; border: 1px solid #ddd;'>Null</th>";
    echo "<th style='padding: 8px; border: 1px solid #ddd;'>Key</th>";
    echo "<th style='padding: 8px; border: 1px solid #ddd;'>Default</th>";
    echo "</tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error getting table structure: " . $e->getMessage() . "</p>";
}

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>✅ Setup Complete!</h4>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li><strong>Test the booking form:</strong> <a href='../booking.html' target='_blank'>booking.html</a></li>";
echo "<li><strong>Check admin panel:</strong> <a href='../admin/bookings.php' target='_blank'>Admin Bookings</a></li>";
echo "<li><strong>Test email notifications:</strong> Submit a real booking</li>";
echo "</ol>";
echo "</div>";

$conn = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Bookings Table</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1000px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        h4 { color: inherit; margin-bottom: 10px; }
        p { line-height: 1.6; }
        ul, ol { line-height: 1.8; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
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
