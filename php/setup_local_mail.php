<?php
/**
 * Local Mail Server Setup for XAMPP
 * 
 * This script helps configure XAMPP to send emails locally
 */

echo "<h2>📧 XAMPP Local Mail Setup</h2>";

// Check current PHP mail configuration
$current_config = [
    'sendmail_path' => ini_get('sendmail_path'),
    'SMTP' => ini_get('SMTP'),
    'smtp_port' => ini_get('smtp_port'),
    'sendmail_from' => ini_get('sendmail_from')
];

echo "<h3>1. Current PHP Mail Configuration</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #e9ecef;'>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Setting</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Current Value</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Status</th>";
echo "</tr>";

foreach ($current_config as $setting => $value) {
    $status = !empty($value) ? 'Configured' : 'Not Set';
    $color = !empty($value) ? '#28a745' : '#dc3545';
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'><strong>{$setting}</strong></td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . ($value ?: 'Not set') . "</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd; color: {$color};'>{$status}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// Test basic email sending
echo "<h3>2. Email Sending Test</h3>";

$test_email = 'izabayojeanlucseverin@gmail.com';
$test_subject = 'XAMPP Mail Test - ' . date('Y-m-d H:i:s');
$test_message = '
<html>
<body style="font-family: Arial, sans-serif;">
    <h2>📧 XAMPP Mail Test</h2>
    <p>This is a test email to verify that XAMPP mail configuration is working.</p>
    <p><strong>Test Details:</strong></p>
    <ul>
        <li>Date: ' . date('F j, Y g:i A') . '</li>
        <li>Server: ' . $_SERVER['SERVER_NAME'] . '</li>
        <li>PHP Version: ' . PHP_VERSION . '</li>
    </ul>
    <p>If you received this email, your mail system is working correctly!</p>
</body>
</html>';

$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: XAMPP Test <noreply@localhost>',
    'Reply-To: izabayojeanlucseverin@gmail.com'
];

echo "<p><strong>Testing email to:</strong> {$test_email}</p>";

$email_sent = @mail($test_email, $test_subject, $test_message, implode("\r\n", $headers));

if ($email_sent) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
    echo "<h4>✅ EMAIL SENT SUCCESSFULLY!</h4>";
    echo "<p>Test email was sent. Check your inbox at {$test_email}</p>";
    echo "<p><strong>This means your XAMPP mail configuration is working!</strong></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;'>";
    echo "<h4>❌ EMAIL FAILED TO SEND</h4>";
    echo "<p>XAMPP mail is not configured properly. Follow the setup instructions below.</p>";
    echo "</div>";
}

// Setup instructions
echo "<h3>3. XAMPP Mail Setup Instructions</h3>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h4>🛠️ Quick Setup Options</h4>";

echo "<h5>Option A: Use MailHog (Recommended for Testing)</h5>";
echo "<ol>";
echo "<li><strong>Download MailHog:</strong> <a href='https://github.com/mailhog/MailHog/releases' target='_blank'>https://github.com/mailhog/MailHog/releases</a></li>";
echo "<li><strong>Extract</strong> MailHog.exe to a folder (e.g., C:\\mailhog\\)</li>";
echo "<li><strong>Run MailHog:</strong> Double-click MailHog.exe</li>";
echo "<li><strong>Configure PHP:</strong> Update php.ini with these settings:</li>";
echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
echo "<pre>";
echo "sendmail_path = \"C:\\mailhog\\mhsendmail.exe\"\n";
echo "SMTP = localhost\n";
echo "smtp_port = 1025";
echo "</pre>";
echo "</div>";
echo "<li><strong>Restart Apache</strong> in XAMPP</li>";
echo "<li><strong>View emails:</strong> Open <a href='http://localhost:8025' target='_blank'>http://localhost:8025</a></li>";
echo "</ol>";

echo "<h5>Option B: Use hMailServer (Full Mail Server)</h5>";
echo "<ol>";
echo "<li><strong>Download hMailServer:</strong> <a href='https://www.hmailserver.com/download' target='_blank'>https://www.hmailserver.com/download</a></li>";
echo "<li><strong>Install</strong> with default settings</li>";
echo "<li><strong>Configure</strong> a domain and email account</li>";
echo "<li><strong>Update php.ini:</strong></li>";
echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
echo "<pre>";
echo "SMTP = localhost\n";
echo "smtp_port = 25\n";
echo "sendmail_from = noreply@localhost";
echo "</pre>";
echo "</div>";
echo "<li><strong>Restart Apache</strong> in XAMPP</li>";
echo "</ol>";

echo "<h5>Option C: Use Gmail SMTP (Production Ready)</h5>";
echo "<ol>";
echo "<li><strong>Enable 2-Factor Authentication</strong> on Gmail</li>";
echo "<li><strong>Generate App Password</strong> in Google Account settings</li>";
echo "<li><strong>Update email_config.php:</strong></li>";
echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
echo "<pre>";
echo "define('SMTP_PASSWORD', 'your-16-character-app-password');";
echo "</pre>";
echo "</div>";
echo "<li><strong>Install PHPMailer</strong> for better SMTP support (optional)</li>";
echo "</ol>";

echo "</div>";

// PHP.ini location
echo "<h3>4. PHP Configuration File Location</h3>";

$php_ini_path = php_ini_loaded_file();
echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<p><strong>PHP.ini file location:</strong></p>";
echo "<code style='background: #ffffff; padding: 8px; border-radius: 4px; display: block; margin: 10px 0;'>";
echo $php_ini_path ?: 'No php.ini file loaded';
echo "</code>";

if ($php_ini_path) {
    echo "<p><strong>To edit php.ini:</strong></p>";
    echo "<ol>";
    echo "<li>Stop Apache in XAMPP Control Panel</li>";
    echo "<li>Open the php.ini file in a text editor</li>";
    echo "<li>Find the [mail function] section</li>";
    echo "<li>Update the mail settings as shown above</li>";
    echo "<li>Save the file</li>";
    echo "<li>Start Apache in XAMPP Control Panel</li>";
    echo "</ol>";
} else {
    echo "<p style='color: #dc3545;'>⚠️ No php.ini file found. This might indicate a PHP configuration issue.</p>";
}

echo "</div>";

// Test after setup
echo "<h3>5. Test After Setup</h3>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 After configuring mail settings:</h4>";
echo "<ol>";
echo "<li><strong>Restart Apache</strong> in XAMPP Control Panel</li>";
echo "<li><strong>Refresh this page</strong> to test email again</li>";
echo "<li><strong>Test booking form:</strong> <a href='../booking.html' target='_blank'>booking.html</a></li>";
echo "<li><strong>Check email setup guide:</strong> <a href='email_setup_guide.php'>email_setup_guide.php</a></li>";
echo "</ol>";

echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='email_setup_guide.php' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;'>";
echo "📧 Go to Email Setup Guide";
echo "</a>";
echo "</div>";

echo "</div>";

// Troubleshooting
echo "<h3>6. Common Issues</h3>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🔍 Troubleshooting:</h4>";

echo "<h5>Error: 'Failed to connect to mailserver at localhost port 25'</h5>";
echo "<ul>";
echo "<li><strong>Cause:</strong> No mail server running on port 25</li>";
echo "<li><strong>Solution:</strong> Install MailHog or hMailServer</li>";
echo "</ul>";

echo "<h5>Error: 'Could not execute mail delivery program'</h5>";
echo "<ul>";
echo "<li><strong>Cause:</strong> sendmail_path not configured</li>";
echo "<li><strong>Solution:</strong> Set sendmail_path in php.ini</li>";
echo "</ul>";

echo "<h5>Emails not received</h5>";
echo "<ul>";
echo "<li>Check spam/junk folder</li>";
echo "<li>Verify email address spelling</li>";
echo "<li>Check mail server logs</li>";
echo "<li>Try different email address</li>";
echo "</ul>";

echo "<h5>PHP.ini changes not taking effect</h5>";
echo "<ul>";
echo "<li>Make sure you edited the correct php.ini file</li>";
echo "<li>Restart Apache after making changes</li>";
echo "<li>Check for syntax errors in php.ini</li>";
echo "</ul>";

echo "</div>";

// Summary
$mail_status = $email_sent ? 'WORKING' : 'NEEDS SETUP';
$status_color = $email_sent ? '#28a745' : '#dc3545';
$status_bg = $email_sent ? '#d4edda' : '#f8d7da';

echo "<div style='background: {$status_bg}; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid {$status_color};'>";
echo "<h3 style='color: {$status_color};'>📧 XAMPP Mail Status: {$mail_status}</h3>";

if ($email_sent) {
    echo "<p><strong>✅ Your XAMPP mail configuration is working!</strong></p>";
    echo "<ul>";
    echo "<li>Booking notifications will be sent</li>";
    echo "<li>Admin will receive email alerts</li>";
    echo "<li>Clients will receive confirmations</li>";
    echo "</ul>";
    echo "<p><strong>Next step:</strong> Test the booking form to ensure everything works together.</p>";
} else {
    echo "<p><strong>❌ XAMPP mail needs configuration</strong></p>";
    echo "<ul>";
    echo "<li>Follow one of the setup options above</li>";
    echo "<li>Restart Apache after configuration</li>";
    echo "<li>Test again by refreshing this page</li>";
    echo "</ul>";
}

echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>XAMPP Mail Setup</title>
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
        pre { font-family: monospace; font-size: 14px; background: #f8f9fa; padding: 10px; border-radius: 4px; }
        table { font-size: 14px; }
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
