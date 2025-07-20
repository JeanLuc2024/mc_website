-- Settings Table Creation Script

-- Check if table exists and create if it doesn't
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings if they don't exist
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('contact_email', 'valentin@mcservices.com'),
('contact_phone', '+123 456 7890'),
('contact_address', 'Kigali, Rwanda'),
('facebook_url', 'https://facebook.com/valentinmc'),
('instagram_url', 'https://instagram.com/valentinmc'),
('twitter_url', 'https://twitter.com/valentinmc'),
('youtube_url', 'https://youtube.com/valentinmc'),
('business_name', 'Byiringiro Valentin MC Services'),
('business_tagline', 'Making Your Events Memorable'),
('business_description', 'Professional Master of Ceremony services for weddings, corporate events, and special occasions.');