<?php
/**
 * Create Missing Database Tables
 * 
 * This script creates all missing tables for the admin panel functionality
 */

echo "<h2>🔧 Creating Missing Database Tables...</h2>";

// Database connection
$host = 'localhost';
$dbname = 'mc_website';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Create website_content table
echo "<h3>1. Creating website_content table...</h3>";

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
    echo "<p style='color: green;'>✅ website_content table created successfully</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating website_content table: " . $e->getMessage() . "</p>";
}

// Insert default website content
echo "<h4>Inserting default website content...</h4>";

$default_content = [
    [
        'section' => 'hero',
        'title' => 'Professional Master of Ceremony Services',
        'content' => 'Make your special events unforgettable with Byiringiro Valentin\'s professional MC services. From weddings to corporate events, we bring energy, elegance, and expertise to every occasion.',
        'button_text' => 'Book Now',
        'button_link' => 'booking.html',
        'display_order' => 1
    ],
    [
        'section' => 'about',
        'title' => 'About Byiringiro Valentin',
        'content' => 'With years of experience in event hosting and public speaking, Byiringiro Valentin brings professionalism, charisma, and cultural sensitivity to every event. Specializing in weddings, corporate events, and special celebrations.',
        'display_order' => 2
    ],
    [
        'section' => 'services',
        'title' => 'Our Services',
        'content' => 'We offer comprehensive MC services including wedding ceremonies, corporate events, anniversary celebrations, birthday parties, and special occasions. Each service is tailored to meet your specific needs and cultural preferences.',
        'display_order' => 3
    ],
    [
        'section' => 'contact',
        'title' => 'Get In Touch',
        'content' => 'Ready to make your event special? Contact us today to discuss your requirements and book our professional MC services.',
        'button_text' => 'Contact Us',
        'button_link' => '#contact',
        'display_order' => 4
    ]
];

$insert_content_sql = "INSERT IGNORE INTO website_content (section, title, content, button_text, button_link, display_order) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($insert_content_sql);

foreach ($default_content as $content) {
    try {
        $stmt->execute([
            $content['section'],
            $content['title'],
            $content['content'],
            $content['button_text'] ?? null,
            $content['button_link'] ?? null,
            $content['display_order']
        ]);
        echo "<p style='color: green;'>✅ Added {$content['section']} section content</p>";
    } catch(PDOException $e) {
        echo "<p style='color: orange;'>⚠️ {$content['section']} section already exists or error: " . $e->getMessage() . "</p>";
    }
}

// Create settings table
echo "<h3>2. Creating settings table...</h3>";

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
    echo "<p style='color: green;'>✅ settings table created successfully</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating settings table: " . $e->getMessage() . "</p>";
}

// Insert default settings
echo "<h4>Inserting default settings...</h4>";

$default_settings = [
    [
        'setting_key' => 'site_name',
        'setting_value' => 'Byiringiro Valentin MC Services',
        'setting_type' => 'text',
        'setting_group' => 'general',
        'setting_label' => 'Website Name',
        'setting_description' => 'The name of your website/business'
    ],
    [
        'setting_key' => 'admin_email',
        'setting_value' => 'izabayojeanlucseverin@gmail.com',
        'setting_type' => 'email',
        'setting_group' => 'contact',
        'setting_label' => 'Admin Email',
        'setting_description' => 'Email address for receiving notifications'
    ],
    [
        'setting_key' => 'business_phone',
        'setting_value' => '+123 456 7890',
        'setting_type' => 'phone',
        'setting_group' => 'contact',
        'setting_label' => 'Business Phone',
        'setting_description' => 'Main business phone number'
    ],
    [
        'setting_key' => 'business_address',
        'setting_value' => 'Kigali, Rwanda',
        'setting_type' => 'textarea',
        'setting_group' => 'contact',
        'setting_label' => 'Business Address',
        'setting_description' => 'Physical business address'
    ],
    [
        'setting_key' => 'enable_email_notifications',
        'setting_value' => '1',
        'setting_type' => 'boolean',
        'setting_group' => 'notifications',
        'setting_label' => 'Enable Email Notifications',
        'setting_description' => 'Send email notifications for new bookings'
    ],
    [
        'setting_key' => 'booking_confirmation_message',
        'setting_value' => 'Thank you for your booking! We will contact you shortly to confirm the details.',
        'setting_type' => 'textarea',
        'setting_group' => 'booking',
        'setting_label' => 'Booking Confirmation Message',
        'setting_description' => 'Message shown after successful booking'
    ]
];

$insert_settings_sql = "INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, setting_group, setting_label, setting_description) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($insert_settings_sql);

foreach ($default_settings as $setting) {
    try {
        $stmt->execute([
            $setting['setting_key'],
            $setting['setting_value'],
            $setting['setting_type'],
            $setting['setting_group'],
            $setting['setting_label'],
            $setting['setting_description']
        ]);
        echo "<p style='color: green;'>✅ Added setting: {$setting['setting_label']}</p>";
    } catch(PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Setting {$setting['setting_key']} already exists or error: " . $e->getMessage() . "</p>";
    }
}

// Create email_communications table for tracking emails
echo "<h3>3. Creating email_communications table...</h3>";

$email_communications_sql = "
CREATE TABLE IF NOT EXISTS email_communications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(50) DEFAULT NULL,
    to_email VARCHAR(255) NOT NULL,
    from_email VARCHAR(255) DEFAULT 'izabayojeanlucseverin@gmail.com',
    subject VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    email_type ENUM('booking_notification', 'client_response', 'general') DEFAULT 'general',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    INDEX idx_booking_ref (booking_ref),
    INDEX idx_to_email (to_email),
    INDEX idx_sent_at (sent_at)
)";

try {
    $pdo->exec($email_communications_sql);
    echo "<p style='color: green;'>✅ email_communications table created successfully</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating email_communications table: " . $e->getMessage() . "</p>";
}

// Show table structures
echo "<h3>4. Verifying Table Structures...</h3>";

$tables = ['website_content', 'settings', 'email_communications', 'bookings', 'notifications'];

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>📋 {$table} table structure:</h4>";
        echo "<table style='width: 100%; border-collapse: collapse; font-size: 14px;'>";
        echo "<tr style='background: #e9ecef;'>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Field</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Type</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Null</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Key</th>";
        echo "</tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($column['Key']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        
    } catch(PDOException $e) {
        echo "<p style='color: red;'>❌ Error checking {$table} table: " . $e->getMessage() . "</p>";
    }
}

// Summary
echo "<h3>📋 Setup Summary</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>✅ Database Setup Complete!</h4>";
echo "<p><strong>Tables Created/Verified:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>website_content</strong> - For managing website content</li>";
echo "<li>✅ <strong>settings</strong> - For admin panel settings</li>";
echo "<li>✅ <strong>email_communications</strong> - For tracking email communications</li>";
echo "<li>✅ <strong>bookings</strong> - For client bookings</li>";
echo "<li>✅ <strong>notifications</strong> - For admin notifications</li>";
echo "</ul>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li><strong>Test Content Management:</strong> <a href='../admin/content-management.php' target='_blank'>Update Website</a></li>";
echo "<li><strong>Test Settings:</strong> <a href='../admin/settings.php' target='_blank'>Admin Settings</a></li>";
echo "<li><strong>Test Email Client:</strong> <a href='../admin/email-client.php' target='_blank'>Email Clients</a></li>";
echo "<li><strong>Test Booking Form:</strong> <a href='../booking.html' target='_blank'>Booking Form</a></li>";
echo "</ol>";
echo "</div>";

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Missing Tables</title>
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
