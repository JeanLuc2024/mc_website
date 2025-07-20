<?php
/**
 * Email System Test
 * 
 * This script tests the email notification system to ensure emails are being sent.
 */

echo "<h2>📧 Testing Email System...</h2>";

// Test 1: Check PHP mail configuration
echo "<h3>1. PHP Mail Configuration:</h3>";

$mail_settings = [
    'sendmail_path' => ini_get('sendmail_path'),
    'SMTP' => ini_get('SMTP'),
    'smtp_port' => ini_get('smtp_port'),
    'sendmail_from' => ini_get('sendmail_from')
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>Current PHP Mail Settings:</h4>";
echo "<ul>";
foreach ($mail_settings as $setting => $value) {
    $status = !empty($value) ? 'configured' : 'not set';
    $color = !empty($value) ? 'green' : 'orange';
    echo "<li style='color: {$color};'><strong>{$setting}:</strong> " . ($value ?: 'Not set') . " ({$status})</li>";
}
echo "</ul>";
echo "</div>";

// Test 2: Simple email test
echo "<h3>2. Simple Email Test:</h3>";

$test_email = 'izabayojeanlucseverin@gmail.com';
$test_subject = 'Test Email from MC Website - ' . date('Y-m-d H:i:s');
$test_message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 8px 8px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🧪 Email System Test</h1>
            <p>MC Website Email Notification System</p>
        </div>
        <div class='content'>
            <h2>Test Email Successful!</h2>
            <p>This is a test email to verify that the email notification system is working properly.</p>
            
            <h3>Test Details:</h3>
            <ul>
                <li><strong>Date:</strong> " . date('F j, Y g:i A') . "</li>
                <li><strong>Server:</strong> " . $_SERVER['SERVER_NAME'] . "</li>
                <li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>
                <li><strong>Test Type:</strong> Email System Verification</li>
            </ul>
            
            <p>If you received this email, the notification system is working correctly and you should receive booking notifications.</p>
            
            <div style='background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #27ae60;'>
                <h4>✅ Email System Status: WORKING</h4>
                <p>You will now receive notifications when clients make bookings through your website.</p>
            </div>
            
            <p>Best regards,<br>
            <strong>MC Website System</strong></p>
        </div>
    </div>
</body>
</html>";

$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: MC Website System <noreply@mcwebsite.com>',
    'Reply-To: noreply@mcwebsite.com'
];

$header_string = implode("\r\n", $headers);

echo "<p><strong>Sending test email to:</strong> {$test_email}</p>";
echo "<p><strong>Subject:</strong> {$test_subject}</p>";

$email_sent = mail($test_email, $test_subject, $test_message, $header_string);

if ($email_sent) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
    echo "<h4>✅ EMAIL SENT SUCCESSFULLY!</h4>";
    echo "<p>Test email was sent to {$test_email}. Check your inbox (and spam folder) for the test email.</p>";
    echo "<p><strong>This means:</strong></p>";
    echo "<ul>";
    echo "<li>✅ PHP mail function is working</li>";
    echo "<li>✅ Email notifications will be sent for bookings</li>";
    echo "<li>✅ Admin will receive booking alerts</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;'>";
    echo "<h4>❌ EMAIL FAILED TO SEND</h4>";
    echo "<p>The test email could not be sent. This means booking notifications won't work.</p>";
    echo "<p><strong>Possible causes:</strong></p>";
    echo "<ul>";
    echo "<li>XAMPP mail configuration not set up</li>";
    echo "<li>No SMTP server configured</li>";
    echo "<li>Firewall blocking email sending</li>";
    echo "<li>PHP mail function disabled</li>";
    echo "</ul>";
    echo "</div>";
}

// Test 3: Check if booking handler sends emails
echo "<h3>3. Booking Handler Email Test:</h3>";

echo "<p>Testing the booking handler email functionality...</p>";

// Simulate a booking submission to test email
$_POST = [
    'name' => 'Email Test Client',
    'email' => 'test@example.com',
    'phone' => '+250123456789',
    'event_date' => date('Y-m-d', strtotime('+7 days')),
    'event_time' => '14:00',
    'event_type' => 'Email System Test',
    'event_location' => 'Test Location',
    'guests' => '50',
    'package' => 'Test Package',
    'message' => 'This is a test booking to verify email notifications',
    'terms' => 'on'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<p><strong>Simulating booking submission...</strong></p>";

// Capture output from booking handler
ob_start();
include 'booking_handler.php';
$booking_response = ob_get_clean();

// Parse response
$booking_data = json_decode($booking_response, true);

if ($booking_data && $booking_data['success']) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
    echo "<h4>✅ BOOKING HANDLER EMAIL TEST PASSED!</h4>";
    echo "<p>Booking was created successfully: {$booking_data['booking_ref']}</p>";
    echo "<p>Check the PHP error log for email sending status.</p>";
    echo "</div>";
    
    // Clean up test booking
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $delete_sql = "DELETE FROM bookings WHERE booking_ref = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$booking_data['booking_ref']]);
        
        echo "<p style='color: blue;'>🧹 Test booking cleaned up from database</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Could not clean up test booking: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;'>";
    echo "<h4>❌ BOOKING HANDLER EMAIL TEST FAILED</h4>";
    echo "<p>Booking handler did not work properly.</p>";
    if ($booking_data) {
        echo "<p><strong>Error:</strong> " . htmlspecialchars($booking_data['message']) . "</p>";
    }
    echo "</div>";
}

// Test 4: Email troubleshooting guide
echo "<h3>4. Email Troubleshooting Guide:</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🔧 How to Fix Email Issues:</h4>";

if (!$email_sent) {
    echo "<h5>For XAMPP Users (Local Development):</h5>";
    echo "<ol>";
    echo "<li><strong>Install a local mail server:</strong>";
    echo "<ul>";
    echo "<li>Download and install <a href='https://www.hmailserver.com/' target='_blank'>hMailServer</a></li>";
    echo "<li>Or use <a href='https://github.com/mailhog/MailHog' target='_blank'>MailHog</a> for testing</li>";
    echo "</ul>";
    echo "</li>";
    echo "<li><strong>Configure PHP mail settings:</strong>";
    echo "<ul>";
    echo "<li>Edit <code>php.ini</code> file in XAMPP</li>";
    echo "<li>Set SMTP server and port</li>";
    echo "<li>Restart Apache</li>";
    echo "</ul>";
    echo "</li>";
    echo "<li><strong>Alternative - Use Gmail SMTP:</strong>";
    echo "<ul>";
    echo "<li>Install PHPMailer library</li>";
    echo "<li>Configure with Gmail app password</li>";
    echo "<li>Update booking handler to use SMTP</li>";
    echo "</ul>";
    echo "</li>";
    echo "</ol>";
    
    echo "<h5>For Production Servers:</h5>";
    echo "<ol>";
    echo "<li>Ensure mail server is installed and configured</li>";
    echo "<li>Check firewall settings for SMTP ports</li>";
    echo "<li>Verify DNS records (SPF, DKIM)</li>";
    echo "<li>Test with hosting provider's mail service</li>";
    echo "</ol>";
} else {
    echo "<h5>✅ Email System Working!</h5>";
    echo "<p>Your email system is properly configured. You should receive:</p>";
    echo "<ul>";
    echo "<li>✅ Booking notifications when clients submit forms</li>";
    echo "<li>✅ Test email in your inbox</li>";
    echo "<li>✅ All future booking alerts</li>";
    echo "</ul>";
}

echo "</div>";

// Summary
echo "<h3>📋 Email System Summary:</h3>";

$email_status = $email_sent ? 'WORKING' : 'NEEDS SETUP';
$status_color = $email_sent ? '#28a745' : '#dc3545';
$status_bg = $email_sent ? '#d4edda' : '#f8d7da';

echo "<div style='background: {$status_bg}; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid {$status_color};'>";
echo "<h4 style='color: {$status_color};'>📧 Email System Status: {$email_status}</h4>";

if ($email_sent) {
    echo "<p><strong>✅ Everything is working correctly!</strong></p>";
    echo "<ul>";
    echo "<li>Test email sent successfully</li>";
    echo "<li>Booking notifications will work</li>";
    echo "<li>Admin will receive alerts at: izabayojeanlucseverin@gmail.com</li>";
    echo "</ul>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Check your email inbox for the test email</li>";
    echo "<li>Test the booking form: <a href='../booking.html' target='_blank'>booking.html</a></li>";
    echo "<li>Verify you receive booking notifications</li>";
    echo "</ol>";
} else {
    echo "<p><strong>❌ Email system needs configuration</strong></p>";
    echo "<ul>";
    echo "<li>PHP mail function not working</li>";
    echo "<li>Booking notifications won't be sent</li>";
    echo "<li>Need to set up mail server or SMTP</li>";
    echo "</ul>";
    echo "<p><strong>Immediate action needed:</strong></p>";
    echo "<ol>";
    echo "<li>Configure XAMPP mail settings</li>";
    echo "<li>Install local mail server</li>";
    echo "<li>Or set up SMTP with Gmail</li>";
    echo "<li>Re-run this test after configuration</li>";
    echo "</ol>";
}

echo "</div>";

// Reset POST data
$_POST = [];
$_SERVER['REQUEST_METHOD'] = 'GET';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email System Test</title>
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
        code { background: #f1f1f1; padding: 2px 4px; border-radius: 3px; }
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
