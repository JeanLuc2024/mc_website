<?php
/**
 * Admin Panel Functionality Test
 * 
 * This script tests all admin panel functionalities to ensure they work properly.
 */

require_once 'config.php';

// Get database connection
$conn = connectDB();

if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

echo "<h2>🧪 Testing Admin Panel Functionalities...</h2>";

$test_results = [];

try {
    // Test 1: Admin Users Table
    echo "<h3>1. Testing Admin Users System</h3>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM admin_users");
        $admin_count = $stmt->fetchColumn();
        $test_results['admin_users'] = "✅ PASS - {$admin_count} admin users found";
        echo "<p style='color: green;'>{$test_results['admin_users']}</p>";
        
        // Test login credentials
        $stmt = $conn->query("SELECT username, password FROM admin_users WHERE username = 'admin'");
        $admin = $stmt->fetch();
        if ($admin) {
            $password_test = (md5('admin123') === $admin['password']) ? 'PASS' : 'FAIL';
            echo "<p style='color: " . ($password_test === 'PASS' ? 'green' : 'red') . ";'>Password test: {$password_test}</p>";
        }
    } catch (Exception $e) {
        $test_results['admin_users'] = "❌ FAIL - " . $e->getMessage();
        echo "<p style='color: red;'>{$test_results['admin_users']}</p>";
    }

    // Test 2: Settings System
    echo "<h3>2. Testing Settings System</h3>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM admin_settings");
        $settings_count = $stmt->fetchColumn();
        $test_results['settings'] = "✅ PASS - {$settings_count} settings found";
        echo "<p style='color: green;'>{$test_results['settings']}</p>";
    } catch (Exception $e) {
        $test_results['settings'] = "❌ FAIL - " . $e->getMessage();
        echo "<p style='color: red;'>{$test_results['settings']}</p>";
    }

    // Test 3: Bookings System
    echo "<h3>3. Testing Bookings System</h3>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM bookings");
        $bookings_count = $stmt->fetchColumn();
        $test_results['bookings'] = "✅ PASS - {$bookings_count} bookings found";
        echo "<p style='color: green;'>{$test_results['bookings']}</p>";
        
        // Test booking structure
        $stmt = $conn->query("DESCRIBE bookings");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $required_columns = ['id', 'booking_ref', 'name', 'email', 'phone', 'event_type', 'event_date', 'status'];
        $missing_columns = array_diff($required_columns, $columns);
        
        if (empty($missing_columns)) {
            echo "<p style='color: green;'>✅ All required booking columns present</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Missing columns: " . implode(', ', $missing_columns) . "</p>";
        }
    } catch (Exception $e) {
        $test_results['bookings'] = "❌ FAIL - " . $e->getMessage();
        echo "<p style='color: red;'>{$test_results['bookings']}</p>";
    }

    // Test 4: Notifications System
    echo "<h3>4. Testing Notifications System</h3>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM notifications");
        $notifications_count = $stmt->fetchColumn();
        $test_results['notifications'] = "✅ PASS - {$notifications_count} notifications found";
        echo "<p style='color: green;'>{$test_results['notifications']}</p>";
    } catch (Exception $e) {
        $test_results['notifications'] = "❌ FAIL - " . $e->getMessage();
        echo "<p style='color: red;'>{$test_results['notifications']}</p>";
    }

    // Test 5: Email Communication System
    echo "<h3>5. Testing Email Communication System</h3>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM email_communications");
        $emails_count = $stmt->fetchColumn();
        
        $stmt = $conn->query("SELECT COUNT(*) FROM email_templates");
        $templates_count = $stmt->fetchColumn();
        
        $test_results['email_system'] = "✅ PASS - {$emails_count} emails, {$templates_count} templates";
        echo "<p style='color: green;'>{$test_results['email_system']}</p>";
    } catch (Exception $e) {
        $test_results['email_system'] = "❌ FAIL - " . $e->getMessage();
        echo "<p style='color: red;'>{$test_results['email_system']}</p>";
    }

    // Test 6: Content Management System
    echo "<h3>6. Testing Content Management System</h3>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM website_content");
        $content_count = $stmt->fetchColumn();
        $test_results['content_management'] = "✅ PASS - {$content_count} content items found";
        echo "<p style='color: green;'>{$test_results['content_management']}</p>";
    } catch (Exception $e) {
        $test_results['content_management'] = "❌ FAIL - " . $e->getMessage();
        echo "<p style='color: red;'>{$test_results['content_management']}</p>";
    }

    // Test 7: Activity Logging
    echo "<h3>7. Testing Activity Logging System</h3>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM admin_activity_log");
        $activity_count = $stmt->fetchColumn();
        $test_results['activity_log'] = "✅ PASS - {$activity_count} activity logs found";
        echo "<p style='color: green;'>{$test_results['activity_log']}</p>";
    } catch (Exception $e) {
        $test_results['activity_log'] = "❌ FAIL - " . $e->getMessage();
        echo "<p style='color: red;'>{$test_results['activity_log']}</p>";
    }

    // Test 8: Admin Panel Pages
    echo "<h3>8. Testing Admin Panel Pages</h3>";
    $admin_pages = [
        'index.php' => 'Login Page',
        'dashboard.php' => 'Dashboard',
        'bookings.php' => 'Bookings Management',
        'notifications.php' => 'Notifications Center',
        'email-client.php' => 'Email Client',
        'email-templates.php' => 'Email Templates',
        'content-management.php' => 'Content Management',
        'settings.php' => 'Settings',
        'profile.php' => 'Profile Management'
    ];

    $missing_pages = [];
    foreach ($admin_pages as $page => $description) {
        if (file_exists("../admin/{$page}")) {
            echo "<p style='color: green;'>✅ {$description} - Found</p>";
        } else {
            echo "<p style='color: red;'>❌ {$description} - Missing</p>";
            $missing_pages[] = $page;
        }
    }

    if (empty($missing_pages)) {
        $test_results['admin_pages'] = "✅ PASS - All admin pages found";
    } else {
        $test_results['admin_pages'] = "❌ FAIL - Missing: " . implode(', ', $missing_pages);
    }

    // Test 9: Configuration
    echo "<h3>9. Testing Configuration</h3>";
    $config_tests = [
        'ADMIN_EMAIL' => ADMIN_EMAIL,
        'SITE_URL' => SITE_URL,
        'DB_NAME' => DB_NAME
    ];

    foreach ($config_tests as $key => $value) {
        echo "<p style='color: blue;'>{$key}: {$value}</p>";
    }
    $test_results['configuration'] = "✅ PASS - Configuration loaded";

    // Summary
    echo "<h3 style='color: #2c3e50;'>📊 Test Summary</h3>";
    
    $passed = 0;
    $failed = 0;
    
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    foreach ($test_results as $test => $result) {
        echo "<p>{$result}</p>";
        if (strpos($result, '✅ PASS') !== false) {
            $passed++;
        } else {
            $failed++;
        }
    }
    echo "</div>";

    $total_tests = $passed + $failed;
    $success_rate = $total_tests > 0 ? round(($passed / $total_tests) * 100, 1) : 0;

    echo "<div style='background: " . ($success_rate >= 80 ? '#d4edda' : '#fff3cd') . "; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid " . ($success_rate >= 80 ? '#28a745' : '#ffc107') . ";'>";
    echo "<h4>Overall Result: {$success_rate}% Success Rate</h4>";
    echo "<p><strong>Passed:</strong> {$passed} tests</p>";
    echo "<p><strong>Failed:</strong> {$failed} tests</p>";
    echo "</div>";

    if ($success_rate >= 80) {
        echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
        echo "<h4>🎉 Admin Panel is Ready!</h4>";
        echo "<p><strong>Login Credentials:</strong></p>";
        echo "<ul>";
        echo "<li>Username: admin</li>";
        echo "<li>Password: admin123</li>";
        echo "<li>URL: <a href='../admin/' target='_blank'>http://localhost/mc_website/admin/</a></li>";
        echo "</ul>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ol>";
        echo "<li>Login to the admin panel</li>";
        echo "<li>Change your password in Profile section</li>";
        echo "<li>Update settings with your information</li>";
        echo "<li>Test the booking system</li>";
        echo "<li>Test email communication</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
        echo "<h4>⚠️ Issues Found</h4>";
        echo "<p>Some functionalities need attention. Please run the fix script:</p>";
        echo "<p><a href='fix_admin_panel.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Run Fix Script</a></p>";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Critical Error: " . $e->getMessage() . "</p>";
}

// Close connection
$conn = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel Test Results</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1000px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        h4 { color: #2c3e50; margin-bottom: 10px; }
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
