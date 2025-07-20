-- Create notifications table for admin panel
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME NOT NULL,
    read_at DATETIME NULL
);

-- Create website_content table for content management
CREATE TABLE IF NOT EXISTS website_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(100) NOT NULL,
    content_key VARCHAR(100) NOT NULL,
    content_value TEXT NOT NULL,
    content_type ENUM('text', 'html', 'image', 'json') DEFAULT 'text',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    UNIQUE KEY unique_content (section, content_key)
);

-- Insert default website content
INSERT INTO website_content (section, content_key, content_value, content_type) VALUES
('hero', 'main_title', 'Make Your Event Memorable', 'text'),
('hero', 'subtitle', 'With Byiringiro Valentin', 'text'),
('hero', 'description', 'Professional Master of Ceremony for Weddings, Meetings, and Anniversary Celebrations', 'text'),
('about', 'main_title', 'Meet Your MC', 'text'),
('about', 'description', 'Byiringiro Valentin is a professional Master of Ceremony with years of experience in hosting a wide range of events, from elegant weddings to corporate meetings and anniversary celebrations.', 'text'),
('contact', 'phone', '+123 456 7890', 'text'),
('contact', 'email', 'valentin@mcservices.com', 'text'),
('contact', 'address', 'Kigali, Rwanda', 'text'),
('services', 'wedding_price', '500', 'text'),
('services', 'anniversary_price', '400', 'text'),
('services', 'meeting_price', '300', 'text')
ON DUPLICATE KEY UPDATE content_value = VALUES(content_value);

-- Create admin activity log table
CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME NOT NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_created_at (created_at)
);

-- Create settings table for admin panel configuration
CREATE TABLE IF NOT EXISTS admin_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    description TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin settings
INSERT INTO admin_settings (setting_key, setting_value, setting_type, description) VALUES
('site_maintenance', 'false', 'boolean', 'Enable/disable site maintenance mode'),
('booking_enabled', 'true', 'boolean', 'Enable/disable booking functionality'),
('email_notifications', 'true', 'boolean', 'Enable/disable email notifications'),
('sms_notifications', 'false', 'boolean', 'Enable/disable SMS notifications'),
('max_bookings_per_day', '5', 'number', 'Maximum bookings allowed per day'),
('booking_advance_days', '7', 'number', 'Minimum days in advance for bookings'),
('admin_email', 'admin@valentinmc.com', 'text', 'Admin email address for notifications'),
('site_title', 'Byiringiro Valentin MC Services', 'text', 'Website title'),
('site_description', 'Professional Master of Ceremony Services', 'text', 'Website description')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
