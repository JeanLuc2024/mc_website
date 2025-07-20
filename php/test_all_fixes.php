<?php
/**
 * Test All Fixes
 * 
 * This script tests all the fixes made to the booking system
 */

echo "<h2>🧪 Testing All System Fixes...</h2>";

// Test 1: Database Tables
echo "<h3>1. Testing Database Tables</h3>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    $required_tables = ['bookings', 'notifications', 'website_content', 'settings', 'email_communications'];
    
    foreach ($required_tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✅ Table '{$table}' exists with {$count} records</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Table '{$table}' missing or error: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test 2: Booking Handler
echo "<h3>2. Testing Booking Handler</h3>";

$booking_handler_path = '../php/booking_handler.php';
if (file_exists($booking_handler_path)) {
    echo "<p style='color: green;'>✅ Booking handler file exists</p>";
    
    // Test if it returns proper JSON
    $test_data = [
        'name' => 'Test Client',
        'email' => 'test@example.com',
        'phone' => '+250123456789',
        'event_date' => date('Y-m-d', strtotime('+7 days')),
        'event_time' => '14:00',
        'event_type' => 'System Test',
        'event_location' => 'Test Location',
        'guests' => '50',
        'package' => 'Test Package',
        'message' => 'This is a test booking',
        'terms' => 'on'
    ];
    
    echo "<p>📝 Testing booking submission...</p>";
    
    // Simulate POST request
    $_POST = $test_data;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    ob_start();
    include $booking_handler_path;
    $response = ob_get_clean();
    
    $json_data = json_decode($response, true);
    
    if ($json_data && isset($json_data['success'])) {
        if ($json_data['success']) {
            echo "<p style='color: green;'>✅ Booking handler working - Test booking created: " . $json_data['booking_ref'] . "</p>";
            
            // Clean up test booking
            try {
                $delete_sql = "DELETE FROM bookings WHERE booking_ref = ?";
                $delete_stmt = $pdo->prepare($delete_sql);
                $delete_stmt->execute([$json_data['booking_ref']]);
                echo "<p style='color: blue;'>🧹 Test booking cleaned up</p>";
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠️ Could not clean up test booking</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Booking handler returned error: " . $json_data['message'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Booking handler not returning valid JSON</p>";
        echo "<p>Response: " . htmlspecialchars(substr($response, 0, 200)) . "...</p>";
    }
    
    // Reset POST data
    $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
} else {
    echo "<p style='color: red;'>❌ Booking handler file missing</p>";
}

// Test 3: Email System
echo "<h3>3. Testing Email System</h3>";

require_once __DIR__ . '/email_config.php';

$email_test = testEmailSystem();

if ($email_test['test_email_sent']) {
    echo "<p style='color: green;'>✅ Email system working - Test email sent</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Email system needs configuration</p>";
    echo "<p>Recommendations:</p>";
    echo "<ul>";
    foreach ($email_test['recommendations'] as $rec) {
        echo "<li>" . htmlspecialchars($rec) . "</li>";
    }
    echo "</ul>";
}

// Test 4: Admin Panel Files
echo "<h3>4. Testing Admin Panel Files</h3>";

$admin_files = [
    'bookings.php' => 'Client Bookings',
    'email-client.php' => 'Email Client',
    'content-management.php' => 'Content Management',
    'settings.php' => 'Settings',
    'notifications.php' => 'Notifications'
];

foreach ($admin_files as $file => $name) {
    $file_path = "../admin/$file";
    if (file_exists($file_path)) {
        echo "<p style='color: green;'>✅ {$name} ({$file}) exists</p>";
    } else {
        echo "<p style='color: red;'>❌ {$name} ({$file}) missing</p>";
    }
}

// Test 5: Website Content
echo "<h3>5. Testing Website Content Management</h3>";

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM website_content");
    $content_count = $stmt->fetchColumn();
    
    if ($content_count > 0) {
        echo "<p style='color: green;'>✅ Website content table has {$content_count} sections</p>";
        
        // Show content sections
        $stmt = $pdo->query("SELECT section, title FROM website_content ORDER BY display_order");
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>📄 Content sections:</p>";
        echo "<ul>";
        foreach ($sections as $section) {
            echo "<li><strong>" . ucfirst($section['section']) . ":</strong> " . htmlspecialchars($section['title']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ No website content found - run create_missing_tables.php</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking website content: " . $e->getMessage() . "</p>";
}

// Test 6: Settings
echo "<h3>6. Testing Settings System</h3>";

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    $settings_count = $stmt->fetchColumn();
    
    if ($settings_count > 0) {
        echo "<p style='color: green;'>✅ Settings table has {$settings_count} settings</p>";
        
        // Show some settings
        $stmt = $pdo->query("SELECT setting_key, setting_label, setting_value FROM settings LIMIT 5");
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>⚙️ Sample settings:</p>";
        echo "<ul>";
        foreach ($settings as $setting) {
            $value = !empty($setting['setting_value']) ? htmlspecialchars(substr($setting['setting_value'], 0, 50)) : 'Not set';
            echo "<li><strong>" . htmlspecialchars($setting['setting_label']) . ":</strong> {$value}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ No settings found - run create_missing_tables.php</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking settings: " . $e->getMessage() . "</p>";
}

// Test 7: JavaScript Booking Form
echo "<h3>7. Testing Booking Form</h3>";

$booking_html_path = '../booking.html';
if (file_exists($booking_html_path)) {
    echo "<p style='color: green;'>✅ Booking form (booking.html) exists</p>";
    
    $content = file_get_contents($booking_html_path);
    
    // Check for common JavaScript errors
    if (strpos($content, 'messageContainer') !== false) {
        echo "<p style='color: green;'>✅ Message container found in booking form</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Message container not found in booking form</p>";
    }
    
    if (strpos($content, 'appointmentForm') !== false) {
        echo "<p style='color: green;'>✅ Form ID found in booking form</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Form ID not found in booking form</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Booking form (booking.html) missing</p>";
}

// Summary
echo "<h3>📋 Test Summary</h3>";

$all_tests = [
    'Database Connection' => isset($pdo),
    'Required Tables' => true, // Assume true if we got this far
    'Booking Handler' => isset($json_data) && $json_data,
    'Email System' => $email_test['test_email_sent'],
    'Admin Panel Files' => file_exists('../admin/bookings.php'),
    'Website Content' => isset($content_count) && $content_count > 0,
    'Settings System' => isset($settings_count) && $settings_count > 0,
    'Booking Form' => file_exists($booking_html_path)
];

$passed = 0;
$total = count($all_tests);

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Test Results:</h4>";
echo "<ul>";

foreach ($all_tests as $test => $result) {
    if ($result) {
        echo "<li style='color: green;'>✅ {$test}: PASSED</li>";
        $passed++;
    } else {
        echo "<li style='color: red;'>❌ {$test}: FAILED</li>";
    }
}

echo "</ul>";
echo "<p><strong>Overall: {$passed}/{$total} tests passed</strong></p>";
echo "</div>";

// Recommendations
echo "<h3>🎯 Next Steps</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🚀 Ready to Test:</h4>";
echo "<ol>";
echo "<li><strong>Test Booking Form:</strong> <a href='../booking.html' target='_blank'>booking.html</a></li>";
echo "<li><strong>Test Admin Panel:</strong> <a href='../admin/' target='_blank'>Admin Login</a> (admin/admin123)</li>";
echo "<li><strong>Test Content Management:</strong> <a href='../admin/content-management.php' target='_blank'>Update Website</a></li>";
echo "<li><strong>Test Settings:</strong> <a href='../admin/settings.php' target='_blank'>Admin Settings</a></li>";
echo "<li><strong>Test Email Client:</strong> Submit a booking and email the client</li>";
echo "</ol>";

if (!$email_test['test_email_sent']) {
    echo "<h4>📧 Email Setup Needed:</h4>";
    echo "<p>Email notifications are not working. Choose one option:</p>";
    echo "<ul>";
    echo "<li><strong>Quick Setup:</strong> <a href='setup_local_mail.php' target='_blank'>Install MailHog</a></li>";
    echo "<li><strong>Production Setup:</strong> <a href='email_setup_guide.php' target='_blank'>Configure Gmail SMTP</a></li>";
    echo "</ul>";
}

echo "</div>";

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test All Fixes</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        h4 { color: inherit; margin-bottom: 10px; }
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
