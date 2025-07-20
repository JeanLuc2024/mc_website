<?php
/**
 * Simple Booking Test
 * 
 * This script tests the booking system with a simple form submission.
 */

// Simulate POST data
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

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<h2>🧪 Testing Booking Submission...</h2>";
echo "<p><strong>Test Data:</strong></p>";
echo "<ul>";
foreach ($_POST as $key => $value) {
    echo "<li><strong>{$key}:</strong> {$value}</li>";
}
echo "</ul>";

echo "<h3>Booking Result:</h3>";

// Capture the output from booking.php
ob_start();
include 'booking.php';
$result = ob_get_clean();

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>Server Response:</h4>";
echo "<pre>" . htmlspecialchars($result) . "</pre>";
echo "</div>";

// Try to decode JSON response
$json_result = json_decode($result, true);

if ($json_result) {
    echo "<div style='background: " . ($json_result['success'] ? '#d4edda' : '#f8d7da') . "; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>" . ($json_result['success'] ? '✅ Success!' : '❌ Error') . "</h4>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($json_result['message']) . "</p>";
    
    if (isset($json_result['booking_ref'])) {
        echo "<p><strong>Booking Reference:</strong> " . htmlspecialchars($json_result['booking_ref']) . "</p>";
    }
    
    if (isset($json_result['debug'])) {
        echo "<p><strong>Debug Info:</strong></p>";
        echo "<ul>";
        foreach ($json_result['debug'] as $debug_item) {
            echo "<li>" . htmlspecialchars($debug_item) . "</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>⚠️ Invalid JSON Response</h4>";
    echo "<p>The server did not return a valid JSON response. This might indicate a PHP error.</p>";
    echo "</div>";
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🔧 Next Steps:</h4>";
echo "<ol>";
echo "<li><strong>If successful:</strong> Test the actual form at <a href='../booking.html' target='_blank'>booking.html</a></li>";
echo "<li><strong>If error:</strong> Check the debug information above</li>";
echo "<li><strong>Check database:</strong> Verify if booking was saved</li>";
echo "<li><strong>Check admin panel:</strong> <a href='../admin/bookings.php' target='_blank'>View bookings</a></li>";
echo "</ol>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Booking Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
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
        pre { background: #f1f1f1; padding: 10px; border-radius: 4px; overflow-x: auto; }
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
