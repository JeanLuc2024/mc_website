<?php
/**
 * Debug Duplicate Submission Issue
 * 
 * Comprehensive analysis to find the root cause
 */

echo "<h2>🔍 Debug Duplicate Submission Issue</h2>";

echo "<h3>1. JavaScript Event Listeners Analysis</h3>";

// Check if js/script.js has any remaining AJAX handlers
$script_js = file_get_contents('../js/script.js');

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📄 js/script.js Analysis:</h4>";

// Check for submit event listeners
$submit_listeners = substr_count($script_js, 'addEventListener(\'submit\'');
echo "<p><strong>Submit Event Listeners:</strong> {$submit_listeners}</p>";

// Check for fetch calls
$fetch_calls = substr_count($script_js, 'fetch(');
echo "<p><strong>Fetch Calls:</strong> {$fetch_calls}</p>";

// Check for AJAX handlers
$ajax_handlers = substr_count($script_js, 'AJAX');
echo "<p><strong>AJAX References:</strong> {$ajax_handlers}</p>";

if ($submit_listeners > 1) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
    echo "⚠️ <strong>ISSUE FOUND:</strong> Multiple submit listeners detected in js/script.js";
    echo "</div>";
}
echo "</div>";

// Check booking.html
$booking_html = file_get_contents('../booking.html');

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📄 booking.html Analysis:</h4>";

$html_submit_listeners = substr_count($booking_html, 'addEventListener(\'submit\'');
echo "<p><strong>Submit Event Listeners:</strong> {$html_submit_listeners}</p>";

$html_fetch_calls = substr_count($booking_html, 'fetch(');
echo "<p><strong>Fetch Calls:</strong> {$html_fetch_calls}</p>";

if ($html_submit_listeners != 1) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
    echo "⚠️ <strong>ISSUE FOUND:</strong> Expected exactly 1 submit listener, found {$html_submit_listeners}";
    echo "</div>";
}
echo "</div>";

echo "<h3>2. Server-Side Handler Analysis</h3>";

// Check for multiple booking handlers
$php_files = glob('booking*.php');

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📁 Booking Handler Files:</h4>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #e9ecef;'>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>File</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Size</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Modified</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Status</th>";
echo "</tr>";

foreach ($php_files as $file) {
    $size = filesize($file);
    $modified = date('Y-m-d H:i:s', filemtime($file));
    $is_disabled = strpos($file, '.disabled') !== false;
    
    $status = $is_disabled ? '🔒 Disabled' : '✅ Active';
    $color = $is_disabled ? '#6c757d' : '#28a745';
    
    if ($file === 'booking_handler.php') {
        $status = '🎯 Main Handler';
        $color = '#007bff';
    }
    
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$file}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . number_format($size) . " bytes</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$modified}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd; color: {$color};'>{$status}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<h3>3. Database Connection Test</h3>";

try {
    $host = 'localhost';
    $dbname = 'mc_website';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>✅ Database Connection: OK</h4>";
    
    // Check for recent duplicate entries
    $stmt = $pdo->query("
        SELECT name, email, event_date, event_time, created_at, COUNT(*) as count
        FROM bookings 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        GROUP BY name, email, event_date, event_time
        HAVING COUNT(*) > 1
        ORDER BY created_at DESC
    ");
    
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($duplicates)) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
        echo "<h5>🚨 Recent Duplicates Found:</h5>";
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr style='background: #e9ecef;'>";
        echo "<th style='padding: 6px; border: 1px solid #ddd;'>Name</th>";
        echo "<th style='padding: 6px; border: 1px solid #ddd;'>Email</th>";
        echo "<th style='padding: 6px; border: 1px solid #ddd;'>Event Date</th>";
        echo "<th style='padding: 6px; border: 1px solid #ddd;'>Duplicates</th>";
        echo "</tr>";
        
        foreach ($duplicates as $dup) {
            echo "<tr>";
            echo "<td style='padding: 6px; border: 1px solid #ddd;'>" . htmlspecialchars($dup['name']) . "</td>";
            echo "<td style='padding: 6px; border: 1px solid #ddd;'>" . htmlspecialchars($dup['email']) . "</td>";
            echo "<td style='padding: 6px; border: 1px solid #ddd;'>" . htmlspecialchars($dup['event_date']) . "</td>";
            echo "<td style='padding: 6px; border: 1px solid #ddd; color: #dc3545;'><strong>{$dup['count']} entries</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<p>✅ No recent duplicates found in database</p>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>❌ Database Connection Failed</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<h3>4. Live Submission Test</h3>";

if (isset($_POST['test_submission'])) {
    echo "<h4>🧪 Testing Live Submission...</h4>";
    
    // Enable detailed logging
    $log_file = 'submission_debug.log';
    
    function debug_log($message) {
        global $log_file;
        $timestamp = date('Y-m-d H:i:s.u');
        file_put_contents($log_file, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
    }
    
    // Clear previous log
    file_put_contents($log_file, "=== NEW TEST SESSION ===\n");
    
    debug_log("Starting submission test");
    
    // Count bookings before
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $before_count = $stmt->fetchColumn();
    debug_log("Bookings before test: {$before_count}");
    
    // Create test data
    $test_data = [
        'name' => 'Debug Test User',
        'email' => 'debugtest@example.com',
        'phone' => '0788123456',
        'event_date' => date('Y-m-d', strtotime('+7 days')),
        'event_time' => '14:00',
        'event_type' => 'Debug Test Event',
        'event_location' => 'Debug Test Location',
        'guests' => '25',
        'package' => 'Standard Package',
        'message' => 'This is a debug test submission to identify duplicate issue.',
        'terms' => 'on'
    ];
    
    debug_log("Test data prepared: " . json_encode($test_data));
    
    // Backup original POST data
    $original_post = $_POST;
    $original_method = $_SERVER['REQUEST_METHOD'];
    
    // Set test data
    $_POST = $test_data;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    debug_log("POST data set, calling booking_handler.php");
    
    // Capture output from booking handler
    ob_start();
    try {
        include 'booking_handler.php';
        $handler_output = ob_get_contents();
        debug_log("Handler executed successfully");
    } catch (Exception $e) {
        $handler_output = json_encode(['success' => false, 'message' => 'Handler error: ' . $e->getMessage()]);
        debug_log("Handler error: " . $e->getMessage());
    }
    ob_end_clean();
    
    // Restore original data
    $_POST = $original_post;
    $_SERVER['REQUEST_METHOD'] = $original_method;
    
    debug_log("Original POST data restored");
    
    // Count bookings after
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $after_count = $stmt->fetchColumn();
    $new_bookings = $after_count - $before_count;
    
    debug_log("Bookings after test: {$after_count}");
    debug_log("New bookings created: {$new_bookings}");
    
    echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h5>📊 Test Results:</h5>";
    echo "<p><strong>Bookings before:</strong> {$before_count}</p>";
    echo "<p><strong>Bookings after:</strong> {$after_count}</p>";
    echo "<p><strong>New bookings:</strong> {$new_bookings}</p>";
    
    if ($new_bookings == 1) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
        echo "🎉 <strong>SUCCESS!</strong> Exactly 1 booking created - No duplicates!";
        echo "</div>";
    } elseif ($new_bookings > 1) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
        echo "❌ <strong>DUPLICATE DETECTED!</strong> {$new_bookings} bookings created instead of 1";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
        echo "⚠️ <strong>NO BOOKING CREATED!</strong> Handler may have failed";
        echo "</div>";
    }
    
    // Show handler response
    $response = json_decode($handler_output, true);
    if ($response) {
        echo "<h5>📄 Handler Response:</h5>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
        echo htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT));
        echo "</pre>";
    }
    
    // Show debug log
    if (file_exists($log_file)) {
        echo "<h5>📝 Debug Log:</h5>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; max-height: 300px; overflow-y: auto;'>";
        echo htmlspecialchars(file_get_contents($log_file));
        echo "</pre>";
    }
    
    echo "</div>";
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Run Live Submission Test</h4>";
echo "<form method='POST'>";
echo "<p>This will simulate a real form submission and track exactly what happens:</p>";
echo "<ul>";
echo "<li>📋 Create test booking data</li>";
echo "<li>📊 Count database records before/after</li>";
echo "<li>🔍 Log every step of the process</li>";
echo "<li>✅ Identify if duplicates are created</li>";
echo "</ul>";
echo "<button type='submit' name='test_submission' style='background: #17a2b8; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🧪 Run Test</button>";
echo "</form>";
echo "</div>";

echo "<h3>5. Fix Recommendations</h3>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🔧 Recommended Actions:</h4>";

echo "<ol>";
echo "<li><strong>Clean js/script.js:</strong> Remove any remaining AJAX handler code</li>";
echo "<li><strong>Disable old handlers:</strong> Rename unused booking*.php files to .disabled</li>";
echo "<li><strong>Add submission protection:</strong> Prevent double-clicks on submit button</li>";
echo "<li><strong>Add server-side deduplication:</strong> Check for recent identical submissions</li>";
echo "<li><strong>Test thoroughly:</strong> Submit real bookings and verify single entries</li>";
echo "</ol>";

echo "<p><strong>Priority:</strong> Run the live test above to identify the exact cause!</p>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Duplicate Submission Issue</title>
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
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
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
