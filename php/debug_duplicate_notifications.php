<?php
/**
 * Debug Duplicate Notifications
 * 
 * Identify why notifications are being sent twice
 */

echo "<h2>🔍 Debug Duplicate Notifications</h2>";

echo "<h3>1. Check Active Booking Handlers</h3>";

$booking_files = [
    'booking_handler.php' => 'Main booking handler (SHOULD BE ACTIVE)',
    'booking.php' => 'Old booking handler',
    'booking_clean.php' => 'Clean booking handler',
    'booking_simple.php' => 'Simple booking handler',
    'booking_handler_backup.php' => 'Backup booking handler',
    'booking_handler_fixed.php' => 'Fixed booking handler'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📁 Booking Handler Files:</h4>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #e9ecef;'>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>File</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Status</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Description</th>";
echo "</tr>";

foreach ($booking_files as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? '✅ EXISTS' : '❌ Missing';
    $color = $exists ? '#28a745' : '#dc3545';
    
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$file}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd; color: {$color};'>{$status}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$description}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<h3>2. Check Email System Files</h3>";

$email_files = [
    'real_smtp_email.php' => 'Real SMTP system (SHOULD BE ACTIVE)',
    'enhanced_smtp.php' => 'Enhanced SMTP (OLD - should be disabled)',
    'notifications.php' => 'Old notification system',
    'notification_system.php' => 'Notification system',
    'simple_email_handler.php' => 'Simple email handler'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📧 Email System Files:</h4>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #e9ecef;'>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>File</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Status</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Description</th>";
echo "</tr>";

foreach ($email_files as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? '✅ EXISTS' : '❌ Missing';
    $color = $exists ? '#28a745' : '#dc3545';
    
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$file}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd; color: {$color};'>{$status}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$description}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<h3>3. Check Email Logs</h3>";

$log_files = [
    'real_smtp_log.txt' => 'Real SMTP attempts',
    'email_log.txt' => 'Enhanced SMTP attempts',
    'pending_emails.txt' => 'Fallback emails',
    'manual_emails.txt' => 'Manual emails'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📄 Email Log Files:</h4>";

foreach ($log_files as $log_file => $description) {
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        if (!empty(trim($log_content))) {
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
echo "</div>";

echo "<h3>4. Test Single Booking Submission</h3>";

if (isset($_POST['test_single_booking'])) {
    echo "<h4>📋 Testing Single Booking Submission...</h4>";
    
    // Clear all logs first
    $logs_to_clear = ['real_smtp_log.txt', 'email_log.txt', 'pending_emails.txt', 'manual_emails.txt'];
    foreach ($logs_to_clear as $log) {
        if (file_exists($log)) {
            file_put_contents($log, '');
        }
    }
    
    echo "<p><strong>✅ Cleared all email logs</strong></p>";
    
    // Create test booking data
    $test_data = [
        'name' => 'Duplicate Test Client',
        'email' => 'duplicatetest@example.com',
        'phone' => '0788487100',
        'event_date' => date('Y-m-d', strtotime('+5 days')),
        'event_time' => '16:00',
        'event_type' => 'Duplicate Notification Test',
        'event_location' => 'Test Location',
        'guests' => '30',
        'package' => 'Standard Package',
        'message' => 'This is a test to identify duplicate notifications.',
        'terms' => 'on'
    ];
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h5>📊 Test Booking Data:</h5>";
    echo "<ul>";
    echo "<li><strong>Client:</strong> {$test_data['name']} ({$test_data['email']})</li>";
    echo "<li><strong>Event:</strong> {$test_data['event_type']}</li>";
    echo "<li><strong>Date:</strong> " . date('F j, Y', strtotime($test_data['event_date'])) . " at {$test_data['event_time']}</li>";
    echo "</ul>";
    echo "</div>";
    
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
            echo "<h5>✅ Booking Submitted Successfully!</h5>";
            echo "<p><strong>Booking Reference:</strong> " . htmlspecialchars($response['booking_ref']) . "</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>❌ Booking Failed</h5>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($response['message']) . "</p>";
            echo "</div>";
        }
    }
    
    // Now check logs for duplicates
    echo "<h5>📄 Email Activity After Test:</h5>";
    
    $email_count = 0;
    foreach ($log_files as $log_file => $description) {
        if (file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
            if (!empty(trim($log_content))) {
                $lines = array_filter(explode("\n", $log_content));
                $count = count($lines);
                $email_count += $count;
                
                echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
                echo "<h6>{$log_file} ({$description}): {$count} entries</h6>";
                if ($count > 0) {
                    echo "<pre style='background: #fff; padding: 8px; border-radius: 4px; font-size: 11px; max-height: 100px; overflow-y: auto;'>";
                    echo htmlspecialchars(implode("\n", array_slice($lines, -5))); // Last 5 lines
                    echo "</pre>";
                }
                echo "</div>";
            }
        }
    }
    
    if ($email_count > 2) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>🚨 DUPLICATE NOTIFICATIONS DETECTED!</h5>";
        echo "<p><strong>Total email attempts:</strong> {$email_count}</p>";
        echo "<p><strong>Expected:</strong> 2 (1 client confirmation + 1 admin notification)</p>";
        echo "<p><strong>This indicates multiple email systems are running simultaneously!</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>✅ NO DUPLICATES DETECTED</h5>";
        echo "<p><strong>Total email attempts:</strong> {$email_count}</p>";
        echo "<p><strong>This is the expected behavior (1 client + 1 admin email).</strong></p>";
        echo "</div>";
    }
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Test Single Booking</h4>";
echo "<form method='POST'>";
echo "<p>This will submit a single test booking and monitor all email logs to detect duplicates.</p>";
echo "<button type='submit' name='test_single_booking' style='background: #17a2b8; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🧪 Test Single Booking for Duplicates</button>";
echo "</form>";
echo "</div>";

echo "<h3>5. Recommended Actions</h3>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🔧 To Fix Duplicate Notifications:</h4>";
echo "<ol>";
echo "<li><strong>Disable old email systems:</strong> Rename or move old booking handlers</li>";
echo "<li><strong>Use only booking_handler.php:</strong> Ensure it's the only active handler</li>";
echo "<li><strong>Use only real_smtp_email.php:</strong> Disable enhanced_smtp.php</li>";
echo "<li><strong>Clear old logs:</strong> Remove old email logs to avoid confusion</li>";
echo "<li><strong>Test with single booking:</strong> Use the test above to verify</li>";
echo "</ol>";

echo "<h4>🎯 Expected Email Flow:</h4>";
echo "<ul>";
echo "<li>📋 <strong>1 booking submission</strong> → booking_handler.php</li>";
echo "<li>📧 <strong>1 client email</strong> → via real_smtp_email.php</li>";
echo "<li>🔔 <strong>1 admin email</strong> → via real_smtp_email.php</li>";
echo "<li>📊 <strong>1 dashboard notification</strong> → admin_notifications table</li>";
echo "</ul>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Duplicate Notifications</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        h4, h5, h6 { color: inherit; margin-bottom: 10px; }
        p { line-height: 1.6; }
        ul, ol { line-height: 1.8; }
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
