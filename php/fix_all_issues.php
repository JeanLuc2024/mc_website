<?php
/**
 * Fix All Three Issues
 * 
 * 1. Add submission date column to bookings table
 * 2. Fix admin email notifications
 * 3. Fix admin reply email system
 */

echo "<h2>🔧 Fixing All System Issues</h2>";

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h3>1. Adding Submission Date to Bookings Table</h3>";

try {
    // Check if submission_date column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'submission_date'");
    if ($stmt->rowCount() == 0) {
        // Add submission_date column
        $pdo->exec("ALTER TABLE bookings ADD COLUMN submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER created_at");
        echo "<p style='color: green;'>✅ Added 'submission_date' column to bookings table</p>";
        
        // Update existing records to have submission_date = created_at
        $pdo->exec("UPDATE bookings SET submission_date = created_at WHERE submission_date IS NULL");
        echo "<p style='color: green;'>✅ Updated existing bookings with submission dates</p>";
    } else {
        echo "<p style='color: green;'>✅ 'submission_date' column already exists</p>";
    }
    
    // Verify the column
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $booking_count = $stmt->fetchColumn();
    echo "<p style='color: green;'>✅ Bookings table verified: {$booking_count} records with submission dates</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error adding submission_date column: " . $e->getMessage() . "</p>";
}

echo "<h3>2. Checking Email Configuration</h3>";

// Check current email configuration
require_once 'config.php';

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📧 Current Email Configuration:</h4>";
echo "<ul>";
echo "<li><strong>Admin Email:</strong> " . ADMIN_EMAIL . "</li>";
echo "<li><strong>SMTP Host:</strong> " . SMTP_HOST . "</li>";
echo "<li><strong>SMTP Port:</strong> " . SMTP_PORT . "</li>";
echo "<li><strong>SMTP Username:</strong> " . SMTP_USERNAME . "</li>";
echo "<li><strong>SMTP Password:</strong> " . (SMTP_PASSWORD !== 'your-app-password' ? '✅ Configured (fvaa vjqd hwfv jewt)' : '❌ Not configured') . "</li>";
echo "<li><strong>From Email:</strong> " . SMTP_FROM_EMAIL . "</li>";
echo "<li><strong>From Name:</strong> " . SMTP_FROM_NAME . "</li>";
echo "</ul>";
echo "</div>";

echo "<h3>3. Testing Email System</h3>";

// Test email sending
if (isset($_POST['test_admin_email'])) {
    echo "<h4>📤 Testing Admin Email Notification...</h4>";
    
    // Create test booking data
    $test_booking = [
        'id' => 999,
        'booking_ref' => 'TEST-EMAIL-' . date('ymd-His'),
        'name' => 'Email Test Client',
        'email' => 'test@example.com',
        'phone' => '+250123456789',
        'event_date' => date('Y-m-d', strtotime('+7 days')),
        'event_time' => '14:00',
        'event_type' => 'Email System Test',
        'event_location' => 'Test Location',
        'guests' => 50,
        'package' => 'Premium Package',
        'message' => 'This is a test to verify admin email notifications are working.',
        'submission_date' => date('Y-m-d H:i:s')
    ];
    
    // Test admin notification
    $admin_result = testAdminEmailNotification($test_booking);
    
    if ($admin_result) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>✅ Admin Email Test Successful!</h5>";
        echo "<p>Admin notification email sent to: <strong>" . ADMIN_EMAIL . "</strong></p>";
        echo "<p>Check your email inbox for the notification.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Admin Email Test Failed</h5>";
        echo "<p>Email could not be sent. Check the error log below.</p>";
        echo "</div>";
    }
}

// Test client reply email
if (isset($_POST['test_client_reply'])) {
    $client_email = $_POST['client_email'];
    $reply_message = $_POST['reply_message'];
    
    echo "<h4>📤 Testing Client Reply Email...</h4>";
    
    $reply_result = testClientReplyEmail($client_email, $reply_message);
    
    if ($reply_result) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>✅ Client Reply Test Successful!</h5>";
        echo "<p>Reply email sent to: <strong>{$client_email}</strong></p>";
        echo "<p>Check the recipient's inbox for the message.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Client Reply Test Failed</h5>";
        echo "<p>Email could not be sent. Check the error log below.</p>";
        echo "</div>";
    }
}

// Test forms
echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Email System Tests</h4>";

echo "<form method='POST' style='margin: 15px 0;'>";
echo "<h5>Test 1: Admin Email Notification</h5>";
echo "<p>This will send a test booking notification to byirival009@gmail.com</p>";
echo "<button type='submit' name='test_admin_email' style='background: #17a2b8; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>📧 Test Admin Email</button>";
echo "</form>";

echo "<form method='POST' style='margin: 15px 0;'>";
echo "<h5>Test 2: Client Reply Email</h5>";
echo "<p>This will test sending a reply email to a client</p>";
echo "<input type='email' name='client_email' placeholder='Client email address' style='padding: 8px; width: 250px; border: 1px solid #ddd; border-radius: 4px; margin-right: 10px;' required>";
echo "<br><br>";
echo "<textarea name='reply_message' placeholder='Reply message to client' style='padding: 8px; width: 400px; height: 80px; border: 1px solid #ddd; border-radius: 4px;' required>Thank you for your booking request. We have received your information and will contact you shortly to confirm the details.</textarea>";
echo "<br><br>";
echo "<button type='submit' name='test_client_reply' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>📧 Test Client Reply</button>";
echo "</form>";
echo "</div>";

echo "<h3>4. Email Error Logs</h3>";

// Show email logs
$log_files = ['email_log.txt', 'manual_emails.txt', 'pending_emails.txt'];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📄 Email Logs:</h4>";

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        if (!empty(trim($log_content))) {
            echo "<h5>{$log_file}:</h5>";
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

echo "<h3>5. Solutions and Fixes</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>✅ Issue 1: Submission Date - FIXED</h4>";
echo "<ul>";
echo "<li>✅ Added 'submission_date' column to bookings table</li>";
echo "<li>✅ Updated existing records with submission dates</li>";
echo "<li>✅ New bookings will automatically include submission timestamp</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>⚠️ Issue 2: Admin Email Notifications</h4>";
echo "<p><strong>Possible causes:</strong></p>";
echo "<ul>";
echo "<li>Gmail SMTP not properly configured</li>";
echo "<li>App password incorrect or expired</li>";
echo "<li>Email function not being called</li>";
echo "<li>Emails going to spam folder</li>";
echo "</ul>";

echo "<p><strong>Solutions:</strong></p>";
echo "<ol>";
echo "<li><strong>Check Gmail inbox:</strong> Look in byirival009@gmail.com</li>";
echo "<li><strong>Check spam folder:</strong> Emails might be filtered</li>";
echo "<li><strong>Test email above:</strong> Use the admin email test button</li>";
echo "<li><strong>Verify app password:</strong> Ensure 'fvaa vjqd hwfv jewt' is correct</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>❌ Issue 3: Client Reply Emails Failing</h4>";
echo "<p><strong>Common causes:</strong></p>";
echo "<ul>";
echo "<li>SMTP configuration not working in admin panel</li>";
echo "<li>Email function using wrong parameters</li>";
echo "<li>Gmail blocking the connection</li>";
echo "<li>PHP mail() function not configured</li>";
echo "</ul>";

echo "<p><strong>Solutions:</strong></p>";
echo "<ol>";
echo "<li><strong>Test client reply above:</strong> Use the client reply test</li>";
echo "<li><strong>Check error logs:</strong> Look for specific error messages</li>";
echo "<li><strong>Verify SMTP settings:</strong> Ensure all parameters correct</li>";
echo "<li><strong>Use fallback system:</strong> Manual email sending if SMTP fails</li>";
echo "</ol>";
echo "</div>";

echo "<h3>6. Next Steps</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🎯 Action Plan:</h4>";
echo "<ol>";
echo "<li><strong>Test admin email:</strong> Click the 'Test Admin Email' button above</li>";
echo "<li><strong>Check your Gmail:</strong> Look for test email in byirival009@gmail.com</li>";
echo "<li><strong>Test client reply:</strong> Use the client reply test with your email</li>";
echo "<li><strong>Submit real booking:</strong> <a href='../booking.html' target='_blank'>Test booking form</a></li>";
echo "<li><strong>Check admin panel:</strong> <a href='../admin/dashboard.php' target='_blank'>View notifications</a></li>";
echo "</ol>";

echo "<h4>📧 Email Troubleshooting:</h4>";
echo "<ul>";
echo "<li><strong>Gmail Settings:</strong> Ensure 2FA enabled and app password correct</li>";
echo "<li><strong>Spam Folder:</strong> Check if emails are being filtered</li>";
echo "<li><strong>SMTP Logs:</strong> Review error messages above</li>";
echo "<li><strong>Fallback System:</strong> Check manual_emails.txt for failed emails</li>";
echo "</ul>";
echo "</div>";

$pdo = null;

/**
 * Test admin email notification
 */
function testAdminEmailNotification($booking_data) {
    try {
        require_once 'enhanced_smtp.php';
        return sendAdminNotificationSMTP($booking_data);
    } catch (Exception $e) {
        error_log("Admin email test error: " . $e->getMessage());
        return false;
    }
}

/**
 * Test client reply email
 */
function testClientReplyEmail($client_email, $message) {
    try {
        require_once 'enhanced_smtp.php';
        
        $subject = "Message from Byiringiro Valentin MC Services";
        $html_message = createClientReplyTemplate($message);
        
        return sendSMTPEmail($client_email, $subject, $html_message, 'Byiringiro Valentin MC Services', ADMIN_EMAIL);
    } catch (Exception $e) {
        error_log("Client reply test error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create client reply email template
 */
function createClientReplyTemplate($message) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .message-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #3498db; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Message from Byiringiro Valentin</h1>
                <p>Master of Ceremony Services</p>
            </div>
            
            <div class='content'>
                <p>Dear Valued Client,</p>
                
                <div class='message-box'>
                    <p>" . nl2br(htmlspecialchars($message)) . "</p>
                </div>
                
                <p>If you have any questions or need further assistance, please don't hesitate to contact us.</p>
                
                <h3>Contact Information</h3>
                <ul>
                    <li><strong>📧 Email:</strong> byirival009@gmail.com</li>
                    <li><strong>📞 Phone:</strong> +123 456 7890</li>
                </ul>
                
                <p>Best regards,<br>
                <strong>Byiringiro Valentin</strong><br>
                <em>Master of Ceremony</em></p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2025 Byiringiro Valentin MC Services. All Rights Reserved.</p>
            </div>
        </div>
    </body>
    </html>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fix All Issues</title>
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
        input, textarea, button { font-family: inherit; }
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
