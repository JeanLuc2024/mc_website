<?php
/**
 * Test Admin Email Notifications
 * 
 * Specifically test why admin is not receiving booking notification emails
 */

echo "<h2>🔧 Testing Admin Email Notifications</h2>";

// Include required files
require_once 'config.php';
require_once 'enhanced_smtp.php';

echo "<h3>1. Current Email Configuration</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📧 Email Settings:</h4>";
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

echo "<h3>2. Test Direct Email to byirival009@gmail.com</h3>";

if (isset($_POST['test_direct_email'])) {
    echo "<h4>📤 Sending Test Email...</h4>";
    
    $test_subject = "🧪 ADMIN EMAIL TEST - " . date('Y-m-d H:i:s');
    $test_message = createTestAdminEmailTemplate();
    
    echo "<p><strong>To:</strong> byirival009@gmail.com</p>";
    echo "<p><strong>Subject:</strong> {$test_subject}</p>";
    echo "<p><strong>From:</strong> " . SMTP_FROM_NAME . " &lt;" . SMTP_FROM_EMAIL . "&gt;</p>";
    
    $result = sendSMTPEmail(
        'byirival009@gmail.com',
        $test_subject,
        $test_message,
        'MC Booking System Test',
        SMTP_FROM_EMAIL
    );
    
    if ($result) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>✅ Test Email Sent Successfully!</h4>";
        echo "<p><strong>Check your Gmail inbox:</strong> byirival009@gmail.com</p>";
        echo "<p><strong>Also check:</strong> Spam/Junk folder</p>";
        echo "<p><strong>Email logged to:</strong> php/email_log.txt</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>❌ Test Email Failed</h4>";
        echo "<p>The email could not be sent. Check the logs below for details.</p>";
        echo "</div>";
    }
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Send Test Email to Admin</h4>";
echo "<form method='POST'>";
echo "<p>This will send a test email directly to byirival009@gmail.com to verify the email system is working.</p>";
echo "<button type='submit' name='test_direct_email' style='background: #17a2b8; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>📧 Send Test Email to byirival009@gmail.com</button>";
echo "</form>";
echo "</div>";

echo "<h3>3. Test Booking Notification Email</h3>";

if (isset($_POST['test_booking_notification'])) {
    echo "<h4>📤 Testing Booking Notification Email...</h4>";
    
    // Create sample booking data based on the one you showed
    $sample_booking = [
        'id' => 999,
        'booking_ref' => 'MC-TEST-' . date('ymd-His'),
        'name' => 'Test Admin Notification',
        'email' => 'test@example.com',
        'phone' => '0788487100',
        'event_date' => date('Y-m-d', strtotime('+7 days')),
        'event_time' => '14:00',
        'event_type' => 'Corporate Event Test',
        'event_location' => 'Test Location',
        'guests' => 50,
        'package' => 'Premium Package',
        'message' => 'This is a test to verify admin email notifications are working.',
        'submission_date' => date('Y-m-d H:i:s')
    ];
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h5>📊 Test Booking Data:</h5>";
    echo "<ul>";
    echo "<li><strong>Booking Ref:</strong> {$sample_booking['booking_ref']}</li>";
    echo "<li><strong>Client:</strong> {$sample_booking['name']} ({$sample_booking['email']})</li>";
    echo "<li><strong>Event:</strong> {$sample_booking['event_type']}</li>";
    echo "<li><strong>Date:</strong> " . date('F j, Y', strtotime($sample_booking['event_date'])) . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // Test admin notification
    $admin_result = sendAdminNotificationSMTP($sample_booking);
    
    if ($admin_result) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>✅ Admin Notification Email Sent!</h5>";
        echo "<p><strong>Sent to:</strong> byirival009@gmail.com</p>";
        echo "<p><strong>Subject:</strong> 🎉 New Booking Received - {$sample_booking['booking_ref']}</p>";
        echo "<p><strong>Check your Gmail inbox and spam folder!</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Admin Notification Email Failed</h5>";
        echo "<p>The booking notification email could not be sent.</p>";
        echo "</div>";
    }
}

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>📋 Test Booking Notification</h4>";
echo "<form method='POST'>";
echo "<p>This will test the exact same email that should be sent when a client submits a booking.</p>";
echo "<button type='submit' name='test_booking_notification' style='background: #ffc107; color: #212529; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>🎉 Test Booking Notification Email</button>";
echo "</form>";
echo "</div>";

echo "<h3>4. Check Email Logs</h3>";

$log_files = [
    'email_log.txt' => 'SMTP email attempts',
    'manual_emails.txt' => 'Failed emails (fallback)',
    'pending_emails.txt' => 'Pending notifications'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📄 Email Activity Logs:</h4>";

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

echo "<h3>5. Troubleshooting Steps</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🔍 Why You Might Not Be Getting Emails:</h4>";

echo "<h5>📧 Gmail Issues:</h5>";
echo "<ul>";
echo "<li><strong>Spam Folder:</strong> Check your spam/junk folder in Gmail</li>";
echo "<li><strong>Gmail Filters:</strong> Check if you have filters blocking emails</li>";
echo "<li><strong>App Password:</strong> Verify 'fvaa vjqd hwfv jewt' is still valid</li>";
echo "<li><strong>2FA Status:</strong> Ensure 2-Factor Authentication is still enabled</li>";
echo "</ul>";

echo "<h5>🔧 Technical Issues:</h5>";
echo "<ul>";
echo "<li><strong>SMTP Connection:</strong> Gmail might be blocking the connection</li>";
echo "<li><strong>PHP Mail Function:</strong> Server might not be configured for mail</li>";
echo "<li><strong>Firewall:</strong> XAMPP might be blocked from sending emails</li>";
echo "<li><strong>Email Function:</strong> Booking handler might not be calling email function</li>";
echo "</ul>";

echo "<h5>✅ Solutions to Try:</h5>";
echo "<ol>";
echo "<li><strong>Test emails above:</strong> Use the test buttons to verify email sending</li>";
echo "<li><strong>Check Gmail spam:</strong> Look in spam folder for test emails</li>";
echo "<li><strong>Verify app password:</strong> Try generating a new Gmail app password</li>";
echo "<li><strong>Check booking handler:</strong> Ensure it's calling the email function</li>";
echo "<li><strong>Use fallback system:</strong> Check manual_emails.txt for failed emails</li>";
echo "</ol>";
echo "</div>";

echo "<h3>6. Next Steps</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🎯 Action Plan:</h4>";
echo "<ol>";
echo "<li><strong>Send test email:</strong> Click the test buttons above</li>";
echo "<li><strong>Check Gmail thoroughly:</strong> Inbox, spam, all folders</li>";
echo "<li><strong>Submit real booking:</strong> <a href='../booking.html' target='_blank'>Test booking form</a></li>";
echo "<li><strong>Check logs:</strong> Review email logs above for errors</li>";
echo "<li><strong>Verify booking handler:</strong> Ensure it calls email functions</li>";
echo "</ol>";

echo "<h4>📱 Gmail Checklist:</h4>";
echo "<ul>";
echo "<li>✅ Check <strong>Inbox</strong> for emails from MC Booking System</li>";
echo "<li>✅ Check <strong>Spam/Junk</strong> folder</li>";
echo "<li>✅ Check <strong>All Mail</strong> folder</li>";
echo "<li>✅ Search for <strong>'booking'</strong> or <strong>'MC-'</strong></li>";
echo "<li>✅ Look for emails from <strong>byirival009@gmail.com</strong></li>";
echo "</ul>";
echo "</div>";

/**
 * Create test email template for admin
 */
function createTestAdminEmailTemplate() {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #e74c3c; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .test-box { background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🧪 EMAIL SYSTEM TEST</h1>
                <p>Admin Email Notification Test</p>
            </div>
            
            <div class='content'>
                <div class='test-box'>
                    <h3>✅ EMAIL SYSTEM WORKING!</h3>
                    <p>If you receive this email, your Gmail SMTP configuration is working correctly.</p>
                </div>
                
                <p><strong>Test Details:</strong></p>
                <ul>
                    <li><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</li>
                    <li><strong>Admin Email:</strong> " . ADMIN_EMAIL . "</li>
                    <li><strong>SMTP Host:</strong> " . SMTP_HOST . "</li>
                    <li><strong>From Email:</strong> " . SMTP_FROM_EMAIL . "</li>
                </ul>
                
                <p><strong>Next Steps:</strong></p>
                <ol>
                    <li>If you received this email, the system is working</li>
                    <li>Submit a real booking to test the full workflow</li>
                    <li>Check that booking notifications arrive in your inbox</li>
                </ol>
                
                <p>Your MC booking system email notifications should now be working!</p>
            </div>
            
            <div class='footer'>
                <p>MC Booking System - Email Test</p>
                <p>&copy; 2025 Byiringiro Valentin MC Services</p>
            </div>
        </div>
    </body>
    </html>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Admin Email Notifications</title>
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
        button { font-family: inherit; }
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
