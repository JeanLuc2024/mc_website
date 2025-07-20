<?php
/**
 * Email Setup Guide and Test
 * 
 * This script helps set up email notifications and tests the system
 */

require_once 'email_config.php';

echo "<h2>📧 Email System Setup Guide</h2>";

// Test email system
$test_results = testEmailSystem();

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h3>🔧 Current Email System Status</h3>";

if ($test_results['test_email_sent']) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
    echo "<h4>✅ EMAIL SYSTEM WORKING!</h4>";
    echo "<p>Test email was sent successfully to izabayojeanlucseverin@gmail.com</p>";
    echo "<p><strong>This means:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Booking notifications will be sent automatically</li>";
    echo "<li>✅ Admin will receive booking alerts</li>";
    echo "<li>✅ Clients will receive confirmation emails</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;'>";
    echo "<h4>❌ EMAIL SYSTEM NEEDS SETUP</h4>";
    echo "<p>Email notifications are not working. Follow the setup instructions below.</p>";
    echo "</div>";
}

echo "</div>";

// Setup instructions
echo "<h3>🛠️ Email Setup Options</h3>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h4>Option 1: Quick Setup (Recommended for Testing)</h4>";
echo "<p>For immediate testing, the system will try to send emails using PHP's basic mail function.</p>";
echo "<p><strong>Status:</strong> " . ($test_results['basic_mail_works'] ? '✅ Working' : '❌ Not working') . "</p>";

if (!$test_results['basic_mail_works']) {
    echo "<p><strong>To fix this:</strong></p>";
    echo "<ol>";
    echo "<li>Install a local mail server like <a href='https://www.hmailserver.com/' target='_blank'>hMailServer</a></li>";
    echo "<li>Or use <a href='https://github.com/mailhog/MailHog' target='_blank'>MailHog</a> for testing</li>";
    echo "<li>Configure XAMPP PHP mail settings</li>";
    echo "</ol>";
}
echo "</div>";

echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>Option 2: Gmail SMTP Setup (Recommended for Production)</h4>";
echo "<p>For reliable email delivery, configure Gmail SMTP.</p>";
echo "<p><strong>Status:</strong> " . ($test_results['smtp_configured'] ? '✅ Configured' : '❌ Not configured') . "</p>";

echo "<p><strong>Setup Steps:</strong></p>";
echo "<ol>";
echo "<li><strong>Enable 2-Factor Authentication</strong> on your Gmail account</li>";
echo "<li><strong>Generate App Password:</strong>";
echo "<ul>";
echo "<li>Go to Google Account settings</li>";
echo "<li>Security → 2-Step Verification → App passwords</li>";
echo "<li>Generate password for 'Mail'</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Update email configuration:</strong>";
echo "<ul>";
echo "<li>Edit <code>php/email_config.php</code></li>";
echo "<li>Set <code>SMTP_PASSWORD</code> to your app password</li>";
echo "</ul>";
echo "</li>";
echo "</ol>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h5>📝 Configuration Example:</h5>";
echo "<pre style='background: #ffffff; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
echo "// In php/email_config.php, update this line:\n";
echo "define('SMTP_PASSWORD', 'your-16-character-app-password-here');\n";
echo "\n// Example:\n";
echo "define('SMTP_PASSWORD', 'abcd efgh ijkl mnop');";
echo "</pre>";
echo "</div>";

echo "</div>";

// Test booking email
echo "<h3>🧪 Test Booking Email System</h3>";

if (isset($_POST['test_booking_email'])) {
    echo "<h4>Testing Booking Email System...</h4>";
    
    $test_booking_data = [
        'booking_ref' => 'TEST-' . date('ymd') . '-' . strtoupper(substr(md5(time()), 0, 6)),
        'name' => 'Test Client',
        'email' => 'test@example.com',
        'phone' => '+250123456789',
        'event_date' => date('Y-m-d', strtotime('+7 days')),
        'event_time' => '14:00',
        'event_type' => 'Email System Test',
        'event_location' => 'Test Location',
        'guests' => 50,
        'package' => 'Test Package',
        'message' => 'This is a test booking to verify the email notification system is working properly.'
    ];
    
    $email_results = sendBookingEmails($test_booking_data);
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h5>📧 Email Test Results:</h5>";
    echo "<ul>";
    echo "<li><strong>Admin Email:</strong> " . ($email_results['admin_sent'] ? '✅ Sent' : '❌ Failed') . "</li>";
    echo "<li><strong>Client Email:</strong> " . ($email_results['client_sent'] ? '✅ Sent' : '❌ Failed') . "</li>";
    echo "</ul>";
    
    if (!empty($email_results['errors'])) {
        echo "<p><strong>Errors:</strong></p>";
        echo "<ul>";
        foreach ($email_results['errors'] as $error) {
            echo "<li style='color: #dc3545;'>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    if ($email_results['admin_sent']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
        echo "<h4>✅ SUCCESS!</h4>";
        echo "<p>Test booking email sent to izabayojeanlucseverin@gmail.com</p>";
        echo "<p><strong>Booking Reference:</strong> {$test_booking_data['booking_ref']}</p>";
        echo "<p>Check your email inbox (and spam folder) for the test notification.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;'>";
        echo "<h4>❌ EMAIL TEST FAILED</h4>";
        echo "<p>The booking email system is not working properly.</p>";
        echo "<p>Please follow the setup instructions above to configure email delivery.</p>";
        echo "</div>";
    }
}

// Test form
echo "<form method='POST' style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Test Booking Email System</h4>";
echo "<p>Click the button below to send a test booking notification to izabayojeanlucseverin@gmail.com</p>";
echo "<button type='submit' name='test_booking_email' style='background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>";
echo "<i class='fas fa-envelope'></i> Send Test Booking Email";
echo "</button>";
echo "</form>";

// Troubleshooting section
echo "<h3>🔍 Troubleshooting</h3>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>Common Issues and Solutions:</h4>";

echo "<h5>1. 'Failed to connect to mailserver at localhost port 25'</h5>";
echo "<ul>";
echo "<li><strong>Cause:</strong> No local mail server installed</li>";
echo "<li><strong>Solution:</strong> Install hMailServer or configure Gmail SMTP</li>";
echo "</ul>";

echo "<h5>2. Emails not received</h5>";
echo "<ul>";
echo "<li>Check spam/junk folder</li>";
echo "<li>Verify email address is correct</li>";
echo "<li>Check server logs for errors</li>";
echo "</ul>";

echo "<h5>3. Gmail SMTP authentication failed</h5>";
echo "<ul>";
echo "<li>Ensure 2-Factor Authentication is enabled</li>";
echo "<li>Use App Password, not regular password</li>";
echo "<li>Check app password is 16 characters</li>";
echo "</ul>";

echo "<h5>4. PHP mail function disabled</h5>";
echo "<ul>";
echo "<li>Check php.ini for mail settings</li>";
echo "<li>Restart Apache after changes</li>";
echo "<li>Contact hosting provider if on shared hosting</li>";
echo "</ul>";

echo "</div>";

// Next steps
echo "<h3>📋 Next Steps</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>After Email Setup:</h4>";
echo "<ol>";
echo "<li><strong>Test the booking form:</strong> <a href='../booking.html' target='_blank'>booking.html</a></li>";
echo "<li><strong>Submit a test booking</strong> with real data</li>";
echo "<li><strong>Check your email</strong> for notifications</li>";
echo "<li><strong>Verify in admin panel:</strong> <a href='../admin/bookings.php' target='_blank'>Admin Bookings</a></li>";
echo "<li><strong>Test client communication</strong> from admin panel</li>";
echo "</ol>";
echo "</div>";

// Summary
$overall_status = $test_results['test_email_sent'] ? 'WORKING' : 'NEEDS SETUP';
$status_color = $test_results['test_email_sent'] ? '#28a745' : '#dc3545';
$status_bg = $test_results['test_email_sent'] ? '#d4edda' : '#f8d7da';

echo "<div style='background: {$status_bg}; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid {$status_color};'>";
echo "<h3 style='color: {$status_color};'>📧 Email System Status: {$overall_status}</h3>";

if ($test_results['test_email_sent']) {
    echo "<p><strong>✅ Your email system is working!</strong></p>";
    echo "<ul>";
    echo "<li>Booking notifications will be sent automatically</li>";
    echo "<li>Admin will receive alerts at izabayojeanlucseverin@gmail.com</li>";
    echo "<li>Clients will receive professional confirmations</li>";
    echo "</ul>";
} else {
    echo "<p><strong>❌ Email system needs configuration</strong></p>";
    echo "<ul>";
    echo "<li>Follow the setup instructions above</li>";
    echo "<li>Install local mail server or configure Gmail SMTP</li>";
    echo "<li>Test again after configuration</li>";
    echo "</ul>";
}

echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Setup Guide</title>
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
        code { background: #f1f1f1; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
        pre { font-family: monospace; font-size: 14px; }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Content will be inserted here by PHP -->
    </div>
</body>
</html>
