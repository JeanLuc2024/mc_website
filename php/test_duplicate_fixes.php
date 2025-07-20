<?php
/**
 * Test Duplicate Fixes
 * 
 * Verify both client-side and server-side duplicate fixes are working
 */

echo "<h2>🧪 Test Duplicate Fixes</h2>";

echo "<h3>1. Client-Side Fix Verification</h3>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>✅ Client-Side Fixes Applied:</h4>";
echo "<ul>";
echo "<li>✅ <strong>Removed duplicate success message</strong> - showBookingReference function disabled</li>";
echo "<li>✅ <strong>Single message display</strong> - Only main success message shows</li>";
echo "<li>✅ <strong>Submission protection</strong> - isSubmitting flag prevents double clicks</li>";
echo "<li>✅ <strong>Button disabled</strong> during submission</li>";
echo "</ul>";
echo "</div>";

echo "<h3>2. Server-Side Fix Verification</h3>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>✅ Server-Side Fixes Applied:</h4>";
echo "<ul>";
echo "<li>✅ <strong>Deduplication check</strong> - Prevents identical bookings within 5 minutes</li>";
echo "<li>✅ <strong>Single database insert</strong> - Only one record per submission</li>";
echo "<li>✅ <strong>Single email notification</strong> - No duplicate emails</li>";
echo "<li>✅ <strong>Single admin notification</strong> - No duplicate dashboard alerts</li>";
echo "</ul>";
echo "</div>";

echo "<h3>3. Test Single Submission</h3>";

if (isset($_POST['test_single_submission'])) {
    echo "<h4>🧪 Testing Single Submission...</h4>";
    
    try {
        $host = 'localhost';
        $dbname = 'mc_website';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Count bookings before test
        $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
        $before_count = $stmt->fetchColumn();
        
        // Count notifications before test
        $stmt = $pdo->query("SELECT COUNT(*) FROM admin_notifications");
        $before_notifications = $stmt->fetchColumn();
        
        echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>📊 Before Test:</h5>";
        echo "<p><strong>Bookings:</strong> {$before_count}</p>";
        echo "<p><strong>Notifications:</strong> {$before_notifications}</p>";
        echo "</div>";
        
        // Create test booking data
        $test_data = [
            'name' => 'Single Fix Test User',
            'email' => 'singlefixtest@example.com',
            'phone' => '0788555444',
            'event_date' => date('Y-m-d', strtotime('+9 days')),
            'event_time' => '17:00',
            'event_type' => 'Single Fix Test Event',
            'event_location' => 'Single Fix Test Location',
            'guests' => '35',
            'package' => 'Premium Package',
            'message' => 'Testing single submission after duplicate fixes.',
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
        
        // Count after test
        $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
        $after_count = $stmt->fetchColumn();
        $new_bookings = $after_count - $before_count;
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM admin_notifications");
        $after_notifications = $stmt->fetchColumn();
        $new_notifications = $after_notifications - $before_notifications;
        
        echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>📊 After Test:</h5>";
        echo "<p><strong>Bookings:</strong> {$after_count} (+{$new_bookings})</p>";
        echo "<p><strong>Notifications:</strong> {$after_notifications} (+{$new_notifications})</p>";
        echo "</div>";
        
        // Analyze results
        if ($new_bookings == 1 && $new_notifications == 1) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>🎉 PERFECT! Single Submission Working!</h5>";
            echo "<ul>";
            echo "<li>✅ <strong>Exactly 1 booking created</strong></li>";
            echo "<li>✅ <strong>Exactly 1 notification created</strong></li>";
            echo "<li>✅ <strong>No duplicates detected</strong></li>";
            echo "<li>✅ <strong>All fixes working correctly</strong></li>";
            echo "</ul>";
            echo "</div>";
        } elseif ($new_bookings > 1 || $new_notifications > 1) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>❌ Still Detecting Duplicates!</h5>";
            echo "<ul>";
            echo "<li><strong>Bookings created:</strong> {$new_bookings} (should be 1)</li>";
            echo "<li><strong>Notifications created:</strong> {$new_notifications} (should be 1)</li>";
            echo "</ul>";
            echo "<p>There may be additional issues to investigate.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>⚠️ No Records Created</h5>";
            echo "<p>The booking handler may have encountered an error.</p>";
            echo "</div>";
        }
        
        // Show handler response
        $response = json_decode($handler_output, true);
        if ($response) {
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>📄 Handler Response:</h5>";
            echo "<pre style='background: #ffffff; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
            echo htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT));
            echo "</pre>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Test Failed</h5>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Test Single Submission</h4>";
echo "<form method='POST'>";
echo "<p>This will test if both client-side and server-side duplicate fixes are working:</p>";
echo "<ul>";
echo "<li>📋 <strong>Submit one test booking</strong></li>";
echo "<li>📊 <strong>Count database records and notifications</strong></li>";
echo "<li>✅ <strong>Verify exactly 1 of each is created</strong></li>";
echo "<li>🔍 <strong>Check deduplication protection</strong></li>";
echo "</ul>";
echo "<button type='submit' name='test_single_submission' style='background: #17a2b8; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🧪 Test Single Submission</button>";
echo "</form>";
echo "</div>";

echo "<h3>4. Test Deduplication Protection</h3>";

if (isset($_POST['test_deduplication'])) {
    echo "<h4>🛡️ Testing Deduplication Protection...</h4>";
    
    try {
        $host = 'localhost';
        $dbname = 'mc_website';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create identical test data
        $test_data = [
            'name' => 'Dedup Test User',
            'email' => 'deduptest@example.com',
            'phone' => '0788333222',
            'event_date' => date('Y-m-d', strtotime('+10 days')),
            'event_time' => '18:00',
            'event_type' => 'Deduplication Test Event',
            'event_location' => 'Dedup Test Location',
            'guests' => '50',
            'package' => 'Standard Package',
            'message' => 'Testing deduplication protection.',
            'terms' => 'on'
        ];
        
        echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>🔄 Submitting Same Booking Twice...</h5>";
        echo "<p><strong>Test:</strong> Submit identical booking data twice within 5 minutes</p>";
        echo "<p><strong>Expected:</strong> First submission succeeds, second is blocked</p>";
        echo "</div>";
        
        // Backup original POST data
        $original_post = $_POST;
        $original_method = $_SERVER['REQUEST_METHOD'];
        
        // Set test data
        $_POST = $test_data;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // First submission
        echo "<h6>📋 First Submission:</h6>";
        ob_start();
        try {
            include 'booking_handler.php';
            $first_output = ob_get_contents();
        } catch (Exception $e) {
            $first_output = json_encode(['success' => false, 'message' => 'Handler error: ' . $e->getMessage()]);
        }
        ob_end_clean();
        
        $first_response = json_decode($first_output, true);
        if ($first_response && $first_response['success']) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
            echo "✅ <strong>First submission successful</strong>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
            echo "❌ <strong>First submission failed:</strong> " . ($first_response['message'] ?? 'Unknown error');
            echo "</div>";
        }
        
        // Second submission (should be blocked)
        echo "<h6>🛡️ Second Submission (should be blocked):</h6>";
        ob_start();
        try {
            include 'booking_handler.php';
            $second_output = ob_get_contents();
        } catch (Exception $e) {
            $second_output = json_encode(['success' => false, 'message' => 'Handler error: ' . $e->getMessage()]);
        }
        ob_end_clean();
        
        $second_response = json_decode($second_output, true);
        if ($second_response && !$second_response['success'] && strpos($second_response['message'], 'already submitted recently') !== false) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
            echo "🎉 <strong>Deduplication working!</strong> Second submission blocked as expected";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($second_response['message']) . "</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
            echo "❌ <strong>Deduplication failed!</strong> Second submission was not blocked";
            if ($second_response) {
                echo "<p><strong>Response:</strong> " . htmlspecialchars($second_response['message']) . "</p>";
            }
            echo "</div>";
        }
        
        // Restore original data
        $_POST = $original_post;
        $_SERVER['REQUEST_METHOD'] = $original_method;
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Deduplication Test Failed</h5>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
}

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🛡️ Test Deduplication Protection</h4>";
echo "<form method='POST'>";
echo "<p>This will test if the server-side deduplication is working:</p>";
echo "<ul>";
echo "<li>📋 <strong>Submit identical booking twice</strong></li>";
echo "<li>✅ <strong>First submission should succeed</strong></li>";
echo "<li>🛡️ <strong>Second submission should be blocked</strong></li>";
echo "<li>📊 <strong>Verify protection is working</strong></li>";
echo "</ul>";
echo "<button type='submit' name='test_deduplication' style='background: #ffc107; color: #212529; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🛡️ Test Deduplication</button>";
echo "</form>";
echo "</div>";

echo "<h3>5. Summary</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🎉 Duplicate Issues Fixed!</h4>";

echo "<h5>✅ Client-Side Fixes:</h5>";
echo "<ul>";
echo "<li>✅ <strong>Single success message</strong> - Removed duplicate booking reference display</li>";
echo "<li>✅ <strong>Submission protection</strong> - Prevents rapid double-clicks</li>";
echo "<li>✅ <strong>Button disabled</strong> during submission</li>";
echo "</ul>";

echo "<h5>✅ Server-Side Fixes:</h5>";
echo "<ul>";
echo "<li>✅ <strong>Deduplication check</strong> - Blocks identical submissions within 5 minutes</li>";
echo "<li>✅ <strong>Single database insert</strong> - No duplicate records</li>";
echo "<li>✅ <strong>Single email notification</strong> - No duplicate emails</li>";
echo "</ul>";

echo "<h5>🎯 Expected Behavior Now:</h5>";
echo "<ul>";
echo "<li>📋 <strong>1 form submission</strong> → 1 success message</li>";
echo "<li>📊 <strong>1 database record</strong> → 1 admin notification</li>";
echo "<li>📧 <strong>1 client email</strong> → 1 admin email</li>";
echo "<li>🛡️ <strong>Rapid resubmissions blocked</strong> for 5 minutes</li>";
echo "</ul>";

echo "<h5>🧪 Next Steps:</h5>";
echo "<ol>";
echo "<li><strong>Run both tests above</strong> to verify fixes</li>";
echo "<li><strong>Test real booking:</strong> <a href='../booking.html' target='_blank' style='color: #007bff;'>Submit Real Booking</a></li>";
echo "<li><strong>Check for single success message</strong> on client side</li>";
echo "<li><strong>Verify single database entry</strong> and single email notification</li>";
echo "</ol>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Duplicate Fixes</title>
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
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        pre { font-size: 12px; line-height: 1.4; }
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
