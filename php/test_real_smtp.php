<?php
/**
 * Test Real SMTP System
 * 
 * Test the new real SMTP implementation
 */

echo "<h2>🔧 Testing Real SMTP System</h2>";

// Include the real SMTP system
require_once 'real_smtp_email.php';

echo "<h3>1. Configuration Check</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📧 Real SMTP Configuration:</h4>";
echo "<ul>";
echo "<li><strong>Admin Email:</strong> " . ADMIN_EMAIL . "</li>";
echo "<li><strong>SMTP Host:</strong> " . SMTP_HOST . "</li>";
echo "<li><strong>SMTP Port:</strong> " . SMTP_PORT . "</li>";
echo "<li><strong>SMTP Username:</strong> " . SMTP_USERNAME . "</li>";
echo "<li><strong>SMTP Password:</strong> " . (SMTP_PASSWORD !== 'your-app-password' ? '✅ Configured' : '❌ Not configured') . "</li>";
echo "<li><strong>From Email:</strong> " . SMTP_FROM_EMAIL . "</li>";
echo "<li><strong>From Name:</strong> " . SMTP_FROM_NAME . "</li>";
echo "</ul>";
echo "</div>";

echo "<h3>2. Test Real SMTP Email</h3>";

if (isset($_POST['test_real_smtp_email'])) {
    echo "<h4>📤 Sending Real SMTP Email...</h4>";
    
    $test_email = $_POST['test_email'] ?: 'byirival009@gmail.com';
    
    echo "<p><strong>Testing Real SMTP Connection...</strong></p>";
    echo "<p><strong>To:</strong> {$test_email}</p>";
    echo "<p><strong>Method:</strong> Direct SMTP connection to Gmail servers</p>";
    
    $result = testRealSMTPConfiguration($test_email);
    
    if ($result) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>✅ REAL SMTP EMAIL SENT SUCCESSFULLY!</h5>";
        echo "<p><strong>Email sent to:</strong> {$test_email}</p>";
        echo "<p><strong>Method:</strong> Authenticated SMTP connection to Gmail</p>";
        echo "<p><strong>Check your Gmail inbox NOW!</strong></p>";
        echo "<p><strong>Log file:</strong> php/real_smtp_log.txt</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Real SMTP Email Failed</h5>";
        echo "<p>The real SMTP email could not be sent. Check the error logs.</p>";
        echo "</div>";
    }
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>📧 Test Real SMTP Email</h4>";
echo "<form method='POST'>";
echo "<p>This will send an email using REAL SMTP authentication (not PHP's mail() function).</p>";
echo "<p><strong>Email address:</strong></p>";
echo "<input type='email' name='test_email' value='byirival009@gmail.com' style='padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;' required>";
echo "<br><br>";
echo "<button type='submit' name='test_real_smtp_email' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>📧 Send Real SMTP Email</button>";
echo "</form>";
echo "</div>";

echo "<h3>3. Test Booking Notification</h3>";

if (isset($_POST['test_booking_notification'])) {
    echo "<h4>📋 Testing Real Booking Notification...</h4>";
    
    // Create test booking data
    $test_booking = [
        'id' => 999,
        'booking_ref' => 'REAL-TEST-' . date('ymd-His'),
        'name' => 'Real SMTP Test Client',
        'email' => 'test@example.com',
        'phone' => '0788487100',
        'event_date' => date('Y-m-d', strtotime('+7 days')),
        'event_time' => '14:00',
        'event_type' => 'Real SMTP Test Event',
        'event_location' => 'Real SMTP Test Location',
        'guests' => 50,
        'package' => 'Premium Package',
        'message' => 'This is a test using REAL SMTP to verify admin email notifications work.'
    ];
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h5>📊 Test Booking Data:</h5>";
    echo "<ul>";
    echo "<li><strong>Booking Ref:</strong> {$test_booking['booking_ref']}</li>";
    echo "<li><strong>Client:</strong> {$test_booking['name']} ({$test_booking['email']})</li>";
    echo "<li><strong>Event:</strong> {$test_booking['event_type']}</li>";
    echo "<li><strong>Date:</strong> " . date('F j, Y', strtotime($test_booking['event_date'])) . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // Test admin notification using real SMTP
    $admin_result = sendRealAdminNotificationSMTP($test_booking);
    
    if ($admin_result) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>✅ Real SMTP Admin Notification Sent!</h5>";
        echo "<p><strong>Sent to:</strong> " . ADMIN_EMAIL . "</p>";
        echo "<p><strong>Subject:</strong> 🎉 New Booking Received - {$test_booking['booking_ref']}</p>";
        echo "<p><strong>Method:</strong> Real SMTP authentication with Gmail</p>";
        echo "<p><strong>CHECK YOUR GMAIL INBOX NOW!</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Real SMTP Admin Notification Failed</h5>";
        echo "<p>The admin notification could not be sent using real SMTP.</p>";
        echo "</div>";
    }
}

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>📋 Test Real Booking Notification</h4>";
echo "<form method='POST'>";
echo "<p>This will test the admin booking notification using REAL SMTP authentication.</p>";
echo "<button type='submit' name='test_booking_notification' style='background: #ffc107; color: #212529; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>🎉 Test Real SMTP Booking Notification</button>";
echo "</form>";
echo "</div>";

echo "<h3>4. Real SMTP Logs</h3>";

$log_files = [
    'real_smtp_log.txt' => 'Real SMTP email attempts',
    'email_log.txt' => 'Old email system attempts'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📄 Email Logs:</h4>";

foreach ($log_files as $log_file => $description) {
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        if (!empty(trim($log_content))) {
            echo "<h5>{$log_file} ({$description}):</h5>";
            $log_lines = array_slice(array_filter(explode("\n", $log_content)), -5); // Last 5 lines
            echo "<pre style='background: #fff; padding: 10px; border-radius: 4px; font-size: 12px; max-height: 150px; overflow-y: auto;'>";
            echo htmlspecialchars(implode("\n", $log_lines));
            echo "</pre>";
        } else {
            echo "<p>{$log_file}: Empty</p>";
        }
    } else {
        echo "<p>{$log_file}: Not found</p>";
    }
}
echo "</div>";

echo "<h3>5. Comparison: Old vs New System</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📊 Email System Comparison:</h4>";

echo "<table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>";
echo "<tr style='background: #e9ecef;'>";
echo "<th style='padding: 10px; border: 1px solid #ddd;'>Feature</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd;'>Old System (enhanced_smtp.php)</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd;'>New System (real_smtp_email.php)</th>";
echo "</tr>";

$comparisons = [
    'Email Method' => ['PHP mail() function', 'Direct SMTP connection'],
    'Gmail Authentication' => ['❌ No authentication', '✅ App password authentication'],
    'SMTP Connection' => ['❌ No SMTP connection', '✅ Real connection to smtp.gmail.com:587'],
    'TLS Encryption' => ['❌ Not used', '✅ TLS encryption enabled'],
    'Delivery Success' => ['❌ Shows success but emails not delivered', '✅ Actually delivers emails'],
    'Gmail Compatibility' => ['❌ Not compatible', '✅ Fully compatible'],
    'Error Handling' => ['❌ Poor error reporting', '✅ Detailed error logging']
];

foreach ($comparisons as $feature => $data) {
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>{$feature}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$data[0]}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$data[1]}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<h3>6. Next Steps</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🎯 Implementation Plan:</h4>";
echo "<ol>";
echo "<li><strong>Test the real SMTP system above</strong> - Use the test buttons</li>";
echo "<li><strong>Verify emails arrive in Gmail</strong> - Check byirival009@gmail.com</li>";
echo "<li><strong>If successful, update booking system</strong> - Replace old email functions</li>";
echo "<li><strong>Test complete booking workflow</strong> - Submit real booking</li>";
echo "<li><strong>Verify admin notifications work</strong> - Check Gmail for booking alerts</li>";
echo "</ol>";

echo "<h4>✅ Expected Results:</h4>";
echo "<ul>";
echo "<li>✅ Test emails will actually arrive in your Gmail inbox</li>";
echo "<li>✅ Booking notifications will reach byirival009@gmail.com</li>";
echo "<li>✅ Admin replies to clients will work properly</li>";
echo "<li>✅ All emails will be delivered reliably</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🔧 Technical Details:</h4>";
echo "<p><strong>The new system:</strong></p>";
echo "<ul>";
echo "<li>Creates a direct socket connection to smtp.gmail.com:587</li>";
echo "<li>Enables TLS encryption for security</li>";
echo "<li>Authenticates using your Gmail app password</li>";
echo "<li>Sends emails through Gmail's SMTP servers</li>";
echo "<li>Provides detailed error logging</li>";
echo "</ul>";

echo "<p><strong>This is why your emails will now be delivered!</strong></p>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Real SMTP</title>
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
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        input, button { font-family: inherit; }
        button:hover { opacity: 0.9; transform: translateY(-1px); transition: all 0.3s ease; }
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
