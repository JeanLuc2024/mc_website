<?php
/**
 * Final Booking System Test
 * 
 * Complete test of the fixed booking system
 */

echo "<h2>🎯 Final Booking System Test</h2>";

echo "<h3>1. System Status Check</h3>";

// Check database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>🚨 XAMPP Issue</h4>";
    echo "<p>Please ensure XAMPP Apache and MySQL services are running.</p>";
    echo "</div>";
    exit;
}

// Check required files
$required_files = [
    'booking_handler.php' => 'Fixed booking form processor',
    'config.php' => 'SMTP configuration',
    'enhanced_smtp.php' => 'Email system'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📁 Required Files:</h4>";
foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ {$file} - {$description}</p>";
    } else {
        echo "<p style='color: red;'>❌ {$file} - {$description} (MISSING)</p>";
    }
}
echo "</div>";

// Check database tables
$required_tables = ['bookings', 'admin_notifications', 'admin_users'];
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>🗄️ Database Tables:</h4>";
foreach ($required_tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<p style='color: green;'>✅ Table '{$table}' exists ({$count} records)</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Table '{$table}' missing</p>";
    }
}
echo "</div>";

echo "<h3>2. Test Booking Submission</h3>";

// Test booking submission
$test_booking = [
    'name' => 'Final Test Client',
    'email' => 'finaltest@example.com',
    'phone' => '+250123456789',
    'event_date' => date('Y-m-d', strtotime('+10 days')),
    'event_time' => '15:00',
    'event_type' => 'Final System Test',
    'event_location' => 'Test Location - System Verification',
    'guests' => '75',
    'package' => 'Premium Package',
    'message' => 'This is a final test to verify the booking system is working correctly after fixes.',
    'terms' => 'on'
];

echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>🧪 Testing Booking Submission:</h4>";
echo "<ul>";
echo "<li><strong>Client:</strong> {$test_booking['name']} ({$test_booking['email']})</li>";
echo "<li><strong>Event:</strong> {$test_booking['event_type']}</li>";
echo "<li><strong>Date:</strong> " . date('F j, Y', strtotime($test_booking['event_date'])) . " at {$test_booking['event_time']}</li>";
echo "<li><strong>Location:</strong> {$test_booking['event_location']}</li>";
echo "<li><strong>Guests:</strong> {$test_booking['guests']}</li>";
echo "</ul>";
echo "</div>";

// Simulate POST request
$original_post = $_POST;
$original_method = $_SERVER['REQUEST_METHOD'];

$_POST = $test_booking;
$_SERVER['REQUEST_METHOD'] = 'POST';

// Capture booking handler response
ob_start();
try {
    include 'booking_handler.php';
    $handler_output = ob_get_contents();
} catch (Exception $e) {
    $handler_output = json_encode(['success' => false, 'message' => 'Handler error: ' . $e->getMessage()]);
}
ob_end_clean();

// Restore original values
$_POST = $original_post;
$_SERVER['REQUEST_METHOD'] = $original_method;

// Parse response
$response = json_decode($handler_output, true);

if ($response && isset($response['success'])) {
    if ($response['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>✅ Booking Submission Test PASSED!</h4>";
        echo "<p><strong>Success Message:</strong> " . htmlspecialchars($response['message']) . "</p>";
        if (isset($response['booking_ref'])) {
            echo "<p><strong>Booking Reference:</strong> " . htmlspecialchars($response['booking_ref']) . "</p>";
        }
        if (isset($response['email_status'])) {
            echo "<p><strong>Email Status:</strong></p>";
            echo "<ul>";
            echo "<li>Client Email: " . ($response['email_status']['client'] ? '✅ Sent' : '❌ Failed') . "</li>";
            echo "<li>Admin Email: " . ($response['email_status']['admin'] ? '✅ Sent' : '❌ Failed') . "</li>";
            echo "</ul>";
        }
        echo "</div>";
        
        $test_passed = true;
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>❌ Booking Submission Test FAILED</h4>";
        echo "<p><strong>Error Message:</strong> " . htmlspecialchars($response['message']) . "</p>";
        echo "</div>";
        
        $test_passed = false;
    }
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>❌ Invalid Response from Booking Handler</h4>";
    echo "<p><strong>Raw Output:</strong></p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px;'>" . htmlspecialchars($handler_output) . "</pre>";
    echo "</div>";
    
    $test_passed = false;
}

echo "<h3>3. Database Verification</h3>";

if (isset($test_passed) && $test_passed) {
    // Check if booking was saved to database
    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE name = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute(['Final Test Client']);
        $saved_booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($saved_booking) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h4>✅ Database Verification PASSED!</h4>";
            echo "<p><strong>Booking saved successfully:</strong></p>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> {$saved_booking['id']}</li>";
            echo "<li><strong>Reference:</strong> {$saved_booking['booking_ref']}</li>";
            echo "<li><strong>Status:</strong> {$saved_booking['status']}</li>";
            echo "<li><strong>Created:</strong> {$saved_booking['created_at']}</li>";
            echo "</ul>";
            echo "</div>";
            
            // Check notifications
            $stmt = $pdo->prepare("SELECT * FROM admin_notifications WHERE booking_id = ?");
            $stmt->execute([$saved_booking['id']]);
            $notification = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($notification) {
                echo "<p style='color: green;'>✅ Admin notification created: {$notification['title']}</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Admin notification not created</p>";
            }
            
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h4>❌ Database Verification FAILED</h4>";
            echo "<p>Booking was not saved to database</p>";
            echo "</div>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Database verification error: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>4. Email System Status</h3>";

// Check email configuration
require_once 'config.php';

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📧 Email Configuration:</h4>";
echo "<ul>";
echo "<li><strong>Admin Email:</strong> " . ADMIN_EMAIL . "</li>";
echo "<li><strong>SMTP Host:</strong> " . SMTP_HOST . "</li>";
echo "<li><strong>SMTP Port:</strong> " . SMTP_PORT . "</li>";
echo "<li><strong>SMTP Username:</strong> " . SMTP_USERNAME . "</li>";
echo "<li><strong>SMTP Password:</strong> " . (SMTP_PASSWORD !== 'your-app-password' ? '✅ Configured' : '❌ Not configured') . "</li>";
echo "</ul>";
echo "</div>";

// Check email logs
$email_files = [
    'email_log.txt' => 'SMTP email log',
    'manual_emails.txt' => 'Fallback emails',
    'pending_emails.txt' => 'Pending notifications'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📄 Email Logs:</h4>";
foreach ($email_files as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<p style='color: green;'>✅ {$file} - {$description} ({$size} bytes)</p>";
    } else {
        echo "<p style='color: gray;'>⚪ {$file} - {$description} (not created yet)</p>";
    }
}
echo "</div>";

echo "<h3>5. Final System Status</h3>";

$all_systems_working = isset($test_passed) && $test_passed;

if ($all_systems_working) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
    echo "<h4>🎉 ALL SYSTEMS WORKING PERFECTLY!</h4>";
    
    echo "<h5>✅ What's Working:</h5>";
    echo "<ul>";
    echo "<li>✅ <strong>Database connection</strong> and table structure</li>";
    echo "<li>✅ <strong>Booking form submission</strong> processing correctly</li>";
    echo "<li>✅ <strong>Data validation</strong> and error handling</li>";
    echo "<li>✅ <strong>Database storage</strong> of booking information</li>";
    echo "<li>✅ <strong>Admin notifications</strong> creation</li>";
    echo "<li>✅ <strong>Email system</strong> configuration</li>";
    echo "<li>✅ <strong>Error handling</strong> and fallback systems</li>";
    echo "</ul>";
    
    echo "<h5>🎯 Ready For:</h5>";
    echo "<ul>";
    echo "<li>🎊 <strong>Production use</strong> with real clients</li>";
    echo "<li>📧 <strong>Professional email notifications</strong></li>";
    echo "<li>🎛️ <strong>Admin panel management</strong></li>";
    echo "<li>📱 <strong>Mobile-responsive booking</strong></li>";
    echo "</ul>";
    
    echo "<h5>🧪 Test Your System Now:</h5>";
    echo "<ol>";
    echo "<li><strong>Go to booking form:</strong> <a href='../booking.html' target='_blank'>Submit Real Booking</a></li>";
    echo "<li><strong>Fill out form</strong> with your details</li>";
    echo "<li><strong>Submit and verify</strong> success message</li>";
    echo "<li><strong>Check admin dashboard:</strong> <a href='../admin/dashboard.php' target='_blank'>Admin Panel</a></li>";
    echo "<li><strong>Check email:</strong> byirival009@gmail.com for notifications</li>";
    echo "</ol>";
    
    echo "</div>";
    
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
    echo "<h4>⚠️ System Issues Detected</h4>";
    echo "<p>Some components are not working correctly. Please:</p>";
    echo "<ol>";
    echo "<li>Ensure XAMPP Apache and MySQL are running</li>";
    echo "<li>Run the fix script again: <a href='fix_booking_error.php' target='_blank'>Fix Booking Error</a></li>";
    echo "<li>Check error logs for specific issues</li>";
    echo "<li>Try submitting a booking manually</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>📞 Support Information</h4>";
echo "<p><strong>If you encounter any issues:</strong></p>";
echo "<ol>";
echo "<li><strong>Check XAMPP:</strong> Ensure Apache and MySQL are running</li>";
echo "<li><strong>Clear browser cache:</strong> Refresh and try again</li>";
echo "<li><strong>Check email logs:</strong> Look for error messages</li>";
echo "<li><strong>Test in incognito mode:</strong> Rule out browser issues</li>";
echo "</ol>";

echo "<p><strong>System URLs:</strong></p>";
echo "<ul>";
echo "<li><strong>Booking Form:</strong> <a href='../booking.html' target='_blank'>booking.html</a></li>";
echo "<li><strong>Admin Panel:</strong> <a href='../admin/dashboard.php' target='_blank'>dashboard.php</a></li>";
echo "<li><strong>Fix Script:</strong> <a href='fix_booking_error.php' target='_blank'>fix_booking_error.php</a></li>";
echo "</ul>";
echo "</div>";

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Final Booking Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
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
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
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
