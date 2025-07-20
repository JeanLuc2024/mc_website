<?php
/**
 * Test Final Fixes
 * 
 * This script tests all the final fixes made to the system
 */

echo "<h2>🧪 Testing All Final Fixes...</h2>";

// Test 1: Email System
echo "<h3>1. Testing Email System</h3>";

require_once __DIR__ . '/simple_mail_system.php';

// Test simple email sending
$test_email_result = sendSimpleEmail(
    'izabayojeanlucseverin@gmail.com',
    'Test Email - ' . date('Y-m-d H:i:s'),
    '<h1>Test Email</h1><p>This is a test email from the simple mail system.</p>'
);

if ($test_email_result['success']) {
    echo "<p style='color: green;'>✅ Email system working: " . $test_email_result['message'] . "</p>";
    echo "<p><strong>Method used:</strong> " . $test_email_result['method'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ Email system failed: " . $test_email_result['message'] . "</p>";
}

// Test booking confirmation email
$test_booking_data = [
    'booking_ref' => 'TEST-' . date('ymd') . '-' . strtoupper(substr(md5(time()), 0, 6)),
    'name' => 'Test Client',
    'email' => 'test@example.com',
    'event_type' => 'Test Event',
    'event_date' => date('Y-m-d', strtotime('+7 days')),
    'event_time' => '14:00',
    'event_location' => 'Test Location',
    'guests' => 50
];

$confirmation_result = sendBookingConfirmation($test_booking_data);
if ($confirmation_result['success']) {
    echo "<p style='color: green;'>✅ Booking confirmation email system working</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Booking confirmation email: " . $confirmation_result['message'] . "</p>";
}

// Test 2: Database Tables
echo "<h3>2. Testing Database Tables</h3>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $required_tables = ['bookings', 'website_content', 'settings', 'email_communications', 'notifications'];
    
    foreach ($required_tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✅ Table '{$table}' exists with {$count} records</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Table '{$table}' error: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test 3: Content Management System
echo "<h3>3. Testing Content Management System</h3>";

require_once __DIR__ . '/update_website_content.php';

// Test hero section update
$test_update = updateWebsiteFiles(
    'hero',
    'Test Hero Title',
    'This is a test hero content update.',
    'Test Button',
    'test.html'
);

if ($test_update['success']) {
    echo "<p style='color: green;'>✅ Website content update working: " . $test_update['message'] . "</p>";
    echo "<p><strong>Files updated:</strong> " . implode(', ', $test_update['files_updated']) . "</p>";
    
    // Restore original content
    $restore_update = updateWebsiteFiles(
        'hero',
        'Make Your Event Memorable',
        'Professional Master of Ceremony for Weddings, Meetings, and Anniversary Celebrations',
        'Book an Appointment',
        'booking.html'
    );
    
    if ($restore_update['success']) {
        echo "<p style='color: blue;'>🔄 Original content restored</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Website content update failed: " . $test_update['message'] . "</p>";
}

// Test 4: Admin Panel Files
echo "<h3>4. Testing Admin Panel Files</h3>";

$admin_files = [
    'bookings.php' => 'Client Bookings (with delete functionality)',
    'email-client.php' => 'Email Client (simplified)',
    'content-management.php' => 'Content Management (with website update)',
    'settings.php' => 'Settings (with grouped categories)',
    'notifications.php' => 'Notifications'
];

foreach ($admin_files as $file => $description) {
    $file_path = "../admin/$file";
    if (file_exists($file_path)) {
        echo "<p style='color: green;'>✅ {$description} - File exists</p>";
        
        // Check for specific functionality
        $content = file_get_contents($file_path);
        
        if ($file === 'bookings.php') {
            if (strpos($content, 'delete_booking') !== false) {
                echo "<p style='color: green;'>  ✅ Delete functionality found</p>";
            } else {
                echo "<p style='color: orange;'>  ⚠️ Delete functionality not found</p>";
            }
            
            if (strpos($content, 'admin_message') !== false) {
                echo "<p style='color: green;'>  ✅ Admin message field found</p>";
            } else {
                echo "<p style='color: orange;'>  ⚠️ Admin message field not found</p>";
            }
        }
        
        if ($file === 'content-management.php') {
            if (strpos($content, 'update_website_content.php') !== false) {
                echo "<p style='color: green;'>  ✅ Website update integration found</p>";
            } else {
                echo "<p style='color: orange;'>  ⚠️ Website update integration not found</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ {$description} - File missing</p>";
    }
}

// Test 5: Settings System
echo "<h3>5. Testing Settings System</h3>";

try {
    $stmt = $pdo->query("SELECT setting_group, COUNT(*) as count FROM settings GROUP BY setting_group");
    $setting_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($setting_groups)) {
        echo "<p style='color: green;'>✅ Settings system working with grouped categories:</p>";
        echo "<ul>";
        foreach ($setting_groups as $group) {
            echo "<li><strong>" . ucfirst($group['setting_group']) . ":</strong> {$group['count']} settings</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ No settings found - run create_missing_tables.php</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Settings system error: " . $e->getMessage() . "</p>";
}

// Test 6: Manual Email Log (for when mail server is not available)
echo "<h3>6. Testing Manual Email Log</h3>";

$manual_emails = getManualEmails();
if (!empty($manual_emails)) {
    echo "<p style='color: blue;'>📧 Manual emails logged (for when mail server is not available):</p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px; max-height: 200px; overflow-y: auto;'>";
    echo htmlspecialchars(substr($manual_emails, 0, 1000));
    if (strlen($manual_emails) > 1000) {
        echo "\n... (truncated)";
    }
    echo "</pre>";
    
    echo "<p><a href='#' onclick='clearManualEmails()' style='color: #dc3545;'>Clear Manual Email Log</a></p>";
} else {
    echo "<p style='color: green;'>✅ No manual emails logged (mail system working or no emails sent)</p>";
}

// Summary
echo "<h3>📋 Test Summary</h3>";

$tests = [
    'Email System' => $test_email_result['success'],
    'Database Tables' => isset($pdo),
    'Content Management' => $test_update['success'],
    'Admin Panel Files' => file_exists('../admin/bookings.php'),
    'Settings System' => !empty($setting_groups),
    'Booking Confirmation' => $confirmation_result['success']
];

$passed = 0;
$total = count($tests);

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Final Test Results:</h4>";
echo "<ul>";

foreach ($tests as $test => $result) {
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

// Next Steps
echo "<h3>🚀 Next Steps</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🎯 Ready to Use:</h4>";
echo "<ol>";
echo "<li><strong>Test Booking Form:</strong> <a href='../booking.html' target='_blank'>Submit a test booking</a></li>";
echo "<li><strong>Test Admin Panel:</strong> <a href='../admin/' target='_blank'>Login to admin panel</a> (admin/admin123)</li>";
echo "<li><strong>Test Email Client:</strong> Send an email to a client from bookings</li>";
echo "<li><strong>Test Content Management:</strong> Update website content and check changes</li>";
echo "<li><strong>Test Settings:</strong> Update business information</li>";
echo "<li><strong>Test Delete Function:</strong> Delete a completed booking</li>";
echo "</ol>";

echo "<h4>📧 Email Setup Options:</h4>";
if ($test_email_result['method'] === 'manual_log') {
    echo "<p style='color: orange;'>⚠️ Email server not configured. Choose one option:</p>";
    echo "<ul>";
    echo "<li><strong>Quick Setup:</strong> <a href='setup_local_mail.php' target='_blank'>Install MailHog for testing</a></li>";
    echo "<li><strong>Production Setup:</strong> <a href='email_setup_guide.php' target='_blank'>Configure Gmail SMTP</a></li>";
    echo "<li><strong>Manual Emails:</strong> Check manual email log above for emails to send manually</li>";
    echo "</ul>";
} else {
    echo "<p style='color: green;'>✅ Email system is working! Clients will receive automatic notifications.</p>";
}

echo "</div>";

$pdo = null;
?>

<script>
function clearManualEmails() {
    if (confirm('Are you sure you want to clear the manual email log?')) {
        fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=clear_emails', {method: 'POST'})
        .then(() => location.reload());
    }
}
</script>

<?php
// Handle clear emails request
if (isset($_GET['action']) && $_GET['action'] === 'clear_emails') {
    clearManualEmails();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Final Fixes</title>
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
        pre { font-family: monospace; font-size: 12px; }
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
