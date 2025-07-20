<?php
/**
 * Setup Script for Admin Panel Enhancements
 * 
 * This script creates the necessary database tables and initial data
 * for the enhanced admin panel features.
 */

require_once 'config.php';

// Get database connection
$conn = connectDB();

if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

echo "<h2>Setting up Admin Panel Enhancements...</h2>";

try {
    // Create notifications table
    echo "<p>Creating notifications table...</p>";
    $notifications_sql = "
    CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        data JSON,
        is_read BOOLEAN DEFAULT FALSE,
        created_at DATETIME NOT NULL,
        read_at DATETIME NULL,
        INDEX idx_is_read (is_read),
        INDEX idx_created_at (created_at)
    )";
    $conn->exec($notifications_sql);
    echo "<p style='color: green;'>✓ Notifications table created successfully.</p>";

    // Create website_content table
    echo "<p>Creating website content management table...</p>";
    $content_sql = "
    CREATE TABLE IF NOT EXISTS website_content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section VARCHAR(100) NOT NULL,
        content_key VARCHAR(100) NOT NULL,
        content_value TEXT NOT NULL,
        content_type ENUM('text', 'html', 'image', 'json') DEFAULT 'text',
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by INT,
        UNIQUE KEY unique_content (section, content_key),
        INDEX idx_section (section)
    )";
    $conn->exec($content_sql);
    echo "<p style='color: green;'>✓ Website content table created successfully.</p>";

    // Insert default website content
    echo "<p>Inserting default website content...</p>";
    $default_content = [
        ['hero', 'main_title', 'Make Your Event Memorable', 'text'],
        ['hero', 'subtitle', 'With Byiringiro Valentin', 'text'],
        ['hero', 'description', 'Professional Master of Ceremony for Weddings, Meetings, and Anniversary Celebrations', 'text'],
        ['about', 'main_title', 'Meet Your MC', 'text'],
        ['about', 'description', 'Byiringiro Valentin is a professional Master of Ceremony with years of experience in hosting a wide range of events, from elegant weddings to corporate meetings and anniversary celebrations.', 'text'],
        ['about', 'experience_years', '8+', 'text'],
        ['about', 'events_hosted', '225+', 'text'],
        ['contact', 'phone', '+123 456 7890', 'text'],
        ['contact', 'email', 'valentin@mcservices.com', 'text'],
        ['contact', 'address', 'Kigali, Rwanda', 'text'],
        ['services', 'wedding_price', '500', 'text'],
        ['services', 'anniversary_price', '400', 'text'],
        ['services', 'meeting_price', '300', 'text'],
        ['footer', 'company_name', 'Byiringiro Valentin MC Services', 'text'],
        ['footer', 'tagline', 'Making your events memorable', 'text'],
        ['footer', 'copyright', '© 2025 Byiringiro Valentin MC Services. All Rights Reserved.', 'text']
    ];

    $content_insert_sql = "INSERT INTO website_content (section, content_key, content_value, content_type) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)";
    $content_stmt = $conn->prepare($content_insert_sql);

    foreach ($default_content as $content) {
        $content_stmt->execute($content);
    }
    echo "<p style='color: green;'>✓ Default website content inserted successfully.</p>";

    // Create admin activity log table
    echo "<p>Creating admin activity log table...</p>";
    $activity_sql = "
    CREATE TABLE IF NOT EXISTS admin_activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at DATETIME NOT NULL,
        INDEX idx_admin_id (admin_id),
        INDEX idx_created_at (created_at),
        INDEX idx_action (action)
    )";
    $conn->exec($activity_sql);
    echo "<p style='color: green;'>✓ Admin activity log table created successfully.</p>";

    // Create admin settings table
    echo "<p>Creating admin settings table...</p>";
    $settings_sql = "
    CREATE TABLE IF NOT EXISTS admin_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
        description TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_setting_key (setting_key)
    )";
    $conn->exec($settings_sql);
    echo "<p style='color: green;'>✓ Admin settings table created successfully.</p>";

    // Insert default admin settings
    echo "<p>Inserting default admin settings...</p>";
    $default_settings = [
        ['site_maintenance', 'false', 'boolean', 'Enable/disable site maintenance mode'],
        ['booking_enabled', 'true', 'boolean', 'Enable/disable booking functionality'],
        ['email_notifications', 'true', 'boolean', 'Enable/disable email notifications'],
        ['sms_notifications', 'false', 'boolean', 'Enable/disable SMS notifications'],
        ['max_bookings_per_day', '5', 'number', 'Maximum bookings allowed per day'],
        ['booking_advance_days', '7', 'number', 'Minimum days in advance for bookings'],
        ['admin_email', 'admin@valentinmc.com', 'text', 'Admin email address for notifications'],
        ['site_title', 'Byiringiro Valentin MC Services', 'text', 'Website title'],
        ['site_description', 'Professional Master of Ceremony Services', 'text', 'Website description'],
        ['notification_sound', 'true', 'boolean', 'Enable notification sounds in admin panel'],
        ['auto_backup', 'true', 'boolean', 'Enable automatic database backups'],
        ['session_timeout', '3600', 'number', 'Admin session timeout in seconds']
    ];

    $settings_insert_sql = "INSERT INTO admin_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    $settings_stmt = $conn->prepare($settings_insert_sql);

    foreach ($default_settings as $setting) {
        $settings_stmt->execute($setting);
    }
    echo "<p style='color: green;'>✓ Default admin settings inserted successfully.</p>";

    // Check if admin_users table exists, if not create it
    echo "<p>Checking admin users table...</p>";
    $check_admin_table = "SHOW TABLES LIKE 'admin_users'";
    $result = $conn->query($check_admin_table);
    
    if ($result->rowCount() == 0) {
        echo "<p>Creating admin users table...</p>";
        $admin_users_sql = "
        CREATE TABLE admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            role ENUM('admin', 'manager') DEFAULT 'admin',
            is_active BOOLEAN DEFAULT TRUE,
            last_login DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email)
        )";
        $conn->exec($admin_users_sql);
        
        // Insert default admin user
        $default_admin_sql = "INSERT INTO admin_users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)";
        $admin_stmt = $conn->prepare($default_admin_sql);
        $admin_stmt->execute([
            'admin',
            password_hash('admin123', PASSWORD_DEFAULT), // Change this password!
            'Administrator',
            'admin@valentinmc.com',
            'admin'
        ]);
        
        echo "<p style='color: green;'>✓ Admin users table created with default admin user.</p>";
        echo "<p style='color: orange;'><strong>⚠️ Default admin credentials: username='admin', password='admin123' - Please change this immediately!</strong></p>";
    } else {
        echo "<p style='color: green;'>✓ Admin users table already exists.</p>";
    }

    // Create a test notification
    echo "<p>Creating test notification...</p>";
    $test_notification_sql = "INSERT INTO notifications (type, title, message, created_at) VALUES (?, ?, ?, NOW())";
    $notif_stmt = $conn->prepare($test_notification_sql);
    $notif_stmt->execute([
        'system',
        'Admin Panel Enhanced!',
        'Your admin panel has been successfully enhanced with new features including notifications, content management, and activity logging.'
    ]);
    echo "<p style='color: green;'>✓ Test notification created successfully.</p>";

    echo "<h3 style='color: green;'>🎉 Setup completed successfully!</h3>";
    echo "<h4>New Features Available:</h4>";
    echo "<ul>";
    echo "<li>📧 Email notifications for new bookings</li>";
    echo "<li>🔔 Real-time notification center</li>";
    echo "<li>✏️ Content management system</li>";
    echo "<li>📊 Activity logging</li>";
    echo "<li>⚙️ Advanced settings management</li>";
    echo "</ul>";
    
    echo "<h4>Next Steps:</h4>";
    echo "<ol>";
    echo "<li>Update email settings in <code>php/config.php</code></li>";
    echo "<li>Change default admin password</li>";
    echo "<li>Test the booking notification system</li>";
    echo "<li>Customize website content through the admin panel</li>";
    echo "</ol>";
    
    echo "<p><a href='../admin/dashboard.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Panel</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Close connection
$conn = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h2 { color: #2c3e50; }
        h3 { color: #27ae60; }
        p { line-height: 1.6; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        ul, ol { line-height: 1.8; }
    </style>
</head>
<body>
</body>
</html>
