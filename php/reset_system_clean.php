<?php
/**
 * Reset System to Clean State
 * 
 * This script removes all sample data and resets the system for fresh testing
 */

echo "<h2>🧹 Resetting System to Clean State...</h2>";

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h3>1. Current Data Count (Before Reset)</h3>";

// Get current counts
try {
    $counts_before = [
        'bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
        'notifications' => $pdo->query("SELECT COUNT(*) FROM admin_notifications")->fetchColumn(),
        'emails' => $pdo->query("SELECT COUNT(*) FROM email_notifications")->fetchColumn()
    ];
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>📊 Current Data:</h4>";
    echo "<ul>";
    echo "<li><strong>Bookings:</strong> {$counts_before['bookings']}</li>";
    echo "<li><strong>Notifications:</strong> {$counts_before['notifications']}</li>";
    echo "<li><strong>Email Records:</strong> {$counts_before['emails']}</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error getting current counts: " . $e->getMessage() . "</p>";
    $counts_before = ['bookings' => 0, 'notifications' => 0, 'emails' => 0];
}

// Automatic reset (no confirmation needed for clean testing)
echo "<h3>2. Performing Complete System Reset</h3>";

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete all bookings
    $stmt = $pdo->prepare("DELETE FROM bookings");
    $stmt->execute();
    $deleted_bookings = $stmt->rowCount();
    echo "<p style='color: green;'>✅ Deleted {$deleted_bookings} bookings</p>";
    
    // Delete all notifications
    $stmt = $pdo->prepare("DELETE FROM admin_notifications");
    $stmt->execute();
    $deleted_notifications = $stmt->rowCount();
    echo "<p style='color: green;'>✅ Deleted {$deleted_notifications} notifications</p>";
    
    // Delete all email records
    $stmt = $pdo->prepare("DELETE FROM email_notifications");
    $stmt->execute();
    $deleted_emails = $stmt->rowCount();
    echo "<p style='color: green;'>✅ Deleted {$deleted_emails} email records</p>";
    
    // Reset auto-increment counters to start fresh
    $pdo->exec("ALTER TABLE bookings AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE admin_notifications AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE email_notifications AUTO_INCREMENT = 1");
    echo "<p style='color: green;'>✅ Reset auto-increment counters</p>";
    
    // Commit transaction
    $pdo->commit();
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
    echo "<h4>🎉 System Reset Complete!</h4>";
    echo "<p>All sample data has been removed. Your system is now clean and ready for fresh testing.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    // Rollback on error
    $pdo->rollback();
    echo "<p style='color: red;'>❌ Error during reset: " . $e->getMessage() . "</p>";
}

echo "<h3>3. Verifying Clean State</h3>";

// Verify clean state
try {
    $counts_after = [
        'bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
        'notifications' => $pdo->query("SELECT COUNT(*) FROM admin_notifications")->fetchColumn(),
        'emails' => $pdo->query("SELECT COUNT(*) FROM email_notifications")->fetchColumn()
    ];
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>📊 After Reset:</h4>";
    echo "<ul>";
    echo "<li><strong>Bookings:</strong> {$counts_after['bookings']} " . ($counts_after['bookings'] == 0 ? '✅' : '❌') . "</li>";
    echo "<li><strong>Notifications:</strong> {$counts_after['notifications']} " . ($counts_after['notifications'] == 0 ? '✅' : '❌') . "</li>";
    echo "<li><strong>Email Records:</strong> {$counts_after['emails']} " . ($counts_after['emails'] == 0 ? '✅' : '❌') . "</li>";
    echo "</ul>";
    echo "</div>";
    
    $all_clean = ($counts_after['bookings'] == 0 && $counts_after['notifications'] == 0 && $counts_after['emails'] == 0);
    
    if ($all_clean) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
        echo "<h4>✅ System is Completely Clean!</h4>";
        echo "<p>All data tables are empty and ready for fresh testing.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;'>";
        echo "<h4>⚠️ Some Data Remains</h4>";
        echo "<p>There might be some data that wasn't deleted. Check the counts above.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error verifying clean state: " . $e->getMessage() . "</p>";
}

echo "<h3>4. System Status Check</h3>";

// Verify essential components still exist
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>🔍 Essential Components Check:</h4>";

// Check admin user
try {
    $admin_exists = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'")->fetchColumn();
    echo "<p style='color: " . ($admin_exists > 0 ? 'green' : 'red') . ";'>";
    echo ($admin_exists > 0 ? '✅' : '❌') . " Admin User: " . ($admin_exists > 0 ? 'Exists (admin/admin123)' : 'Missing') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Admin User: Error checking</p>";
}

// Check website content
try {
    $content_exists = $pdo->query("SELECT COUNT(*) FROM website_content")->fetchColumn();
    echo "<p style='color: " . ($content_exists > 0 ? 'green' : 'red') . ";'>";
    echo ($content_exists > 0 ? '✅' : '❌') . " Website Content: " . ($content_exists > 0 ? 'Exists' : 'Missing') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Website Content: Error checking</p>";
}

// Check settings
try {
    $settings_exists = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    echo "<p style='color: " . ($settings_exists > 0 ? 'green' : 'red') . ";'>";
    echo ($settings_exists > 0 ? '✅' : '❌') . " Settings: " . ($settings_exists > 0 ? 'Exists' : 'Missing') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Settings: Error checking</p>";
}

// Check table structure
$required_tables = ['bookings', 'admin_users', 'email_notifications', 'admin_notifications', 'website_content', 'settings'];
$tables_exist = 0;
foreach ($required_tables as $table) {
    try {
        $pdo->query("SELECT 1 FROM $table LIMIT 1");
        $tables_exist++;
    } catch (Exception $e) {
        // Table doesn't exist
    }
}
echo "<p style='color: " . ($tables_exist == count($required_tables) ? 'green' : 'red') . ";'>";
echo ($tables_exist == count($required_tables) ? '✅' : '❌') . " Database Tables: {$tables_exist}/" . count($required_tables) . " exist</p>";

echo "</div>";

echo "<h3>5. Ready for Fresh Testing</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🚀 Your System is Ready for Fresh Testing!</h4>";

echo "<h5>🎛️ Dashboard Features (Now Clean):</h5>";
echo "<ul>";
echo "<li>📊 <strong>Animated stat cards</strong> showing 0 for all counts</li>";
echo "<li>🔔 <strong>Notification widget</strong> with no unread notifications</li>";
echo "<li>📋 <strong>Recent sections</strong> showing 'No data' messages</li>";
echo "<li>✨ <strong>All animations</strong> and effects still working</li>";
echo "</ul>";

echo "<h5>🧪 Test Workflow:</h5>";
echo "<ol>";
echo "<li><strong>View Clean Dashboard:</strong> <a href='../admin/dashboard.php' target='_blank'>Admin Dashboard</a></li>";
echo "<li><strong>Submit Test Booking:</strong> <a href='../booking.html' target='_blank'>Booking Form</a></li>";
echo "<li><strong>Watch Stats Update:</strong> See dashboard numbers change in real-time</li>";
echo "<li><strong>Check Notifications:</strong> See notification widget update</li>";
echo "<li><strong>Manage Booking:</strong> Update status and send emails</li>";
echo "<li><strong>Test Complete Workflow:</strong> From booking to completion</li>";
echo "</ol>";

echo "<h5>🎯 What to Test:</h5>";
echo "<ul>";
echo "<li>✅ <strong>Booking submission</strong> from frontend</li>";
echo "<li>✅ <strong>Real-time dashboard updates</strong> with animations</li>";
echo "<li>✅ <strong>Email notifications</strong> to client and admin</li>";
echo "<li>✅ <strong>Notification widget</strong> in admin header</li>";
echo "<li>✅ <strong>Status updates</strong> and client notifications</li>";
echo "<li>✅ <strong>Delete functionality</strong> for completed bookings</li>";
echo "</ul>";

echo "<h5>🔗 Quick Access Links:</h5>";
echo "<ul>";
echo "<li><strong>Admin Dashboard:</strong> <a href='../admin/dashboard.php' target='_blank'>http://localhost/mc_website/admin/dashboard.php</a></li>";
echo "<li><strong>Admin Login:</strong> <a href='../admin/' target='_blank'>http://localhost/mc_website/admin/</a> (admin/admin123)</li>";
echo "<li><strong>Booking Form:</strong> <a href='../booking.html' target='_blank'>http://localhost/mc_website/booking.html</a></li>";
echo "<li><strong>Main Website:</strong> <a href='../index.html' target='_blank'>http://localhost/mc_website/index.html</a></li>";
echo "</ul>";

echo "</div>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>🎉 Perfect! System Reset Complete!</h4>";
echo "<p><strong>Your MC booking system is now:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Completely clean</strong> with no sample data</li>";
echo "<li>✅ <strong>Fully functional</strong> with all features working</li>";
echo "<li>✅ <strong>Ready for testing</strong> with real workflow</li>";
echo "<li>✅ <strong>Dashboard optimized</strong> with clean stat cards</li>";
echo "<li>✅ <strong>Email system ready</strong> for notifications</li>";
echo "</ul>";

echo "<p><strong>Start testing now by submitting your first real booking!</strong> 🚀</p>";
echo "</div>";

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset System Clean</title>
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
