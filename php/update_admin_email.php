<?php
/**
 * Update Admin Email to byirival009@gmail.com
 * 
 * This script updates the admin email across the system
 */

echo "<h2>📧 Updating Admin Email to: byirival009@gmail.com</h2>";

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h3>1. Updating Admin User Email</h3>";

try {
    // Update admin user email
    $stmt = $pdo->prepare("UPDATE admin_users SET email = ? WHERE username = 'admin'");
    $stmt->execute(['byirival009@gmail.com']);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Admin user email updated successfully</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No admin user found to update</p>";
    }
    
    // Verify the update
    $stmt = $pdo->prepare("SELECT username, email, full_name FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>👤 Updated Admin User:</h4>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> {$admin['username']}</li>";
        echo "<li><strong>Email:</strong> {$admin['email']}</li>";
        echo "<li><strong>Full Name:</strong> {$admin['full_name']}</li>";
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error updating admin user: " . $e->getMessage() . "</p>";
}

echo "<h3>2. Updating Settings Table</h3>";

try {
    // Update admin email in settings
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'admin_email'");
    $stmt->execute(['byirival009@gmail.com']);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Admin email setting updated successfully</p>";
    } else {
        // Insert if doesn't exist
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, setting_group, setting_label, setting_description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'admin_email',
            'byirival009@gmail.com',
            'email',
            'contact',
            'Admin Email',
            'Email address for receiving notifications'
        ]);
        echo "<p style='color: green;'>✅ Admin email setting created successfully</p>";
    }
    
    // Verify settings
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key = 'admin_email'");
    $stmt->execute();
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($setting) {
        echo "<p style='color: green;'>✅ Verified: {$setting['setting_key']} = {$setting['setting_value']}</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error updating settings: " . $e->getMessage() . "</p>";
}

echo "<h3>3. System Configuration Updated</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📧 Email Configuration Updated:</h4>";
echo "<ul>";
echo "<li><strong>Admin Email:</strong> byirival009@gmail.com</li>";
echo "<li><strong>SMTP Username:</strong> byirival009@gmail.com</li>";
echo "<li><strong>SMTP From Email:</strong> byirival009@gmail.com</li>";
echo "<li><strong>Reply-To Email:</strong> byirival009@gmail.com</li>";
echo "</ul>";
echo "</div>";

echo "<h3>4. Files Updated</h3>";

$updated_files = [
    'php/config.php' => 'Main configuration file with email constants',
    'php/notification_system.php' => 'Email notification system',
    'php/complete_database_setup.php' => 'Database setup script',
    'Database admin_users table' => 'Admin user email field',
    'Database settings table' => 'Admin email setting'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📁 Updated Files and Database:</h4>";
echo "<ul>";
foreach ($updated_files as $file => $description) {
    echo "<li><strong>{$file}:</strong> {$description}</li>";
}
echo "</ul>";
echo "</div>";

echo "<h3>5. Testing Email System</h3>";

// Test the notification system with new email
require_once 'notification_system.php';

// Create a test booking for email testing
$test_booking = [
    'id' => 999,
    'booking_ref' => 'EMAIL-TEST-' . date('ymd-His'),
    'name' => 'Email Test Client',
    'email' => 'test@example.com',
    'phone' => '+250123456789',
    'event_date' => date('Y-m-d', strtotime('+7 days')),
    'event_time' => '14:00',
    'event_type' => 'Email System Test',
    'event_location' => 'Test Location',
    'guests' => 50,
    'package' => 'Premium Package',
    'message' => 'This is a test to verify the new admin email is working.'
];

echo "<p><strong>Testing admin notification email to: byirival009@gmail.com</strong></p>";

$admin_email_result = sendAdminBookingNotification($test_booking);

if ($admin_email_result) {
    echo "<p style='color: green;'>✅ Test admin notification email sent successfully to byirival009@gmail.com</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Test email failed (mail server may not be configured)</p>";
    echo "<p>Email would be sent to: <strong>byirival009@gmail.com</strong></p>";
}

echo "<h3>6. Summary</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>✅ Admin Email Update Complete!</h4>";

echo "<h5>📧 New Email Configuration:</h5>";
echo "<ul>";
echo "<li><strong>Admin Email:</strong> byirival009@gmail.com</li>";
echo "<li><strong>Purpose:</strong> Receive all booking notifications</li>";
echo "<li><strong>Updated In:</strong> Database, config files, and email templates</li>";
echo "</ul>";

echo "<h5>🔔 What Will Happen Now:</h5>";
echo "<ul>";
echo "<li>✅ <strong>New booking notifications</strong> will be sent to byirival009@gmail.com</li>";
echo "<li>✅ <strong>Admin alerts</strong> will go to the new email address</li>";
echo "<li>✅ <strong>Client emails</strong> will have byirival009@gmail.com as reply-to</li>";
echo "<li>✅ <strong>System notifications</strong> will use the new email</li>";
echo "</ul>";

echo "<h5>🧪 Test the System:</h5>";
echo "<ol>";
echo "<li><strong>Submit a booking:</strong> <a href='../booking.html' target='_blank'>Booking Form</a></li>";
echo "<li><strong>Check email:</strong> Look for notification at byirival009@gmail.com</li>";
echo "<li><strong>Admin panel:</strong> <a href='../admin/dashboard.php' target='_blank'>View Dashboard</a></li>";
echo "<li><strong>Manage booking:</strong> Update status and send client notification</li>";
echo "</ol>";

echo "<h5>📱 Email Setup (Optional):</h5>";
echo "<p>For production use, you may want to:</p>";
echo "<ul>";
echo "<li>Configure Gmail SMTP with app password for byirival009@gmail.com</li>";
echo "<li>Update SMTP_PASSWORD in config.php with your Gmail app password</li>";
echo "<li>Enable 2-factor authentication on the Gmail account</li>";
echo "</ul>";

echo "</div>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🚀 Ready to Test!</h4>";
echo "<p><strong>Your system is now configured to send all admin notifications to:</strong></p>";
echo "<p style='font-size: 1.2em; font-weight: bold; color: #2c3e50;'>📧 byirival009@gmail.com</p>";

echo "<p><strong>Test the complete workflow:</strong></p>";
echo "<ol>";
echo "<li>Submit a test booking from the frontend</li>";
echo "<li>Check byirival009@gmail.com for admin notification</li>";
echo "<li>Login to admin panel and manage the booking</li>";
echo "<li>Update booking status and verify client receives notification</li>";
echo "</ol>";

echo "<p><strong>All email notifications will now go to your new email address!</strong> 🎉</p>";
echo "</div>";

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Admin Email</title>
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
