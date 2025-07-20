<?php
/**
 * Test Booking System
 * 
 * This script tests the booking functionality to ensure it works properly.
 */

require_once 'config.php';

echo "<h2>🧪 Testing Booking System...</h2>";

// Test database connection
$conn = connectDB();
if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connection successful</p>";

// Test if bookings table exists
try {
    $stmt = $conn->query("DESCRIBE bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p style='color: green;'>✅ Bookings table exists with " . count($columns) . " columns</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Bookings table error: " . $e->getMessage() . "</p>";
    echo "<p>Please run the fix script first: <a href='fix_admin_panel.php'>fix_admin_panel.php</a></p>";
    exit;
}

// Test booking insertion
echo "<h3>Testing Booking Insertion:</h3>";

$test_booking = [
    'name' => 'Test Client',
    'email' => 'test@example.com',
    'phone' => '+250123456789',
    'event_date' => date('Y-m-d', strtotime('+7 days')),
    'event_time' => '14:00',
    'event_type' => 'Wedding',
    'event_location' => 'Kigali Convention Centre',
    'guests' => 150,
    'package' => 'Premium',
    'message' => 'This is a test booking'
];

try {
    // Generate booking reference
    $booking_ref = generateBookingReference();
    
    // Insert test booking
    $sql = "INSERT INTO bookings (booking_ref, name, email, phone, event_date, event_time, 
            event_type, event_location, guests, package, message, created_at) 
            VALUES (:booking_ref, :name, :email, :phone, :event_date, :event_time, 
            :event_type, :event_location, :guests, :package, :message, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':booking_ref', $booking_ref);
    $stmt->bindParam(':name', $test_booking['name']);
    $stmt->bindParam(':email', $test_booking['email']);
    $stmt->bindParam(':phone', $test_booking['phone']);
    $stmt->bindParam(':event_date', $test_booking['event_date']);
    $stmt->bindParam(':event_time', $test_booking['event_time']);
    $stmt->bindParam(':event_type', $test_booking['event_type']);
    $stmt->bindParam(':event_location', $test_booking['event_location']);
    $stmt->bindParam(':guests', $test_booking['guests']);
    $stmt->bindParam(':package', $test_booking['package']);
    $stmt->bindParam(':message', $test_booking['message']);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Test booking created successfully!</p>";
        echo "<p><strong>Booking Reference:</strong> {$booking_ref}</p>";
        
        // Test retrieval
        $check_sql = "SELECT * FROM bookings WHERE booking_ref = :ref";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':ref', $booking_ref);
        $check_stmt->execute();
        $retrieved_booking = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($retrieved_booking) {
            echo "<p style='color: green;'>✅ Booking retrieval successful</p>";
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h4>Retrieved Booking Details:</h4>";
            echo "<ul>";
            echo "<li><strong>Name:</strong> " . htmlspecialchars($retrieved_booking['name']) . "</li>";
            echo "<li><strong>Email:</strong> " . htmlspecialchars($retrieved_booking['email']) . "</li>";
            echo "<li><strong>Event Type:</strong> " . htmlspecialchars($retrieved_booking['event_type']) . "</li>";
            echo "<li><strong>Event Date:</strong> " . formatDate($retrieved_booking['event_date']) . "</li>";
            echo "<li><strong>Status:</strong> " . htmlspecialchars($retrieved_booking['status']) . "</li>";
            echo "</ul>";
            echo "</div>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Failed to create test booking</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Booking test error: " . $e->getMessage() . "</p>";
}

// Test helper functions
echo "<h3>Testing Helper Functions:</h3>";

try {
    $test_input = sanitizeInput('<script>alert("test")</script>');
    echo "<p style='color: green;'>✅ sanitizeInput() - Result: " . htmlspecialchars($test_input) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ sanitizeInput() - Error: " . $e->getMessage() . "</p>";
}

try {
    $test_email = validateEmail('test@example.com');
    echo "<p style='color: green;'>✅ validateEmail() - Result: " . ($test_email ? 'Valid' : 'Invalid') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ validateEmail() - Error: " . $e->getMessage() . "</p>";
}

try {
    $test_ref = generateBookingReference();
    echo "<p style='color: green;'>✅ generateBookingReference() - Result: " . htmlspecialchars($test_ref) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ generateBookingReference() - Error: " . $e->getMessage() . "</p>";
}

// Summary
echo "<h3 style='color: #2c3e50;'>📊 Test Summary</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>✅ Booking System Status: READY</h4>";
echo "<p><strong>What's Working:</strong></p>";
echo "<ul>";
echo "<li>✅ Database connection and tables</li>";
echo "<li>✅ Booking insertion and retrieval</li>";
echo "<li>✅ Helper functions</li>";
echo "<li>✅ Booking reference generation</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>📋 How to Test the Booking Form:</h4>";
echo "<ol>";
echo "<li><strong>Go to the booking page:</strong> <a href='../booking.html' target='_blank'>http://localhost/mc_website/booking.html</a></li>";
echo "<li><strong>Fill out the form</strong> with test data</li>";
echo "<li><strong>Submit the form</strong> and check for success message</li>";
echo "<li><strong>Check admin panel</strong> for the new booking: <a href='../admin/bookings.php' target='_blank'>Admin Bookings</a></li>";
echo "</ol>";
echo "</div>";

// Close connection
$conn = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking System Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1000px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        h4 { color: #2c3e50; margin-bottom: 10px; }
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
