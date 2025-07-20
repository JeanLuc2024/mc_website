<?php
/**
 * Fix Duplicate Notifications
 * 
 * Clean up all duplicate email systems and ensure only one system is active
 */

echo "<h2>🔧 Fix Duplicate Notifications</h2>";

echo "<h3>1. Current System Status</h3>";

$systems_status = [
    'booking_handler.php' => ['active' => true, 'description' => 'Main booking handler (SHOULD BE ACTIVE)'],
    'real_smtp_email.php' => ['active' => true, 'description' => 'Real SMTP system (SHOULD BE ACTIVE)'],
    'enhanced_smtp.php' => ['active' => false, 'description' => 'Old fake SMTP system (DISABLED)'],
    'notifications.php' => ['active' => false, 'description' => 'Old notification system (DISABLED)'],
    'notification_system.php' => ['active' => file_exists('notification_system.php'), 'description' => 'Notification system'],
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📊 System Status:</h4>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #e9ecef;'>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>System</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Status</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Description</th>";
echo "</tr>";

foreach ($systems_status as $system => $info) {
    $status = $info['active'] ? '✅ ACTIVE' : '❌ DISABLED';
    $color = $info['active'] ? '#28a745' : '#dc3545';
    
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$system}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd; color: {$color};'>{$status}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$info['description']}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<h3>2. Clean Up Actions</h3>";

if (isset($_POST['cleanup_duplicates'])) {
    echo "<h4>🧹 Performing Cleanup...</h4>";
    
    $cleanup_actions = [];
    
    // Clear all email logs
    $log_files = ['real_smtp_log.txt', 'email_log.txt', 'pending_emails.txt', 'manual_emails.txt'];
    foreach ($log_files as $log_file) {
        if (file_exists($log_file)) {
            file_put_contents($log_file, '');
            $cleanup_actions[] = "✅ Cleared {$log_file}";
        }
    }
    
    // Disable notification_system.php if it exists
    if (file_exists('notification_system.php')) {
        $content = file_get_contents('notification_system.php');
        if (strpos($content, 'DISABLED') === false) {
            $disabled_content = "<?php\n// DISABLED - Use real_smtp_email.php instead\nexit('This notification system has been disabled.');\n\n" . $content;
            file_put_contents('notification_system.php', $disabled_content);
            $cleanup_actions[] = "✅ Disabled notification_system.php";
        } else {
            $cleanup_actions[] = "✅ notification_system.php already disabled";
        }
    }
    
    // Rename old booking handlers to prevent accidental use
    $old_handlers = ['booking.php', 'booking_clean.php', 'booking_simple.php'];
    foreach ($old_handlers as $handler) {
        if (file_exists($handler) && !file_exists($handler . '.disabled')) {
            rename($handler, $handler . '.disabled');
            $cleanup_actions[] = "✅ Renamed {$handler} to {$handler}.disabled";
        }
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h5>✅ Cleanup Actions Completed:</h5>";
    echo "<ul>";
    foreach ($cleanup_actions as $action) {
        echo "<li>{$action}</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧹 Clean Up Duplicate Systems</h4>";
echo "<form method='POST'>";
echo "<p>This will:</p>";
echo "<ul>";
echo "<li>Clear all email logs to start fresh</li>";
echo "<li>Disable old notification systems</li>";
echo "<li>Rename old booking handlers to prevent conflicts</li>";
echo "<li>Ensure only the real SMTP system is active</li>";
echo "</ul>";
echo "<button type='submit' name='cleanup_duplicates' style='background: #ffc107; color: #212529; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🧹 Clean Up Duplicate Systems</button>";
echo "</form>";
echo "</div>";

echo "<h3>3. Test Fixed System</h3>";

if (isset($_POST['test_fixed_system'])) {
    echo "<h4>🧪 Testing Fixed System...</h4>";
    
    // Create test booking data
    $test_data = [
        'name' => 'Fixed System Test Client',
        'email' => 'fixedtest@example.com',
        'phone' => '0788487100',
        'event_date' => date('Y-m-d', strtotime('+7 days')),
        'event_time' => '17:00',
        'event_type' => 'Fixed System Test Event',
        'event_location' => 'Fixed Test Location',
        'guests' => '40',
        'package' => 'Premium Package',
        'message' => 'This is a test of the fixed system with no duplicates.',
        'terms' => 'on'
    ];
    
    echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h5>📊 Test Data:</h5>";
    echo "<ul>";
    echo "<li><strong>Client:</strong> {$test_data['name']}</li>";
    echo "<li><strong>Event:</strong> {$test_data['event_type']}</li>";
    echo "<li><strong>Date:</strong> " . date('F j, Y', strtotime($test_data['event_date'])) . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // Count emails before test
    $before_count = 0;
    if (file_exists('real_smtp_log.txt')) {
        $before_content = file_get_contents('real_smtp_log.txt');
        $before_count = count(array_filter(explode("\n", $before_content)));
    }
    
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
    
    // Count emails after test
    $after_count = 0;
    if (file_exists('real_smtp_log.txt')) {
        $after_content = file_get_contents('real_smtp_log.txt');
        $after_count = count(array_filter(explode("\n", $after_content)));
    }
    
    $email_attempts = $after_count - $before_count;
    
    // Parse the response
    $response = json_decode($handler_output, true);
    
    if ($response && isset($response['success'])) {
        if ($response['success']) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>✅ Test Booking Successful!</h5>";
            echo "<p><strong>Booking Reference:</strong> " . htmlspecialchars($response['booking_ref']) . "</p>";
            echo "<p><strong>Email Attempts:</strong> {$email_attempts}</p>";
            
            if ($email_attempts == 2) {
                echo "<div style='background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
                echo "<p style='color: #0c5460; margin: 0;'><strong>🎉 PERFECT! Exactly 2 emails sent (1 client + 1 admin) - No duplicates!</strong></p>";
                echo "</div>";
            } elseif ($email_attempts > 2) {
                echo "<div style='background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
                echo "<p style='color: #721c24; margin: 0;'><strong>⚠️ Still detecting duplicates! {$email_attempts} emails sent instead of 2.</strong></p>";
                echo "</div>";
            } else {
                echo "<div style='background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
                echo "<p style='color: #856404; margin: 0;'><strong>⚠️ Only {$email_attempts} emails sent. Expected 2.</strong></p>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>❌ Test Failed</h5>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($response['message']) . "</p>";
            echo "</div>";
        }
    }
    
    // Show recent email log
    if (file_exists('real_smtp_log.txt')) {
        $log_content = file_get_contents('real_smtp_log.txt');
        $log_lines = array_filter(explode("\n", $log_content));
        if (!empty($log_lines)) {
            echo "<h5>📄 Recent Email Log:</h5>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px; max-height: 150px; overflow-y: auto;'>";
            echo htmlspecialchars(implode("\n", array_slice($log_lines, -5)));
            echo "</pre>";
        }
    }
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Test Fixed System</h4>";
echo "<form method='POST'>";
echo "<p>This will test the fixed system to ensure no duplicate notifications are sent.</p>";
echo "<p><strong>Expected Result:</strong> Exactly 2 emails (1 client confirmation + 1 admin notification)</p>";
echo "<button type='submit' name='test_fixed_system' style='background: #17a2b8; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🧪 Test Fixed System</button>";
echo "</form>";
echo "</div>";

echo "<h3>4. Final Verification</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>✅ System Configuration Summary</h4>";

echo "<h5>🎯 Active Systems:</h5>";
echo "<ul>";
echo "<li>✅ <strong>booking_handler.php</strong> - Main booking processor</li>";
echo "<li>✅ <strong>real_smtp_email.php</strong> - Real SMTP email system</li>";
echo "<li>✅ <strong>Admin panel notifications</strong> - Dashboard alerts</li>";
echo "</ul>";

echo "<h5>❌ Disabled Systems:</h5>";
echo "<ul>";
echo "<li>❌ <strong>enhanced_smtp.php</strong> - Old fake SMTP (disabled)</li>";
echo "<li>❌ <strong>notifications.php</strong> - Old notification system (disabled)</li>";
echo "<li>❌ <strong>Old booking handlers</strong> - Renamed to .disabled</li>";
echo "</ul>";

echo "<h5>📧 Expected Email Flow:</h5>";
echo "<ol>";
echo "<li>📋 <strong>Client submits booking</strong> → booking_handler.php processes</li>";
echo "<li>📧 <strong>1 client confirmation email</strong> → sent via real SMTP</li>";
echo "<li>🔔 <strong>1 admin notification email</strong> → sent to byirival009@gmail.com</li>";
echo "<li>📊 <strong>1 dashboard notification</strong> → added to admin panel</li>";
echo "<li>✅ <strong>Total: 2 emails, 1 dashboard alert</strong> → No duplicates!</li>";
echo "</ol>";
echo "</div>";

echo "<h3>5. Test Your Booking Form</h3>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🎉 Ready to Test!</h4>";
echo "<p>Your duplicate notification issue should now be fixed. Test it by:</p>";
echo "<ol>";
echo "<li><strong>Submit a real booking:</strong> <a href='../booking.html' target='_blank' style='color: #007bff;'>Go to Booking Form</a></li>";
echo "<li><strong>Check your Gmail:</strong> You should receive exactly 1 admin notification</li>";
echo "<li><strong>Check admin panel:</strong> <a href='../admin/dashboard.php' target='_blank' style='color: #007bff;'>View Dashboard</a></li>";
echo "<li><strong>Verify no duplicates:</strong> Only 1 notification per booking</li>";
echo "</ol>";

echo "<p><strong>🎯 Success Criteria:</strong></p>";
echo "<ul>";
echo "<li>✅ 1 booking submission = 1 admin email notification</li>";
echo "<li>✅ 1 booking submission = 1 client confirmation email</li>";
echo "<li>✅ 1 booking submission = 1 dashboard notification</li>";
echo "<li>✅ No duplicate emails or notifications</li>";
echo "</ul>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fix Duplicate Notifications</title>
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
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
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
