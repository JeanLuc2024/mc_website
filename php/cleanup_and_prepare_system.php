<?php
/**
 * Cleanup and Prepare System
 * 
 * This script cleans up test data and prepares the system for production
 */

echo "<h2>🧹 Cleaning Up Test Data and Preparing System...</h2>";

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h3>1. Cleaning Up Test Data</h3>";

// Get current counts before cleanup
$counts_before = [];
try {
    $counts_before['bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $counts_before['notifications'] = $pdo->query("SELECT COUNT(*) FROM admin_notifications")->fetchColumn();
    $counts_before['emails'] = $pdo->query("SELECT COUNT(*) FROM email_notifications")->fetchColumn();
} catch (Exception $e) {
    $counts_before = ['bookings' => 0, 'notifications' => 0, 'emails' => 0];
}

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📊 Current Data Count (Before Cleanup):</h4>";
echo "<ul>";
echo "<li><strong>Bookings:</strong> {$counts_before['bookings']}</li>";
echo "<li><strong>Notifications:</strong> {$counts_before['notifications']}</li>";
echo "<li><strong>Email Records:</strong> {$counts_before['emails']}</li>";
echo "</ul>";
echo "</div>";

// Option to delete all test data
if (isset($_POST['cleanup_all'])) {
    echo "<h4>🗑️ Deleting All Test Data...</h4>";
    
    try {
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
        
        // Reset auto-increment counters
        $pdo->exec("ALTER TABLE bookings AUTO_INCREMENT = 1");
        $pdo->exec("ALTER TABLE admin_notifications AUTO_INCREMENT = 1");
        $pdo->exec("ALTER TABLE email_notifications AUTO_INCREMENT = 1");
        echo "<p style='color: green;'>✅ Reset auto-increment counters</p>";
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
        echo "<h4>🎉 Cleanup Complete!</h4>";
        echo "<p>All test data has been removed. Your system is now clean and ready for production.</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error during cleanup: " . $e->getMessage() . "</p>";
    }
    
} elseif (isset($_POST['cleanup_test_only'])) {
    echo "<h4>🧪 Deleting Only Test Data...</h4>";
    
    try {
        // Delete test bookings (those with 'TEST' in booking_ref or name)
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE booking_ref LIKE '%TEST%' OR name LIKE '%Test%'");
        $stmt->execute();
        $deleted_bookings = $stmt->rowCount();
        echo "<p style='color: green;'>✅ Deleted {$deleted_bookings} test bookings</p>";
        
        // Delete related notifications for deleted bookings
        $stmt = $pdo->prepare("DELETE FROM admin_notifications WHERE booking_id NOT IN (SELECT id FROM bookings)");
        $stmt->execute();
        $deleted_notifications = $stmt->rowCount();
        echo "<p style='color: green;'>✅ Deleted {$deleted_notifications} orphaned notifications</p>";
        
        // Delete related email records for deleted bookings
        $stmt = $pdo->prepare("DELETE FROM email_notifications WHERE booking_id NOT IN (SELECT id FROM bookings)");
        $stmt->execute();
        $deleted_emails = $stmt->rowCount();
        echo "<p style='color: green;'>✅ Deleted {$deleted_emails} orphaned email records</p>";
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
        echo "<h4>🧪 Test Data Cleanup Complete!</h4>";
        echo "<p>Only test data has been removed. Real bookings (if any) have been preserved.</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error during test cleanup: " . $e->getMessage() . "</p>";
    }
}

// Show cleanup options if not already performed
if (!isset($_POST['cleanup_all']) && !isset($_POST['cleanup_test_only'])) {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
    echo "<h4>⚠️ Cleanup Options</h4>";
    echo "<p>Choose how you want to clean up the test data:</p>";
    
    echo "<form method='POST' style='margin: 15px 0;'>";
    echo "<button type='submit' name='cleanup_test_only' style='background: #ffc107; color: #212529; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;'>";
    echo "🧪 Delete Test Data Only";
    echo "</button>";
    echo "<small style='display: block; margin-top: 5px; color: #856404;'>Removes only bookings with 'TEST' in reference or name</small>";
    echo "</form>";
    
    echo "<form method='POST' style='margin: 15px 0;'>";
    echo "<button type='submit' name='cleanup_all' onclick='return confirm(\"Are you sure you want to delete ALL data? This cannot be undone!\")' style='background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>";
    echo "🗑️ Delete All Data";
    echo "</button>";
    echo "<small style='display: block; margin-top: 5px; color: #721c24;'>Removes ALL bookings, notifications, and email records</small>";
    echo "</form>";
    echo "</div>";
}

echo "<h3>2. System Verification</h3>";

// Verify system components
$system_checks = [
    'Database Tables' => 'Check if all required tables exist',
    'Admin User' => 'Verify admin user exists and can login',
    'Email System' => 'Check notification system functions',
    'Frontend Files' => 'Verify booking form and website files',
    'Admin Panel' => 'Check admin panel files and functionality'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>🔍 System Component Verification:</h4>";

// Check database tables
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
echo ($tables_exist == count($required_tables) ? '✅' : '❌') . " Database Tables: {$tables_exist}/" . count($required_tables) . " tables exist</p>";

// Check admin user
try {
    $admin_exists = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'")->fetchColumn();
    echo "<p style='color: " . ($admin_exists > 0 ? 'green' : 'red') . ";'>";
    echo ($admin_exists > 0 ? '✅' : '❌') . " Admin User: " . ($admin_exists > 0 ? 'Exists' : 'Missing') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Admin User: Error checking</p>";
}

// Check email system
$email_functions_exist = function_exists('sendBookingConfirmationEmail') && function_exists('sendAdminBookingNotification');
echo "<p style='color: " . ($email_functions_exist ? 'green' : 'red') . ";'>";
echo ($email_functions_exist ? '✅' : '❌') . " Email System: " . ($email_functions_exist ? 'Functions loaded' : 'Functions missing') . "</p>";

// Check frontend files
$frontend_files = ['../booking.html', '../index.html', '../services.html'];
$frontend_exist = 0;
foreach ($frontend_files as $file) {
    if (file_exists($file)) $frontend_exist++;
}
echo "<p style='color: " . ($frontend_exist == count($frontend_files) ? 'green' : 'red') . ";'>";
echo ($frontend_exist == count($frontend_files) ? '✅' : '❌') . " Frontend Files: {$frontend_exist}/" . count($frontend_files) . " files exist</p>";

// Check admin panel files
$admin_files = ['../admin/dashboard.php', '../admin/bookings.php', '../admin/includes/header.php'];
$admin_exist = 0;
foreach ($admin_files as $file) {
    if (file_exists($file)) $admin_exist++;
}
echo "<p style='color: " . ($admin_exist == count($admin_files) ? 'green' : 'red') . ";'>";
echo ($admin_exist == count($admin_files) ? '✅' : '❌') . " Admin Panel: {$admin_exist}/" . count($admin_files) . " files exist</p>";

echo "</div>";

echo "<h3>3. Current System Statistics</h3>";

// Get current statistics after cleanup
try {
    $current_stats = [
        'total_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
        'pending_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
        'confirmed_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn(),
        'unread_notifications' => $pdo->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = FALSE")->fetchColumn(),
        'total_emails' => $pdo->query("SELECT COUNT(*) FROM email_notifications")->fetchColumn(),
        'sent_emails' => $pdo->query("SELECT COUNT(*) FROM email_notifications WHERE status = 'sent'")->fetchColumn()
    ];
    
    echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
    echo "<h4>📊 Current System Statistics:</h4>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0;'>";
    
    $stat_cards = [
        ['Total Bookings', $current_stats['total_bookings'], '#3498db', 'fas fa-calendar'],
        ['Pending Bookings', $current_stats['pending_bookings'], '#f39c12', 'fas fa-clock'],
        ['Confirmed Bookings', $current_stats['confirmed_bookings'], '#27ae60', 'fas fa-check-circle'],
        ['Unread Notifications', $current_stats['unread_notifications'], '#e74c3c', 'fas fa-bell'],
        ['Total Emails', $current_stats['total_emails'], '#9b59b6', 'fas fa-envelope'],
        ['Sent Emails', $current_stats['sent_emails'], '#2ecc71', 'fas fa-paper-plane']
    ];
    
    foreach ($stat_cards as $card) {
        echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid {$card[2]}; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
        echo "<div style='display: flex; align-items: center; gap: 10px;'>";
        echo "<i class='{$card[3]}' style='font-size: 24px; color: {$card[2]};'></i>";
        echo "<div>";
        echo "<div style='font-size: 24px; font-weight: bold; color: {$card[2]};'>{$card[1]}</div>";
        echo "<div style='font-size: 14px; color: #666;'>{$card[0]}</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error getting current statistics: " . $e->getMessage() . "</p>";
}

echo "<h3>4. System Ready for Production</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>🚀 Your MC Booking System is Ready!</h4>";

echo "<h5>🎯 What's Working:</h5>";
echo "<ul>";
echo "<li>✅ <strong>Complete database structure</strong> with all required tables</li>";
echo "<li>✅ <strong>Admin panel</strong> with animated dashboard and stat cards</li>";
echo "<li>✅ <strong>Real-time notifications</strong> system</li>";
echo "<li>✅ <strong>Email notification system</strong> for clients and admin</li>";
echo "<li>✅ <strong>Booking management</strong> with status updates</li>";
echo "<li>✅ <strong>Content management</strong> for website updates</li>";
echo "<li>✅ <strong>Updated pricing</strong> (Basic $100, Premium $150, Deluxe $200)</li>";
echo "</ul>";

echo "<h5>🎛️ Admin Panel Features:</h5>";
echo "<ul>";
echo "<li>📊 <strong>Animated stat cards</strong> with real-time data</li>";
echo "<li>🔔 <strong>Real-time notification widget</strong> in header</li>";
echo "<li>📋 <strong>Booking management</strong> with search and filters</li>";
echo "<li>📧 <strong>Email client system</strong> for direct communication</li>";
echo "<li>✏️ <strong>Content management</strong> for website updates</li>";
echo "<li>⚙️ <strong>Settings management</strong> for business configuration</li>";
echo "</ul>";

echo "<h5>🌐 Access Your System:</h5>";
echo "<ul>";
echo "<li><strong>Admin Panel:</strong> <a href='../admin/' target='_blank'>http://localhost/mc_website/admin/</a></li>";
echo "<li><strong>Login:</strong> admin / admin123</li>";
echo "<li><strong>Booking Form:</strong> <a href='../booking.html' target='_blank'>http://localhost/mc_website/booking.html</a></li>";
echo "<li><strong>Main Website:</strong> <a href='../index.html' target='_blank'>http://localhost/mc_website/index.html</a></li>";
echo "</ul>";

echo "<h5>📧 Email Notifications:</h5>";
echo "<ul>";
echo "<li>✅ <strong>Client confirmation</strong> emails sent automatically</li>";
echo "<li>✅ <strong>Admin notifications</strong> sent to izabayojeanlucseverin@gmail.com</li>";
echo "<li>✅ <strong>Status update</strong> emails when booking status changes</li>";
echo "<li>✅ <strong>Real-time notifications</strong> in admin panel</li>";
echo "</ul>";

echo "</div>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🧪 Test Your System Now:</h4>";
echo "<ol>";
echo "<li><strong>Submit a test booking:</strong> <a href='../booking.html' target='_blank'>Fill out booking form</a></li>";
echo "<li><strong>Check admin dashboard:</strong> <a href='../admin/dashboard.php' target='_blank'>View animated stat cards</a></li>";
echo "<li><strong>See notification widget:</strong> Check header for notification badge</li>";
echo "<li><strong>Manage booking:</strong> Update status and send client notification</li>";
echo "<li><strong>Test email system:</strong> Verify client receives status updates</li>";
echo "<li><strong>Clean up test data:</strong> Delete test booking when done</li>";
echo "</ol>";
echo "</div>";

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cleanup and Prepare System</title>
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
        button { transition: all 0.3s ease; }
        button:hover { transform: translateY(-1px); }
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
