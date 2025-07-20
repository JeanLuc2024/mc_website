<?php
/**
 * Complete Database Setup
 * 
 * This script creates the complete database structure for the MC booking system
 */

echo "<h2>🗄️ Complete Database Setup for MC Booking System</h2>";

// Database connection
$host = 'localhost';
$dbname = 'mc_website';
$username = 'root';
$password = '';

try {
    // First connect without database to create it if needed
    $pdo_root = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo_root->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo_root->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✅ Database '$dbname' created/verified</p>";
    
    // Now connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Connected to database '$dbname'</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h3>📋 Database Tables Structure</h3>";

// Table 1: bookings (Main booking data from frontend)
echo "<h4>1. Creating 'bookings' table...</h4>";
$bookings_sql = "
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_location VARCHAR(255) NOT NULL,
    guests INT NOT NULL,
    package VARCHAR(50) NOT NULL,
    message TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_event_date (event_date),
    INDEX idx_email (email),
    INDEX idx_booking_ref (booking_ref)
)";

try {
    $pdo->exec($bookings_sql);
    echo "<p style='color: green;'>✅ 'bookings' table created successfully</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating 'bookings' table: " . $e->getMessage() . "</p>";
}

// Table 2: admin_users (Admin panel authentication)
echo "<h4>2. Creating 'admin_users' table...</h4>";
$admin_users_sql = "
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
)";

try {
    $pdo->exec($admin_users_sql);
    echo "<p style='color: green;'>✅ 'admin_users' table created successfully</p>";
    
    // Create default admin user
    $check_admin = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'")->fetchColumn();
    if ($check_admin == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = $pdo->prepare("INSERT INTO admin_users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
        $insert_admin->execute(['admin', $admin_password, 'Administrator', 'byirival009@gmail.com', 'super_admin']);
        echo "<p style='color: blue;'>👤 Default admin user created: admin/admin123</p>";
    } else {
        echo "<p style='color: orange;'>👤 Admin user already exists</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating 'admin_users' table: " . $e->getMessage() . "</p>";
}

// Table 3: email_notifications (Track all email communications)
echo "<h4>3. Creating 'email_notifications' table...</h4>";
$email_notifications_sql = "
CREATE TABLE IF NOT EXISTS email_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    recipient_email VARCHAR(100) NOT NULL,
    recipient_name VARCHAR(100),
    email_type ENUM('booking_confirmation', 'status_update', 'admin_notification', 'custom_message') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    error_message TEXT,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_booking_id (booking_id),
    INDEX idx_recipient_email (recipient_email),
    INDEX idx_email_type (email_type),
    INDEX idx_status (status)
)";

try {
    $pdo->exec($email_notifications_sql);
    echo "<p style='color: green;'>✅ 'email_notifications' table created successfully</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating 'email_notifications' table: " . $e->getMessage() . "</p>";
}

// Table 4: admin_notifications (Real-time notifications for admin panel)
echo "<h4>4. Creating 'admin_notifications' table...</h4>";
$admin_notifications_sql = "
CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    type ENUM('new_booking', 'booking_update', 'email_sent', 'email_failed', 'system_alert') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_type (type),
    INDEX idx_is_read (is_read),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at)
)";

try {
    $pdo->exec($admin_notifications_sql);
    echo "<p style='color: green;'>✅ 'admin_notifications' table created successfully</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating 'admin_notifications' table: " . $e->getMessage() . "</p>";
}

// Table 5: website_content (Content management)
echo "<h4>5. Creating 'website_content' table...</h4>";
$website_content_sql = "
CREATE TABLE IF NOT EXISTS website_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) DEFAULT NULL,
    content TEXT DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    button_text VARCHAR(100) DEFAULT NULL,
    button_link VARCHAR(500) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

try {
    $pdo->exec($website_content_sql);
    echo "<p style='color: green;'>✅ 'website_content' table created successfully</p>";
    
    // Insert default content
    $default_content = [
        ['hero', 'Make Your Event Memorable', 'Professional Master of Ceremony for Weddings, Meetings, and Anniversary Celebrations', 'Book an Appointment', 'booking.html', 1],
        ['about', 'About Byiringiro Valentin', 'A passionate and experienced Master of Ceremony dedicated to making your events memorable and successful.', '', '', 2],
        ['services', 'Services Offered', 'Creating unforgettable moments for your special occasions', '', '', 3],
        ['contact', 'Ready to Make Your Event Special?', 'Book Byiringiro Valentin as your Master of Ceremony today!', 'Contact Us', '#contact', 4]
    ];
    
    $insert_content = $pdo->prepare("INSERT IGNORE INTO website_content (section, title, content, button_text, button_link, display_order) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($default_content as $content) {
        $insert_content->execute($content);
    }
    echo "<p style='color: blue;'>📄 Default website content added</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating 'website_content' table: " . $e->getMessage() . "</p>";
}

// Table 6: settings (System settings)
echo "<h4>6. Creating 'settings' table...</h4>";
$settings_sql = "
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_type ENUM('text', 'email', 'phone', 'url', 'textarea', 'boolean', 'number') DEFAULT 'text',
    setting_group VARCHAR(50) DEFAULT 'general',
    setting_label VARCHAR(255) DEFAULT NULL,
    setting_description TEXT DEFAULT NULL,
    is_editable BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

try {
    $pdo->exec($settings_sql);
    echo "<p style='color: green;'>✅ 'settings' table created successfully</p>";
    
    // Insert default settings
    $default_settings = [
        ['site_name', 'Byiringiro Valentin MC Services', 'text', 'general', 'Website Name', 'The name of your website/business'],
        ['admin_email', 'byirival009@gmail.com', 'email', 'contact', 'Admin Email', 'Email address for receiving notifications'],
        ['business_phone', '+123 456 7890', 'phone', 'contact', 'Business Phone', 'Main business phone number'],
        ['business_address', 'Kigali, Rwanda', 'textarea', 'contact', 'Business Address', 'Physical business address'],
        ['enable_email_notifications', '1', 'boolean', 'notifications', 'Enable Email Notifications', 'Send email notifications for new bookings'],
        ['booking_confirmation_message', 'Thank you for your booking! We will contact you shortly to confirm the details.', 'textarea', 'booking', 'Booking Confirmation Message', 'Message shown after successful booking']
    ];
    
    $insert_setting = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, setting_group, setting_label, setting_description) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($default_settings as $setting) {
        $insert_setting->execute($setting);
    }
    echo "<p style='color: blue;'>⚙️ Default settings added</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating 'settings' table: " . $e->getMessage() . "</p>";
}

// Show database summary
echo "<h3>📊 Database Summary</h3>";

$tables = ['bookings', 'admin_users', 'email_notifications', 'admin_notifications', 'website_content', 'settings'];

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🗄️ Database: mc_website</h4>";
echo "<table style='width: 100%; border-collapse: collapse; font-size: 14px;'>";
echo "<tr style='background: #e9ecef;'>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Table</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Purpose</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Records</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Status</th>";
echo "</tr>";

$table_purposes = [
    'bookings' => 'Store client booking requests from frontend',
    'admin_users' => 'Admin panel authentication and user management',
    'email_notifications' => 'Track all email communications',
    'admin_notifications' => 'Real-time notifications for admin panel',
    'website_content' => 'Manage website content through admin panel',
    'settings' => 'System configuration and business settings'
];

foreach ($tables as $table) {
    try {
        $count_stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $count_stmt->fetchColumn();
        $status = "✅ Active";
        $status_color = "#28a745";
    } catch (Exception $e) {
        $count = "Error";
        $status = "❌ Error";
        $status_color = "#dc3545";
    }
    
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'><strong>$table</strong></td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $table_purposes[$table] . "</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>$count</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd; color: $status_color;'>$status</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

// Connection details
echo "<h3>🔗 Database Connection Details</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>📋 Connection Information:</h4>";
echo "<ul>";
echo "<li><strong>Database Server:</strong> localhost (XAMPP MySQL)</li>";
echo "<li><strong>Database Name:</strong> mc_website</li>";
echo "<li><strong>Username:</strong> root</li>";
echo "<li><strong>Password:</strong> (empty)</li>";
echo "<li><strong>Port:</strong> 3306 (default)</li>";
echo "<li><strong>Charset:</strong> utf8mb4</li>";
echo "</ul>";

echo "<h4>🔧 Configuration Files:</h4>";
echo "<ul>";
echo "<li><strong>Admin Panel Config:</strong> admin/includes/config.php</li>";
echo "<li><strong>Frontend Config:</strong> php/config.php</li>";
echo "<li><strong>Booking Handler:</strong> php/booking_handler.php</li>";
echo "</ul>";
echo "</div>";

// Next steps
echo "<h3>🚀 Next Steps</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>✅ Database Setup Complete!</h4>";
echo "<p><strong>What's Ready:</strong></p>";
echo "<ol>";
echo "<li>✅ Complete database structure created</li>";
echo "<li>✅ Default admin user: admin/admin123</li>";
echo "<li>✅ Email notification system ready</li>";
echo "<li>✅ Real-time admin notifications ready</li>";
echo "<li>✅ Content management system ready</li>";
echo "<li>✅ Settings management ready</li>";
echo "</ol>";

echo "<p><strong>Test the System:</strong></p>";
echo "<ol>";
echo "<li><strong>Admin Login:</strong> <a href='../admin/' target='_blank'>http://localhost/mc_website/admin/</a></li>";
echo "<li><strong>Booking Form:</strong> <a href='../booking.html' target='_blank'>http://localhost/mc_website/booking.html</a></li>";
echo "<li><strong>Email Test:</strong> <a href='test_complete_system.php' target='_blank'>Test Complete System</a></li>";
echo "</ol>";
echo "</div>";

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Database Setup</title>
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
