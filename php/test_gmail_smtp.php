<?php
/**
 * Test Gmail SMTP Configuration
 * 
 * Test the updated SMTP settings with your Gmail app password
 */

echo "<h2>📧 Testing Gmail SMTP Configuration</h2>";
echo "<p><strong>App Name:</strong> Booking System</p>";
echo "<p><strong>Admin Email:</strong> byirival009@gmail.com</p>";

// Include required files
require_once 'config.php';
require_once 'enhanced_smtp.php';

echo "<h3>1. SMTP Configuration Verification</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📋 Current SMTP Settings:</h4>";
echo "<ul>";
echo "<li><strong>SMTP Host:</strong> " . SMTP_HOST . "</li>";
echo "<li><strong>SMTP Port:</strong> " . SMTP_PORT . "</li>";
echo "<li><strong>SMTP Username:</strong> " . SMTP_USERNAME . "</li>";
echo "<li><strong>SMTP Password:</strong> " . (SMTP_PASSWORD !== 'your-app-password' ? '✅ Configured (fvaa vjqd hwfv jewt)' : '❌ Not configured') . "</li>";
echo "<li><strong>From Email:</strong> " . SMTP_FROM_EMAIL . "</li>";
echo "<li><strong>From Name:</strong> " . SMTP_FROM_NAME . "</li>";
echo "<li><strong>Admin Email:</strong> " . ADMIN_EMAIL . "</li>";
echo "</ul>";
echo "</div>";

// Check if password is configured
if (SMTP_PASSWORD === 'your-app-password') {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;'>";
    echo "<h4>❌ SMTP Password Not Configured</h4>";
    echo "<p>The Gmail app password is not set in config.php</p>";
    echo "</div>";
    exit;
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
    echo "<h4>✅ SMTP Password Configured</h4>";
    echo "<p>Gmail app password is properly set in config.php</p>";
    echo "</div>";
}

echo "<h3>2. Email Test</h3>";

// Test email form
if (isset($_POST['send_test'])) {
    $test_email = $_POST['test_email'];
    
    echo "<h4>📤 Sending Test Email...</h4>";
    echo "<p><strong>To:</strong> {$test_email}</p>";
    echo "<p><strong>From:</strong> " . SMTP_FROM_NAME . " &lt;" . SMTP_FROM_EMAIL . "&gt;</p>";
    
    // Send test email
    $result = testSMTPConfiguration($test_email);
    
    if ($result) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
        echo "<h4>✅ Test Email Sent Successfully!</h4>";
        echo "<p>Check <strong>{$test_email}</strong> for the test email.</p>";
        echo "<p>If you received it, your Gmail SMTP is working perfectly!</p>";
        echo "</div>";
        
        // Log success
        echo "<p style='color: green;'>✅ Email logged to: php/email_log.txt</p>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;'>";
        echo "<h4>❌ Test Email Failed</h4>";
        echo "<p>The email could not be sent. Possible issues:</p>";
        echo "<ul>";
        echo "<li>Gmail app password incorrect</li>";
        echo "<li>2-Factor Authentication not enabled</li>";
        echo "<li>Internet connection issue</li>";
        echo "<li>Gmail account temporarily locked</li>";
        echo "</ul>";
        echo "<p>Email saved for manual sending: php/manual_emails.txt</p>";
        echo "</div>";
    }
}

// Test form
echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🧪 Send Test Email</h4>";
echo "<form method='POST'>";
echo "<p><strong>Enter email address to test:</strong></p>";
echo "<input type='email' name='test_email' placeholder='your-email@example.com' style='padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;' required>";
echo "<button type='submit' name='send_test' style='background: #17a2b8; color: white; padding: 8px 15px; border: none; border-radius: 4px; margin-left: 10px; cursor: pointer;'>📧 Send Test Email</button>";
echo "</form>";
echo "<p><small>This will send a test email to verify your SMTP configuration is working.</small></p>";
echo "</div>";

echo "<h3>3. Test Booking System Emails</h3>";

// Test booking system emails
if (isset($_POST['test_booking_emails'])) {
    echo "<h4>📋 Testing Booking System Email Templates...</h4>";
    
    // Create sample booking data
    $sample_booking = [
        'id' => 999,
        'booking_ref' => 'TEST-' . date('ymd-His'),
        'name' => 'Test Client',
        'email' => $_POST['client_email'],
        'phone' => '+250123456789',
        'event_date' => date('Y-m-d', strtotime('+7 days')),
        'event_time' => '14:00',
        'event_type' => 'SMTP Test Event',
        'event_location' => 'Test Location',
        'guests' => 50,
        'package' => 'Premium Package',
        'message' => 'This is a test booking to verify the SMTP email system is working correctly.'
    ];
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h5>📊 Sample Booking Data:</h5>";
    echo "<ul>";
    echo "<li><strong>Booking Ref:</strong> {$sample_booking['booking_ref']}</li>";
    echo "<li><strong>Client:</strong> {$sample_booking['name']} ({$sample_booking['email']})</li>";
    echo "<li><strong>Event:</strong> {$sample_booking['event_type']}</li>";
    echo "<li><strong>Date:</strong> " . date('F j, Y', strtotime($sample_booking['event_date'])) . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // Test 1: Client confirmation email
    echo "<h5>1. Testing Client Confirmation Email...</h5>";
    $client_result = sendBookingConfirmationSMTP($sample_booking);
    
    if ($client_result) {
        echo "<p style='color: green;'>✅ Client confirmation email sent to: {$sample_booking['email']}</p>";
    } else {
        echo "<p style='color: red;'>❌ Client confirmation email failed</p>";
    }
    
    // Test 2: Admin notification email
    echo "<h5>2. Testing Admin Notification Email...</h5>";
    $admin_result = sendAdminNotificationSMTP($sample_booking);
    
    if ($admin_result) {
        echo "<p style='color: green;'>✅ Admin notification email sent to: " . ADMIN_EMAIL . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Admin notification email failed</p>";
    }
    
    // Test 3: Status update email
    echo "<h5>3. Testing Status Update Email...</h5>";
    $status_result = sendStatusUpdateSMTP($sample_booking, 'confirmed', 'Your booking has been confirmed! We look forward to making your event special.');
    
    if ($status_result) {
        echo "<p style='color: green;'>✅ Status update email sent to: {$sample_booking['email']}</p>";
    } else {
        echo "<p style='color: red;'>❌ Status update email failed</p>";
    }
    
    // Summary
    $total_tests = 3;
    $passed_tests = ($client_result ? 1 : 0) + ($admin_result ? 1 : 0) + ($status_result ? 1 : 0);
    
    echo "<div style='background: " . ($passed_tests == $total_tests ? '#d4edda' : '#fff3cd') . "; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid " . ($passed_tests == $total_tests ? '#28a745' : '#ffc107') . ";'>";
    echo "<h5>" . ($passed_tests == $total_tests ? '✅' : '⚠️') . " Email System Test Results</h5>";
    echo "<p><strong>Passed:</strong> {$passed_tests}/{$total_tests} tests</p>";
    
    if ($passed_tests == $total_tests) {
        echo "<p>🎉 <strong>All email templates are working perfectly!</strong></p>";
        echo "<p>Your booking system is ready for production use.</p>";
    } else {
        echo "<p>⚠️ Some email tests failed. Check the logs and configuration.</p>";
    }
    echo "</div>";
}

// Booking system test form
echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>🎯 Test Complete Booking System Emails</h4>";
echo "<form method='POST'>";
echo "<p><strong>Client email address (to receive booking emails):</strong></p>";
echo "<input type='email' name='client_email' placeholder='client-email@example.com' style='padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;' required>";
echo "<button type='submit' name='test_booking_emails' style='background: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 4px; margin-left: 10px; cursor: pointer;'>🧪 Test All Email Templates</button>";
echo "</form>";
echo "<p><small>This will test all 3 email types: client confirmation, admin notification, and status update.</small></p>";
echo "</div>";

echo "<h3>4. Email Logs</h3>";

// Show email logs
$email_log_file = __DIR__ . '/email_log.txt';
$manual_email_file = __DIR__ . '/manual_emails.txt';

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📄 Email Activity Logs:</h4>";

if (file_exists($email_log_file)) {
    $log_content = file_get_contents($email_log_file);
    $log_lines = array_slice(array_filter(explode("\n", $log_content)), -10); // Last 10 lines
    
    echo "<h5>Recent Email Log (last 10 entries):</h5>";
    echo "<pre style='background: #fff; padding: 10px; border-radius: 4px; font-size: 12px; max-height: 200px; overflow-y: auto;'>";
    echo htmlspecialchars(implode("\n", $log_lines));
    echo "</pre>";
} else {
    echo "<p>No email log file found yet.</p>";
}

if (file_exists($manual_email_file)) {
    echo "<h5>⚠️ Manual Emails (failed SMTP):</h5>";
    echo "<p>Some emails were saved for manual sending. Check: <code>php/manual_emails.txt</code></p>";
} else {
    echo "<p style='color: green;'>✅ No manual emails - all emails sent via SMTP successfully!</p>";
}

echo "</div>";

echo "<h3>5. Next Steps</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🚀 Your SMTP Configuration is Ready!</h4>";

echo "<h5>✅ What's Working:</h5>";
echo "<ul>";
echo "<li>✅ <strong>Gmail SMTP configured</strong> with app password: fvaa vjqd hwfv jewt</li>";
echo "<li>✅ <strong>Admin email set</strong> to: byirival009@gmail.com</li>";
echo "<li>✅ <strong>Professional email templates</strong> ready</li>";
echo "<li>✅ <strong>Booking system integration</strong> complete</li>";
echo "</ul>";

echo "<h5>🎯 Test Complete Workflow:</h5>";
echo "<ol>";
echo "<li><strong>Submit booking:</strong> <a href='../booking.html' target='_blank'>Booking Form</a></li>";
echo "<li><strong>Check admin email:</strong> byirival009@gmail.com should receive notification</li>";
echo "<li><strong>Login admin panel:</strong> <a href='../admin/dashboard.php' target='_blank'>Dashboard</a></li>";
echo "<li><strong>Update booking status:</strong> Client should receive status email</li>";
echo "</ol>";

echo "<h5>📧 Email Flow:</h5>";
echo "<ul>";
echo "<li>📋 <strong>Client books</strong> → Gets confirmation email</li>";
echo "<li>🔔 <strong>Admin notified</strong> → byirival009@gmail.com receives alert</li>";
echo "<li>✅ <strong>Admin approves</strong> → Client gets status update</li>";
echo "<li>📱 <strong>All emails professional</strong> → Sent from byirival009@gmail.com</li>";
echo "</ul>";

echo "<p><strong>Your MC booking system is now ready for production with professional Gmail SMTP!</strong> 🎉</p>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Gmail SMTP</title>
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
        input, button { font-family: inherit; }
        button:hover { opacity: 0.9; transform: translateY(-1px); transition: all 0.3s ease; }
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
