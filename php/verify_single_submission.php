<?php
/**
 * Verify Single Submission
 * 
 * Simple verification that one submission creates one record
 */

echo "<h2>✅ Verify Single Submission</h2>";

echo "<h3>1. Quick System Check</h3>";

// Check database connection
try {
    $host = 'localhost';
    $dbname = 'mc_website';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>✅ Database Connection: OK</h4>";
    
    // Check current counts
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $total_bookings = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin_notifications");
    $total_notifications = $stmt->fetchColumn();
    
    echo "<p><strong>Current bookings:</strong> {$total_bookings}</p>";
    echo "<p><strong>Current notifications:</strong> {$total_notifications}</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>❌ Database Connection Failed</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    exit;
}

// Check active handlers
$active_handlers = [];
$php_files = glob('booking*.php');
foreach ($php_files as $file) {
    if (strpos($file, '.disabled') === false && strpos($file, '.backup') === false) {
        $active_handlers[] = $file;
    }
}

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📁 Active Handlers:</h4>";
if (count($active_handlers) == 1 && $active_handlers[0] == 'booking_handler.php') {
    echo "<p style='color: #28a745;'>✅ <strong>Perfect!</strong> Only booking_handler.php is active</p>";
} else {
    echo "<p style='color: #ffc107;'>⚠️ <strong>Warning:</strong> Multiple handlers detected:</p>";
    echo "<ul>";
    foreach ($active_handlers as $handler) {
        echo "<li>{$handler}</li>";
    }
    echo "</ul>";
}
echo "</div>";

echo "<h3>2. Test Single Submission</h3>";

if (isset($_POST['test_submission'])) {
    echo "<h4>🧪 Testing Single Submission...</h4>";
    
    // Record before counts
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $before_bookings = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin_notifications");
    $before_notifications = $stmt->fetchColumn();
    
    echo "<div style='background: #e8f4f8; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
    echo "<p><strong>Before:</strong> {$before_bookings} bookings, {$before_notifications} notifications</p>";
    echo "</div>";
    
    // Create unique test data
    $unique_id = uniqid();
    $test_data = [
        'name' => 'Verify Test User ' . substr($unique_id, -6),
        'email' => 'verifytest' . substr($unique_id, -6) . '@example.com',
        'phone' => '0788' . substr($unique_id, -6),
        'event_date' => date('Y-m-d', strtotime('+20 days')),
        'event_time' => '20:00',
        'event_type' => 'Verification Test Event',
        'event_location' => 'Verification Test Location',
        'guests' => '45',
        'package' => 'Standard Package',
        'message' => 'Verification test for single submission.',
        'terms' => 'on'
    ];
    
    // Backup original POST data
    $original_post = $_POST;
    $original_method = $_SERVER['REQUEST_METHOD'];
    
    // Set test data
    $_POST = $test_data;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Submit through booking handler
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
    
    // Record after counts
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $after_bookings = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin_notifications");
    $after_notifications = $stmt->fetchColumn();
    
    $new_bookings = $after_bookings - $before_bookings;
    $new_notifications = $after_notifications - $before_notifications;
    
    echo "<div style='background: #e8f4f8; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
    echo "<p><strong>After:</strong> {$after_bookings} bookings (+{$new_bookings}), {$after_notifications} notifications (+{$new_notifications})</p>";
    echo "</div>";
    
    // Analyze results
    if ($new_bookings == 1 && $new_notifications == 1) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>🎉 SUCCESS! Perfect Single Submission!</h5>";
        echo "<ul>";
        echo "<li>✅ <strong>Exactly 1 booking created</strong></li>";
        echo "<li>✅ <strong>Exactly 1 notification created</strong></li>";
        echo "<li>✅ <strong>No duplicates detected</strong></li>";
        echo "</ul>";
        
        // Parse response
        $response = json_decode($handler_output, true);
        if ($response && $response['success']) {
            echo "<p><strong>Booking Reference:</strong> " . htmlspecialchars($response['booking_ref']) . "</p>";
        }
        echo "</div>";
        
    } elseif ($new_bookings > 1 || $new_notifications > 1) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Duplicates Still Detected!</h5>";
        echo "<ul>";
        echo "<li><strong>Bookings created:</strong> {$new_bookings} (should be 1)</li>";
        echo "<li><strong>Notifications created:</strong> {$new_notifications} (should be 1)</li>";
        echo "</ul>";
        echo "<p><strong>Action needed:</strong> Run the Ultimate Duplicate Fix script</p>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>⚠️ No Records Created</h5>";
        echo "<p>The booking handler may have encountered an error.</p>";
        
        $response = json_decode($handler_output, true);
        if ($response) {
            echo "<p><strong>Response:</strong> " . htmlspecialchars($response['message']) . "</p>";
        }
        echo "</div>";
    }
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Test Single Submission</h4>";
echo "<form method='POST'>";
echo "<p>This will test if one submission creates exactly one record:</p>";
echo "<ul>";
echo "<li>📋 <strong>Submit unique test booking</strong></li>";
echo "<li>📊 <strong>Count records before and after</strong></li>";
echo "<li>✅ <strong>Verify exactly 1 booking and 1 notification created</strong></li>";
echo "</ul>";
echo "<button type='submit' name='test_submission' style='background: #17a2b8; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🧪 Test Single Submission</button>";
echo "</form>";
echo "</div>";

echo "<h3>3. Quick Duplicate Check</h3>";

// Check for recent duplicates
$stmt = $pdo->query("
    SELECT name, email, event_date, COUNT(*) as count
    FROM bookings 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    GROUP BY name, email, event_date
    HAVING COUNT(*) > 1
    ORDER BY MAX(created_at) DESC
    LIMIT 5
");

$recent_duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($recent_duplicates)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>⚠️ Recent Duplicates Found</h4>";
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='padding: 8px; border: 1px solid #ddd;'>Name</th>";
    echo "<th style='padding: 8px; border: 1px solid #ddd;'>Email</th>";
    echo "<th style='padding: 8px; border: 1px solid #ddd;'>Event Date</th>";
    echo "<th style='padding: 8px; border: 1px solid #ddd;'>Count</th>";
    echo "</tr>";
    
    foreach ($recent_duplicates as $dup) {
        echo "<tr>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($dup['name']) . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($dup['email']) . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($dup['event_date']) . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd; color: #dc3545;'><strong>{$dup['count']}</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Action needed:</strong> <a href='ultimate_duplicate_fix.php' style='color: #007bff;'>Run Ultimate Duplicate Fix</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>✅ No Recent Duplicates</h4>";
    echo "<p>Database is clean for the last hour</p>";
    echo "</div>";
}

echo "<h3>4. Summary</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🎯 Single Submission Verification</h4>";

echo "<h5>✅ What This Verifies:</h5>";
echo "<ul>";
echo "<li>✅ <strong>One submission</strong> → One database record</li>";
echo "<li>✅ <strong>One submission</strong> → One admin notification</li>";
echo "<li>✅ <strong>No duplicate handlers</strong> active</li>";
echo "<li>✅ <strong>No recent duplicates</strong> in database</li>";
echo "</ul>";

echo "<h5>🧪 Next Steps:</h5>";
echo "<ol>";
echo "<li><strong>Run the test above</strong> to verify single submission</li>";
echo "<li><strong>If duplicates found:</strong> <a href='ultimate_duplicate_fix.php' style='color: #007bff;'>Run Ultimate Fix</a></li>";
echo "<li><strong>Test real booking:</strong> <a href='../booking.html' target='_blank' style='color: #007bff;'>Submit Real Booking</a></li>";
echo "<li><strong>Verify single entries</strong> in database and admin dashboard</li>";
echo "</ol>";

echo "<h5>🎯 Expected Result:</h5>";
echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
echo "<p><strong>User submits 1 booking → System creates exactly 1 of everything</strong></p>";
echo "<ul>";
echo "<li>📋 1 database record</li>";
echo "<li>📊 1 admin notification</li>";
echo "<li>📧 1 email to admin</li>";
echo "<li>📧 1 confirmation to client</li>";
echo "</ul>";
echo "</div>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Single Submission</title>
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
