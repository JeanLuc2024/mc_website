<?php
/**
 * Diagnose Email Issue
 * 
 * Check why admin is not receiving booking notification emails
 */

echo "<h2>🔍 Diagnosing Email Issue</h2>";

// Include required files
require_once 'config.php';
require_once 'enhanced_smtp.php';

echo "<h3>1. Check Recent Bookings and Email Status</h3>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get recent bookings
    $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5");
    $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>📋 Recent Bookings:</h4>";
    
    if (!empty($recent_bookings)) {
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #e9ecef;'>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Booking Ref</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Client</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Event Type</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Submission Date</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Status</th>";
        echo "</tr>";
        
        foreach ($recent_bookings as $booking) {
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$booking['booking_ref']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$booking['name']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$booking['event_type']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . date('M j, Y H:i', strtotime($booking['created_at'])) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'><span style='background: #ffc107; padding: 2px 8px; border-radius: 12px; font-size: 12px;'>{$booking['status']}</span></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No recent bookings found.</p>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error loading bookings: " . $e->getMessage() . "</p>";
}

echo "<h3>2. Check Email Logs</h3>";

$log_files = [
    'email_log.txt' => 'SMTP email attempts',
    'manual_emails.txt' => 'Failed emails (fallback)',
    'pending_emails.txt' => 'Pending notifications'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📄 Email Activity Logs:</h4>";

$emails_found = false;
foreach ($log_files as $log_file => $description) {
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        if (!empty(trim($log_content))) {
            $emails_found = true;
            echo "<h5>{$log_file} ({$description}):</h5>";
            $log_lines = array_slice(array_filter(explode("\n", $log_content)), -10); // Last 10 lines
            echo "<pre style='background: #fff; padding: 10px; border-radius: 4px; font-size: 12px; max-height: 200px; overflow-y: auto;'>";
            echo htmlspecialchars(implode("\n", $log_lines));
            echo "</pre>";
        } else {
            echo "<p>{$log_file}: Empty</p>";
        }
    } else {
        echo "<p>{$log_file}: Not found</p>";
    }
}

if (!$emails_found) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h5>⚠️ No Email Activity Found</h5>";
    echo "<p>This suggests that the email functions might not be executing properly.</p>";
    echo "</div>";
}
echo "</div>";

echo "<h3>3. Test Email Function Directly</h3>";

if (isset($_POST['test_email_function'])) {
    echo "<h4>🧪 Testing Email Function...</h4>";
    
    // Test the exact same function used in booking handler
    $test_booking = [
        'id' => 999,
        'booking_ref' => 'TEST-EMAIL-' . date('ymd-His'),
        'name' => 'Email Function Test',
        'email' => 'test@example.com',
        'phone' => '0788487100',
        'event_date' => date('Y-m-d', strtotime('+7 days')),
        'event_time' => '14:00',
        'event_type' => 'Email Function Test',
        'event_location' => 'Test Location',
        'guests' => 50,
        'package' => 'Premium Package',
        'message' => 'Testing if the email function works directly.'
    ];
    
    echo "<p><strong>Testing sendAdminNotificationSMTP() function...</strong></p>";
    
    try {
        $result = sendAdminNotificationSMTP($test_booking);
        
        if ($result) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>✅ Email Function Test PASSED!</h5>";
            echo "<p>The sendAdminNotificationSMTP() function executed successfully.</p>";
            echo "<p><strong>Email should be sent to:</strong> byirival009@gmail.com</p>";
            echo "<p><strong>Check your Gmail inbox and spam folder!</strong></p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>❌ Email Function Test FAILED</h5>";
            echo "<p>The sendAdminNotificationSMTP() function returned false.</p>";
            echo "<p>Check the email logs above for error details.</p>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Email Function Error</h5>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Test Email Function</h4>";
echo "<form method='POST'>";
echo "<p>This will test the exact same email function used when clients submit bookings.</p>";
echo "<button type='submit' name='test_email_function' style='background: #17a2b8; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>🔧 Test sendAdminNotificationSMTP()</button>";
echo "</form>";
echo "</div>";

echo "<h3>4. Simulate Real Booking Submission</h3>";

if (isset($_POST['simulate_booking'])) {
    echo "<h4>📋 Simulating Real Booking Submission...</h4>";
    
    // Simulate the exact process from booking_handler.php
    $test_data = [
        'name' => 'Real Booking Test',
        'email' => 'realbookingtest@example.com',
        'phone' => '0788487100',
        'event_date' => date('Y-m-d', strtotime('+10 days')),
        'event_time' => '15:00',
        'event_type' => 'Real Booking Simulation',
        'event_location' => 'Simulation Test Location',
        'guests' => '75',
        'package' => 'Premium Package',
        'message' => 'This is a simulation of a real booking to test email notifications.',
        'terms' => 'on'
    ];
    
    // Backup original POST data
    $original_post = $_POST;
    $original_method = $_SERVER['REQUEST_METHOD'];
    
    // Set test data
    $_POST = $test_data;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Capture output from booking handler
    ob_start();
    try {
        include 'booking_handler.php';
        $handler_output = ob_get_contents();
    } catch (Exception $e) {
        $handler_output = json_encode(['success' => false, 'message' => 'Handler error: ' . $e->getMessage()]);
    }
    ob_end_clean();
    
    // Restore original data
    $_POST = $original_post;
    $_SERVER['REQUEST_METHOD'] = $original_method;
    
    // Parse the response
    $response = json_decode($handler_output, true);
    
    if ($response && isset($response['success'])) {
        if ($response['success']) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>✅ Booking Simulation SUCCESSFUL!</h5>";
            echo "<p><strong>Booking Reference:</strong> " . htmlspecialchars($response['booking_ref']) . "</p>";
            
            if (isset($response['email_status'])) {
                echo "<p><strong>Email Status:</strong></p>";
                echo "<ul>";
                echo "<li>Client Email: " . ($response['email_status']['client'] ? '✅ Sent' : '❌ Failed') . "</li>";
                echo "<li>Admin Email: " . ($response['email_status']['admin'] ? '✅ Sent' : '❌ Failed') . "</li>";
                echo "</ul>";
                
                if ($response['email_status']['admin']) {
                    echo "<p style='color: green;'><strong>✅ Admin email was sent! Check byirival009@gmail.com</strong></p>";
                } else {
                    echo "<p style='color: red;'><strong>❌ Admin email failed to send</strong></p>";
                }
            }
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>❌ Booking Simulation Failed</h5>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($response['message']) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Invalid Response</h5>";
        echo "<p><strong>Raw Output:</strong></p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px;'>" . htmlspecialchars($handler_output) . "</pre>";
        echo "</div>";
    }
}

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>📋 Simulate Real Booking</h4>";
echo "<form method='POST'>";
echo "<p>This will simulate the exact process that happens when a client submits a booking through the form.</p>";
echo "<button type='submit' name='simulate_booking' style='background: #ffc107; color: #212529; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>📋 Simulate Real Booking Submission</button>";
echo "</form>";
echo "</div>";

echo "<h3>5. Gmail Troubleshooting</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>📧 Gmail Checklist for byirival009@gmail.com</h4>";

echo "<h5>🔍 Where to Look for Emails:</h5>";
echo "<ol>";
echo "<li><strong>Primary Inbox:</strong> Check main inbox for emails</li>";
echo "<li><strong>Spam/Junk Folder:</strong> Gmail might filter booking emails as spam</li>";
echo "<li><strong>All Mail:</strong> Check the 'All Mail' folder</li>";
echo "<li><strong>Search:</strong> Search for 'booking', 'MC-', or 'Byiringiro'</li>";
echo "<li><strong>Promotions Tab:</strong> Check if Gmail sorted emails into Promotions</li>";
echo "</ol>";

echo "<h5>⚙️ Gmail Settings to Check:</h5>";
echo "<ul>";
echo "<li><strong>Filters:</strong> Check if you have filters blocking emails</li>";
echo "<li><strong>Blocked Addresses:</strong> Ensure byirival009@gmail.com is not blocked</li>";
echo "<li><strong>2FA Status:</strong> Verify 2-Factor Authentication is still enabled</li>";
echo "<li><strong>App Password:</strong> Confirm 'fvaa vjqd hwfv jewt' is still valid</li>";
echo "</ul>";

echo "<h5>🔧 Technical Solutions:</h5>";
echo "<ol>";
echo "<li><strong>Generate New App Password:</strong> Create a fresh Gmail app password</li>";
echo "<li><strong>Add to Contacts:</strong> Add byirival009@gmail.com to your contacts</li>";
echo "<li><strong>Check Gmail Security:</strong> Look for security alerts in Gmail</li>";
echo "<li><strong>Test from Different Email:</strong> Send test email from another service</li>";
echo "</ol>";
echo "</div>";

echo "<h3>6. Summary and Next Steps</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🎯 Diagnosis Summary</h4>";
echo "<p><strong>What we know:</strong></p>";
echo "<ul>";
echo "<li>✅ Bookings are being saved to database (you see them in admin panel)</li>";
echo "<li>✅ Booking handler includes email functions</li>";
echo "<li>❓ Email functions may or may not be executing</li>";
echo "<li>❓ Emails may be sent but not reaching Gmail inbox</li>";
echo "</ul>";

echo "<p><strong>Action Plan:</strong></p>";
echo "<ol>";
echo "<li><strong>Run tests above:</strong> Use the test buttons to check email functions</li>";
echo "<li><strong>Check Gmail thoroughly:</strong> Look in all folders and search</li>";
echo "<li><strong>Review email logs:</strong> Check for error messages</li>";
echo "<li><strong>Submit real booking:</strong> <a href='../booking.html' target='_blank'>Test booking form</a></li>";
echo "<li><strong>Monitor email logs:</strong> Watch for new entries after booking submission</li>";
echo "</ol>";
echo "</div>";

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Diagnose Email Issue</title>
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
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
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
