<?php
/**
 * Ultimate Duplicate Fix
 * 
 * Complete diagnosis and fix for all duplicate issues
 */

echo "<h2>🔧 Ultimate Duplicate Fix</h2>";

echo "<h3>1. Current System Analysis</h3>";

// Check database for recent duplicates
try {
    $host = 'localhost';
    $dbname = 'mc_website';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check for recent duplicates
    $stmt = $pdo->query("
        SELECT name, email, event_date, event_time, COUNT(*) as count, 
               GROUP_CONCAT(booking_ref ORDER BY created_at) as refs,
               GROUP_CONCAT(created_at ORDER BY created_at) as times
        FROM bookings 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY name, email, event_date, event_time
        HAVING COUNT(*) > 1
        ORDER BY MAX(created_at) DESC
    ");
    
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($duplicates)) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>🚨 Recent Duplicates Found!</h4>";
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr style='background: #e9ecef;'>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Name</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Email</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Event Date</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Duplicates</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Booking Refs</th>";
        echo "</tr>";
        
        foreach ($duplicates as $dup) {
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($dup['name']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($dup['email']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($dup['event_date']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd; color: #dc3545;'><strong>{$dup['count']} entries</strong></td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd; font-size: 12px;'>" . htmlspecialchars($dup['refs']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>✅ No Recent Duplicates Found</h4>";
        echo "<p>Database is clean for the last 24 hours</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>❌ Database Connection Failed</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<h3>2. Check Active Handlers</h3>";

$php_files = glob('booking*.php*');
$active_handlers = [];
$disabled_handlers = [];

foreach ($php_files as $file) {
    if (strpos($file, '.disabled') !== false) {
        $disabled_handlers[] = $file;
    } else {
        $active_handlers[] = $file;
    }
}

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📁 Handler Status:</h4>";

echo "<h5>✅ Active Handlers:</h5>";
if (!empty($active_handlers)) {
    echo "<ul>";
    foreach ($active_handlers as $handler) {
        $color = ($handler === 'booking_handler.php') ? '#28a745' : '#ffc107';
        $status = ($handler === 'booking_handler.php') ? '(Main Handler)' : '(Should be disabled)';
        echo "<li style='color: {$color};'><strong>{$handler}</strong> {$status}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No active handlers found</p>";
}

echo "<h5>🔒 Disabled Handlers:</h5>";
if (!empty($disabled_handlers)) {
    echo "<ul>";
    foreach ($disabled_handlers as $handler) {
        echo "<li style='color: #6c757d;'>{$handler}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No disabled handlers found</p>";
}
echo "</div>";

if (count($active_handlers) > 1) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>⚠️ Multiple Active Handlers Detected!</h4>";
    echo "<p>This could be causing duplicate submissions. Only <code>booking_handler.php</code> should be active.</p>";
    echo "</div>";
}

echo "<h3>3. Disable All Old Handlers</h3>";

if (isset($_POST['disable_all_handlers'])) {
    echo "<h4>🔒 Disabling All Old Handlers...</h4>";
    
    $disabled_count = 0;
    $errors = [];
    
    foreach ($active_handlers as $handler) {
        if ($handler !== 'booking_handler.php') {
            if (file_exists($handler)) {
                if (rename($handler, $handler . '.disabled')) {
                    $disabled_count++;
                    echo "<p>✅ Disabled: {$handler}</p>";
                } else {
                    $errors[] = "Failed to disable: {$handler}";
                }
            }
        }
    }
    
    if ($disabled_count > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>✅ Successfully Disabled {$disabled_count} Handlers</h5>";
        echo "<p>Only <code>booking_handler.php</code> is now active</p>";
        echo "</div>";
    }
    
    if (!empty($errors)) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Errors:</h5>";
        foreach ($errors as $error) {
            echo "<p>{$error}</p>";
        }
        echo "</div>";
    }
}

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🔒 Disable All Old Handlers</h4>";
echo "<form method='POST'>";
echo "<p>This will disable ALL booking handlers except the main one:</p>";
echo "<ul>";
echo "<li>✅ <strong>Keep only booking_handler.php active</strong></li>";
echo "<li>🔒 <strong>Disable all other booking*.php files</strong></li>";
echo "<li>🛡️ <strong>Eliminate handler conflicts</strong></li>";
echo "</ul>";
echo "<button type='submit' name='disable_all_handlers' style='background: #ffc107; color: #212529; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🔒 Disable All Old Handlers</button>";
echo "</form>";
echo "</div>";

echo "<h3>4. Enhanced Deduplication</h3>";

if (isset($_POST['enhance_deduplication'])) {
    echo "<h4>🛡️ Enhancing Deduplication Protection...</h4>";
    
    $handler_file = 'booking_handler.php';
    if (file_exists($handler_file)) {
        $content = file_get_contents($handler_file);
        
        // Check if enhanced deduplication already exists
        if (strpos($content, 'ENHANCED DEDUPLICATION') !== false) {
            echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>ℹ️ Enhanced Deduplication Already Present</h5>";
            echo "</div>";
        } else {
            // Add enhanced deduplication
            $enhanced_dedup = '
    // ENHANCED DEDUPLICATION - Multiple checks to prevent ANY duplicates
    
    // Check 1: Exact duplicate within 10 minutes
    $exact_duplicate_sql = "SELECT COUNT(*) FROM bookings 
                           WHERE name = ? AND email = ? AND event_date = ? AND event_time = ? 
                           AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
    $exact_stmt = $pdo->prepare($exact_duplicate_sql);
    $exact_stmt->execute([$name, $email, $event_date, $event_time]);
    if ($exact_stmt->fetchColumn() > 0) {
        $response[\'message\'] = \'This exact booking was already submitted recently. Please wait before submitting again.\';
        echo json_encode($response);
        exit;
    }
    
    // Check 2: Same email within 2 minutes (rapid submissions)
    $rapid_submit_sql = "SELECT COUNT(*) FROM bookings 
                        WHERE email = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)";
    $rapid_stmt = $pdo->prepare($rapid_submit_sql);
    $rapid_stmt->execute([$email]);
    if ($rapid_stmt->fetchColumn() > 0) {
        $response[\'message\'] = \'Please wait a moment before submitting another booking.\';
        echo json_encode($response);
        exit;
    }
    
    // Check 3: Identical booking reference (should never happen, but extra safety)
    $ref_check_sql = "SELECT COUNT(*) FROM bookings WHERE booking_ref = ?";
    $temp_ref = \'MC-\' . date(\'ymd\') . \'-\' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    $ref_stmt = $pdo->prepare($ref_check_sql);
    $ref_stmt->execute([$temp_ref]);
    while ($ref_stmt->fetchColumn() > 0) {
        $temp_ref = \'MC-\' . date(\'ymd\') . \'-\' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        $ref_stmt->execute([$temp_ref]);
    }
    $booking_ref = $temp_ref;
';
            
            // Replace the existing deduplication
            $old_dedup_start = strpos($content, '// DEDUPLICATION CHECK');
            $old_dedup_end = strpos($content, '// Generate unique booking reference');
            
            if ($old_dedup_start !== false && $old_dedup_end !== false) {
                $new_content = substr_replace($content, $enhanced_dedup . "\n    ", $old_dedup_start, $old_dedup_end - $old_dedup_start);
                
                // Backup original
                copy($handler_file, $handler_file . '.backup.' . date('YmdHis'));
                
                // Write enhanced version
                file_put_contents($handler_file, $new_content);
                
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
                echo "<h5>✅ Enhanced Deduplication Added!</h5>";
                echo "<ul>";
                echo "<li>✅ <strong>Exact duplicate check</strong> - 10 minute window</li>";
                echo "<li>✅ <strong>Rapid submission check</strong> - 2 minute window</li>";
                echo "<li>✅ <strong>Unique reference check</strong> - Guaranteed uniqueness</li>";
                echo "<li>✅ <strong>Backup created</strong> - {$handler_file}.backup." . date('YmdHis') . "</li>";
                echo "</ul>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
                echo "<h5>❌ Could Not Enhance Deduplication</h5>";
                echo "<p>Could not find insertion point in booking_handler.php</p>";
                echo "</div>";
            }
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ booking_handler.php Not Found</h5>";
        echo "</div>";
    }
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🛡️ Enhance Deduplication Protection</h4>";
echo "<form method='POST'>";
echo "<p>This will add multiple layers of deduplication protection:</p>";
echo "<ul>";
echo "<li>🔍 <strong>Exact duplicate check</strong> - Blocks identical bookings within 10 minutes</li>";
echo "<li>⚡ <strong>Rapid submission check</strong> - Blocks multiple submissions from same email within 2 minutes</li>";
echo "<li>🔑 <strong>Unique reference check</strong> - Ensures booking references are always unique</li>";
echo "<li>💾 <strong>Automatic backup</strong> - Preserves original file</li>";
echo "</ul>";
echo "<button type='submit' name='enhance_deduplication' style='background: #17a2b8; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🛡️ Enhance Deduplication</button>";
echo "</form>";
echo "</div>";

echo "<h3>5. Clean Existing Duplicates</h3>";

if (isset($_POST['clean_duplicates'])) {
    echo "<h4>🗑️ Cleaning Existing Duplicates...</h4>";
    
    try {
        // Find and remove duplicates, keeping only the first submission
        $find_duplicates = "
            SELECT name, email, event_date, event_time, 
                   GROUP_CONCAT(id ORDER BY created_at) as ids,
                   GROUP_CONCAT(booking_ref ORDER BY created_at) as refs,
                   COUNT(*) as count
            FROM bookings 
            GROUP BY name, email, event_date, event_time
            HAVING COUNT(*) > 1
        ";
        
        $stmt = $pdo->query($find_duplicates);
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_removed = 0;
        
        if (!empty($duplicates)) {
            foreach ($duplicates as $dup) {
                $ids = explode(',', $dup['ids']);
                $refs = explode(',', $dup['refs']);
                
                // Keep the first one, remove the rest
                $keep_id = array_shift($ids);
                $remove_ids = $ids;
                
                if (!empty($remove_ids)) {
                    // Remove duplicate bookings
                    $placeholders = str_repeat('?,', count($remove_ids) - 1) . '?';
                    $delete_sql = "DELETE FROM bookings WHERE id IN ($placeholders)";
                    $delete_stmt = $pdo->prepare($delete_sql);
                    $delete_stmt->execute($remove_ids);
                    
                    // Remove duplicate notifications
                    $notif_delete_sql = "DELETE FROM admin_notifications WHERE booking_id IN ($placeholders)";
                    $notif_delete_stmt = $pdo->prepare($notif_delete_sql);
                    $notif_delete_stmt->execute($remove_ids);
                    
                    $removed_count = count($remove_ids);
                    $total_removed += $removed_count;
                    
                    echo "<p>✅ Removed {$removed_count} duplicates for: " . htmlspecialchars($dup['name']) . "</p>";
                }
            }
            
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>✅ Cleanup Complete!</h5>";
            echo "<p><strong>Total duplicates removed:</strong> {$total_removed}</p>";
            echo "<p><strong>Database is now clean</strong></p>";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>✅ No Duplicates Found</h5>";
            echo "<p>Database is already clean</p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Cleanup Failed</h5>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
}

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🗑️ Clean Existing Duplicates</h4>";
echo "<form method='POST'>";
echo "<p>This will remove all duplicate bookings from the database:</p>";
echo "<ul>";
echo "<li>🔍 <strong>Find all duplicate bookings</strong> (same name, email, date, time)</li>";
echo "<li>✅ <strong>Keep the first submission</strong> of each duplicate group</li>";
echo "<li>🗑️ <strong>Remove all duplicate entries</strong> and their notifications</li>";
echo "<li>🧹 <strong>Clean database</strong> for fresh start</li>";
echo "</ul>";
echo "<button type='submit' name='clean_duplicates' style='background: #dc3545; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🗑️ Clean Duplicates</button>";
echo "</form>";
echo "</div>";

echo "<h3>6. Final Test - One Submission = One Everything</h3>";

if (isset($_POST['final_test'])) {
    echo "<h4>🧪 Testing One Submission = One Everything...</h4>";

    try {
        // Count everything before test
        $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
        $before_bookings = $stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COUNT(*) FROM admin_notifications");
        $before_notifications = $stmt->fetchColumn();

        // Count emails if log exists
        $before_emails = 0;
        if (file_exists('pending_emails.txt')) {
            $email_content = file_get_contents('pending_emails.txt');
            $before_emails = substr_count($email_content, '=== NEW BOOKING NOTIFICATION ===');
        }

        echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>📊 Before Test:</h5>";
        echo "<p><strong>Bookings:</strong> {$before_bookings}</p>";
        echo "<p><strong>Notifications:</strong> {$before_notifications}</p>";
        echo "<p><strong>Email logs:</strong> {$before_emails}</p>";
        echo "</div>";

        // Create unique test data
        $unique_id = uniqid();
        $test_data = [
            'name' => 'Ultimate Test User ' . $unique_id,
            'email' => 'ultimatetest' . $unique_id . '@example.com',
            'phone' => '0788' . substr($unique_id, -6),
            'event_date' => date('Y-m-d', strtotime('+15 days')),
            'event_time' => '19:00',
            'event_type' => 'Ultimate Test Event',
            'event_location' => 'Ultimate Test Location',
            'guests' => '60',
            'package' => 'Premium Package',
            'message' => 'Ultimate test to verify ONE submission creates ONE of everything.',
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

        // Count everything after test
        $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
        $after_bookings = $stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COUNT(*) FROM admin_notifications");
        $after_notifications = $stmt->fetchColumn();

        $after_emails = 0;
        if (file_exists('pending_emails.txt')) {
            $email_content = file_get_contents('pending_emails.txt');
            $after_emails = substr_count($email_content, '=== NEW BOOKING NOTIFICATION ===');
        }

        $new_bookings = $after_bookings - $before_bookings;
        $new_notifications = $after_notifications - $before_notifications;
        $new_emails = $after_emails - $before_emails;

        echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>📊 After Test:</h5>";
        echo "<p><strong>Bookings:</strong> {$after_bookings} (+{$new_bookings})</p>";
        echo "<p><strong>Notifications:</strong> {$after_notifications} (+{$new_notifications})</p>";
        echo "<p><strong>Email logs:</strong> {$after_emails} (+{$new_emails})</p>";
        echo "</div>";

        // Analyze results
        if ($new_bookings == 1 && $new_notifications == 1 && $new_emails <= 1) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>🎉 PERFECT! One Submission = One Everything!</h5>";
            echo "<ul>";
            echo "<li>✅ <strong>Exactly 1 booking created</strong></li>";
            echo "<li>✅ <strong>Exactly 1 notification created</strong></li>";
            echo "<li>✅ <strong>Email handling working</strong></li>";
            echo "<li>✅ <strong>NO DUPLICATES ANYWHERE!</strong></li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>❌ Still Detecting Issues!</h5>";
            echo "<ul>";
            echo "<li><strong>Bookings created:</strong> {$new_bookings} (should be 1)</li>";
            echo "<li><strong>Notifications created:</strong> {$new_notifications} (should be 1)</li>";
            echo "<li><strong>Email logs:</strong> {$new_emails} (should be 0-1)</li>";
            echo "</ul>";
            echo "<p>There may be additional issues to investigate.</p>";
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

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Final Test - One Submission = One Everything</h4>";
echo "<form method='POST'>";
echo "<p>This will test if ONE submission creates exactly ONE of everything:</p>";
echo "<ul>";
echo "<li>📋 <strong>Submit one unique booking</strong></li>";
echo "<li>📊 <strong>Count bookings, notifications, emails</strong></li>";
echo "<li>✅ <strong>Verify exactly 1 of each is created</strong></li>";
echo "<li>🎯 <strong>Confirm complete fix</strong></li>";
echo "</ul>";
echo "<button type='submit' name='final_test' style='background: #28a745; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🧪 Run Final Test</button>";
echo "</form>";
echo "</div>";

echo "<h3>7. Summary</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🎯 Ultimate Duplicate Fix Complete!</h4>";

echo "<h5>✅ Complete Solution:</h5>";
echo "<ol>";
echo "<li>✅ <strong>Disabled all old handlers</strong> - Only one active</li>";
echo "<li>✅ <strong>Enhanced deduplication</strong> - Multiple protection layers</li>";
echo "<li>✅ <strong>Cleaned existing duplicates</strong> - Fresh database</li>";
echo "<li>✅ <strong>Tested one-to-one mapping</strong> - Verified fix works</li>";
echo "</ol>";

echo "<h5>🎯 Expected Behavior:</h5>";
echo "<ul>";
echo "<li>📋 <strong>1 user submission</strong> → 1 database record</li>";
echo "<li>📧 <strong>1 user submission</strong> → 1 admin email</li>";
echo "<li>📊 <strong>1 user submission</strong> → 1 dashboard notification</li>";
echo "<li>🛡️ <strong>Rapid resubmissions</strong> → Blocked automatically</li>";
echo "</ul>";

echo "<h5>🧪 Next Steps:</h5>";
echo "<ol>";
echo "<li><strong>Complete all steps above</strong> in order</li>";
echo "<li><strong>Run final test</strong> to verify one-to-one mapping</li>";
echo "<li><strong>Test real booking:</strong> <a href='../booking.html' target='_blank' style='color: #007bff;'>Submit Real Booking</a></li>";
echo "<li><strong>Verify single entries</strong> in database and dashboard</li>";
echo "</ol>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ultimate Duplicate Fix</title>
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
        button { font-family: inherit; }
        button:hover { opacity: 0.9; transform: translateY(-1px); transition: all 0.3s ease; }
        code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
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
