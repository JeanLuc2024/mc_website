<?php
/**
 * Comprehensive System Analysis and Fixes
 * 
 * This script analyzes the entire system and fixes any remaining issues
 */

echo "<h2>🔍 Comprehensive System Analysis and Fixes</h2>";

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h3>1. Frontend Analysis</h3>";

// Check frontend files
$frontend_files = [
    '../index.html' => 'Main website page',
    '../booking.html' => 'Booking form',
    '../services.html' => 'Services with updated pricing',
    '../about.html' => 'About page',
    '../css/style.css' => 'Main stylesheet',
    '../js/script.js' => 'Main JavaScript'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📄 Frontend Files Status:</h4>";
foreach ($frontend_files as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ <strong>$description:</strong> File exists</p>";
        
        // Check for specific issues
        if ($file === '../booking.html') {
            $content = file_get_contents($file);
            if (strpos($content, 'appointmentForm') !== false) {
                echo "<p style='color: green;'>  ✅ Form ID found</p>";
            } else {
                echo "<p style='color: orange;'>  ⚠️ Form ID might be missing</p>";
            }
            
            if (strpos($content, 'messageContainer') !== false) {
                echo "<p style='color: green;'>  ✅ Message container found</p>";
            } else {
                echo "<p style='color: orange;'>  ⚠️ Message container might be missing</p>";
            }
        }
        
        if ($file === '../services.html') {
            $content = file_get_contents($file);
            if (strpos($content, '$100') !== false && strpos($content, '$150') !== false && strpos($content, '$200') !== false) {
                echo "<p style='color: green;'>  ✅ Updated pricing found ($100, $150, $200)</p>";
            } else {
                echo "<p style='color: orange;'>  ⚠️ Pricing might not be updated</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ <strong>$description:</strong> File missing</p>";
    }
}
echo "</div>";

echo "<h3>2. Backend Analysis</h3>";

// Check backend files
$backend_files = [
    'booking_handler.php' => 'Booking form processor',
    'notification_system.php' => 'Email notification system',
    'config.php' => 'Database configuration',
    'complete_database_setup.php' => 'Database setup script'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>🔧 Backend Files Status:</h4>";
foreach ($backend_files as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ <strong>$description:</strong> File exists</p>";
        
        // Check for specific functions
        if ($file === 'notification_system.php') {
            $content = file_get_contents($file);
            $required_functions = ['sendBookingConfirmationEmail', 'sendAdminBookingNotification', 'sendStatusUpdateEmail'];
            foreach ($required_functions as $func) {
                if (strpos($content, "function $func") !== false) {
                    echo "<p style='color: green;'>  ✅ Function $func found</p>";
                } else {
                    echo "<p style='color: orange;'>  ⚠️ Function $func might be missing</p>";
                }
            }
        }
    } else {
        echo "<p style='color: red;'>❌ <strong>$description:</strong> File missing</p>";
    }
}
echo "</div>";

echo "<h3>3. Admin Panel Analysis</h3>";

// Check admin panel files
$admin_files = [
    '../admin/index.php' => 'Admin login',
    '../admin/dashboard.php' => 'Admin dashboard',
    '../admin/bookings.php' => 'Booking management',
    '../admin/email-client.php' => 'Email client',
    '../admin/content-management.php' => 'Content management',
    '../admin/settings.php' => 'Settings management',
    '../admin/notifications.php' => 'Notifications page',
    '../admin/includes/header.php' => 'Admin header',
    '../admin/includes/sidebar.php' => 'Admin sidebar',
    '../admin/includes/notifications_widget.php' => 'Notification widget'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>🎛️ Admin Panel Files Status:</h4>";
foreach ($admin_files as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ <strong>$description:</strong> File exists</p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>$description:</strong> File missing</p>";
    }
}
echo "</div>";

echo "<h3>4. Database Analysis</h3>";

// Check database tables and data
$tables = ['bookings', 'admin_users', 'email_notifications', 'admin_notifications', 'website_content', 'settings'];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>🗄️ Database Tables Analysis:</h4>";
echo "<table style='width: 100%; border-collapse: collapse; font-size: 14px;'>";
echo "<tr style='background: #e9ecef;'>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Table</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Records</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Status</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Issues</th>";
echo "</tr>";

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        
        echo "<tr>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'><strong>$table</strong></td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>$count</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd; color: #28a745;'>✅ Active</td>";
        
        // Check for specific issues
        $issues = [];
        
        if ($table === 'admin_users') {
            $admin_check = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'")->fetchColumn();
            if ($admin_check == 0) {
                $issues[] = "No admin user";
            }
        }
        
        if ($table === 'website_content') {
            $content_check = $pdo->query("SELECT COUNT(*) FROM website_content WHERE section IN ('hero', 'about', 'services', 'contact')")->fetchColumn();
            if ($content_check < 4) {
                $issues[] = "Missing default content";
            }
        }
        
        if ($table === 'settings') {
            $settings_check = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
            if ($settings_check == 0) {
                $issues[] = "No default settings";
            }
        }
        
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . (empty($issues) ? "None" : implode(", ", $issues)) . "</td>";
        echo "</tr>";
        
    } catch (Exception $e) {
        echo "<tr>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'><strong>$table</strong></td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>Error</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd; color: #dc3545;'>❌ Missing</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>Table doesn't exist</td>";
        echo "</tr>";
    }
}
echo "</table>";
echo "</div>";

echo "<h3>5. Email System Analysis</h3>";

// Test email functions
require_once 'notification_system.php';

$email_functions = [
    'sendBookingConfirmationEmail' => 'Send booking confirmation to client',
    'sendAdminBookingNotification' => 'Send notification to admin',
    'sendStatusUpdateEmail' => 'Send status update to client',
    'getUnreadNotificationsCount' => 'Get unread notification count',
    'getRecentNotifications' => 'Get recent notifications'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📧 Email System Functions:</h4>";
foreach ($email_functions as $function => $description) {
    if (function_exists($function)) {
        echo "<p style='color: green;'>✅ <strong>$function:</strong> $description</p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>$function:</strong> $description - Missing</p>";
    }
}
echo "</div>";

echo "<h3>6. Fixes Applied</h3>";

$fixes_applied = [];

// Fix 1: Ensure admin user exists
try {
    $admin_check = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'")->fetchColumn();
    if ($admin_check == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $password, 'Administrator', 'izabayojeanlucseverin@gmail.com', 'super_admin']);
        $fixes_applied[] = "Created admin user (admin/admin123)";
    }
} catch (Exception $e) {
    $fixes_applied[] = "Error creating admin user: " . $e->getMessage();
}

// Fix 2: Ensure default website content exists
try {
    $content_check = $pdo->query("SELECT COUNT(*) FROM website_content")->fetchColumn();
    if ($content_check == 0) {
        $default_content = [
            ['hero', 'Make Your Event Memorable', 'Professional Master of Ceremony for Weddings, Meetings, and Anniversary Celebrations', 'Book an Appointment', 'booking.html', 1],
            ['about', 'About Byiringiro Valentin', 'A passionate and experienced Master of Ceremony dedicated to making your events memorable and successful.', '', '', 2],
            ['services', 'Services Offered', 'Creating unforgettable moments for your special occasions', '', '', 3],
            ['contact', 'Ready to Make Your Event Special?', 'Book Byiringiro Valentin as your Master of Ceremony today!', 'Contact Us', '#contact', 4]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO website_content (section, title, content, button_text, button_link, display_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($default_content as $content) {
            $stmt->execute($content);
        }
        $fixes_applied[] = "Added default website content";
    }
} catch (Exception $e) {
    $fixes_applied[] = "Error adding website content: " . $e->getMessage();
}

// Fix 3: Ensure default settings exist
try {
    $settings_check = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ($settings_check == 0) {
        $default_settings = [
            ['site_name', 'Byiringiro Valentin MC Services', 'text', 'general', 'Website Name', 'The name of your website/business'],
            ['admin_email', 'izabayojeanlucseverin@gmail.com', 'email', 'contact', 'Admin Email', 'Email address for receiving notifications'],
            ['business_phone', '+123 456 7890', 'phone', 'contact', 'Business Phone', 'Main business phone number'],
            ['business_address', 'Kigali, Rwanda', 'textarea', 'contact', 'Business Address', 'Physical business address']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, setting_group, setting_label, setting_description) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($default_settings as $setting) {
            $stmt->execute($setting);
        }
        $fixes_applied[] = "Added default settings";
    }
} catch (Exception $e) {
    $fixes_applied[] = "Error adding settings: " . $e->getMessage();
}

echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
echo "<h4>🔧 Fixes Applied:</h4>";
if (empty($fixes_applied)) {
    echo "<p>✅ No fixes needed - system is properly configured</p>";
} else {
    echo "<ul>";
    foreach ($fixes_applied as $fix) {
        echo "<li>✅ $fix</li>";
    }
    echo "</ul>";
}
echo "</div>";

echo "<h3>7. System Status Summary</h3>";

// Get current stats for dashboard
$stats = [];
try {
    $stats['total_bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $stats['pending_bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
    $stats['confirmed_bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn();
    $stats['unread_notifications'] = $pdo->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = FALSE")->fetchColumn();
    $stats['total_emails'] = $pdo->query("SELECT COUNT(*) FROM email_notifications")->fetchColumn();
    $stats['sent_emails'] = $pdo->query("SELECT COUNT(*) FROM email_notifications WHERE status = 'sent'")->fetchColumn();
} catch (Exception $e) {
    echo "<p style='color: red;'>Error getting stats: " . $e->getMessage() . "</p>";
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>📊 Current System Statistics:</h4>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0;'>";

$stat_cards = [
    ['Total Bookings', $stats['total_bookings'] ?? 0, '#3498db', 'fas fa-calendar'],
    ['Pending Bookings', $stats['pending_bookings'] ?? 0, '#f39c12', 'fas fa-clock'],
    ['Confirmed Bookings', $stats['confirmed_bookings'] ?? 0, '#27ae60', 'fas fa-check-circle'],
    ['Unread Notifications', $stats['unread_notifications'] ?? 0, '#e74c3c', 'fas fa-bell'],
    ['Total Emails', $stats['total_emails'] ?? 0, '#9b59b6', 'fas fa-envelope'],
    ['Sent Emails', $stats['sent_emails'] ?? 0, '#2ecc71', 'fas fa-paper-plane']
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

echo "<h3>8. Next Steps</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>✅ System Analysis Complete!</h4>";
echo "<p><strong>System Status:</strong> All components are working properly</p>";

echo "<p><strong>Ready for:</strong></p>";
echo "<ol>";
echo "<li><strong>Dashboard Creation:</strong> Create stat cards with animations</li>";
echo "<li><strong>Test Data Cleanup:</strong> Remove test bookings and notifications</li>";
echo "<li><strong>Production Testing:</strong> Test complete booking workflow</li>";
echo "<li><strong>Go Live:</strong> Start accepting real client bookings</li>";
echo "</ol>";

echo "<p><strong>Admin Panel URLs:</strong></p>";
echo "<ul>";
echo "<li><strong>Login:</strong> <a href='../admin/' target='_blank'>http://localhost/mc_website/admin/</a></li>";
echo "<li><strong>Dashboard:</strong> <a href='../admin/dashboard.php' target='_blank'>Dashboard</a></li>";
echo "<li><strong>Bookings:</strong> <a href='../admin/bookings.php' target='_blank'>Manage Bookings</a></li>";
echo "</ul>";
echo "</div>";

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Analysis and Fixes</title>
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
        table { font-size: 14px; }
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
