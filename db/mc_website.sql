-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2025 at 09:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mc_website`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `type` enum('new_booking','booking_update','email_sent','email_failed','system_alert') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `booking_id`, `type`, `title`, `message`, `is_read`, `priority`, `created_at`, `read_at`) VALUES
(1, 2, 'new_booking', 'New Booking Received', 'New booking from Sokrate Francis for wedding on Jul 20, 2025', 0, 'high', '2025-07-20 13:29:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','super_admin') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `is_active`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$EVIBRfRvy15W/qbNfZyPWOPYD1/Rlp3RlES.4IbmcrLQv3vpXvSNO', 'Administrator', 'byirival009@gmail.com', 'super_admin', 1, '2025-07-20 13:14:12', '2025-07-20 19:34:31', '2025-07-20 19:34:31');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_ref` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_location` varchar(255) NOT NULL,
  `guests` int(11) NOT NULL,
  `package` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_ref`, `name`, `email`, `phone`, `event_date`, `event_time`, `event_type`, `event_location`, `guests`, `package`, `message`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 'BK-C8FE3372', 'Test Client', 'test@example.com', '+250123456789', '2025-07-27', '14:00:00', 'Wedding', 'Kigali Convention Centre', 150, 'Premium', 'This is a test booking', 'pending', NULL, '2025-07-20 13:15:36', '2025-07-20 13:15:36'),
(2, 'MC-250720-333165', 'Sokrate Francis', 'izabayojeanluc12@gmail.com', '0790635888', '2025-07-20', '19:09:00', 'wedding', 'Yaounde', 100, 'basic', 'be there plz', 'pending', '', '2025-07-20 13:29:36', '2025-07-20 13:49:34');

-- --------------------------------------------------------

--
-- Table structure for table `booking_history`
--

CREATE TABLE `booking_history` (
  `id` int(11) NOT NULL,
  `original_booking_id` int(11) NOT NULL,
  `booking_ref` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_location` varchar(255) NOT NULL,
  `guests` int(11) NOT NULL,
  `package` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'completed',
  `admin_notes` text DEFAULT NULL,
  `moved_to_history_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `original_created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_history`
--

INSERT INTO `booking_history` (`id`, `original_booking_id`, `booking_ref`, `name`, `email`, `phone`, `event_date`, `event_time`, `event_type`, `event_location`, `guests`, `package`, `message`, `status`, `admin_notes`, `moved_to_history_at`, `original_created_at`) VALUES
(3, 5, 'MC-250720-5F8ED0', 'NTARINDWA Christian', 'izabayojeanlucseverin@gmail.com', '0788605734', '2025-07-21', '22:00:00', 'wedding', 'Susa', 890, 'basic', 'uzaze hakiri kare bro', 'completed', '', '2025-07-20 19:34:58', '2025-07-20 19:20:22');

-- --------------------------------------------------------

--
-- Table structure for table `email_notifications`
--

CREATE TABLE `email_notifications` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `recipient_email` varchar(100) NOT NULL,
  `recipient_name` varchar(100) DEFAULT NULL,
  `email_type` enum('booking_confirmation','status_update','admin_notification','custom_message') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_notifications`
--

INSERT INTO `email_notifications` (`id`, `booking_id`, `recipient_email`, `recipient_name`, `email_type`, `subject`, `message`, `status`, `sent_at`, `created_at`, `error_message`) VALUES
(1, 2, 'izabayojeanluc12@gmail.com', NULL, '', 'Response to your booking - MC-250720-333165', 'no problem I will catch up', 'sent', '2025-07-20 13:47:46', '2025-07-20 13:47:46', NULL),
(2, NULL, 'izabayojeanlucseverin@gmail.com', NULL, '', 'Response to your booking - MC-250720-6F5A9B', 'ntaribi mwan', 'sent', '2025-07-20 15:02:51', '2025-07-20 15:02:51', NULL),
(3, NULL, 'izabayojeanluc12@gmail.com', NULL, '', 'Response to your booking - MC-250720-603BA7', 'ntaribi tuzabihuza', 'sent', '2025-07-20 16:14:21', '2025-07-20 16:14:21', NULL),
(4, NULL, 'izabayojeanlucseverin@gmail.com', NULL, '', 'Response to your booking - MC-250720-6F5A9B', 'hyyyyy', 'sent', '2025-07-20 16:48:50', '2025-07-20 16:48:50', NULL),
(5, NULL, 'izabayojeanlucseverin@gmail.com', NULL, '', 'Response to your booking - MC-250720-5F8ED0', 'I got you bro', 'sent', '2025-07-20 19:34:58', '2025-07-20 19:34:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','email','phone','url','textarea','boolean','number') DEFAULT 'text',
  `setting_group` varchar(50) DEFAULT 'general',
  `setting_label` varchar(255) DEFAULT NULL,
  `setting_description` text DEFAULT NULL,
  `is_editable` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_label`, `setting_description`, `is_editable`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Byiringiro Valentin MC Services', 'text', 'general', 'Website Name', 'The name of your website/business', 1, '2025-07-20 13:14:12', '2025-07-20 13:14:12'),
(2, 'admin_email', 'byirival009@gmail.com', 'email', 'contact', 'Admin Email', 'Email address for receiving notifications', 1, '2025-07-20 13:14:12', '2025-07-20 13:14:12'),
(3, 'business_phone', '+123 456 7890', 'phone', 'contact', 'Business Phone', 'Main business phone number', 1, '2025-07-20 13:14:13', '2025-07-20 13:14:13'),
(4, 'business_address', 'Kigali, Rwanda', 'textarea', 'contact', 'Business Address', 'Physical business address', 1, '2025-07-20 13:14:13', '2025-07-20 13:14:13'),
(5, 'enable_email_notifications', '1', 'boolean', 'notifications', 'Enable Email Notifications', 'Send email notifications for new bookings', 1, '2025-07-20 13:14:13', '2025-07-20 13:14:13'),
(6, 'booking_confirmation_message', 'Thank you for your booking! We will contact you shortly to confirm the details.', 'textarea', 'booking', 'Booking Confirmation Message', 'Message shown after successful booking', 1, '2025-07-20 13:14:13', '2025-07-20 13:14:13'),
(7, 'contact_email', 'byirival009@gmail.com', 'text', 'general', 'Contact Email', NULL, 1, '2025-07-20 13:57:12', '2025-07-20 14:10:32'),
(8, 'contact_phone', '0788764456', 'text', 'general', 'Contact Phone', NULL, 1, '2025-07-20 13:57:12', '2025-07-20 14:10:32'),
(9, 'contact_address', '', 'text', 'general', 'Contact Address', NULL, 1, '2025-07-20 13:57:12', '2025-07-20 14:10:32'),
(10, 'facebook_url', '', 'text', 'general', 'Facebook Url', NULL, 1, '2025-07-20 13:57:12', '2025-07-20 14:10:32'),
(11, 'instagram_url', '', 'text', 'general', 'Instagram Url', NULL, 1, '2025-07-20 13:57:12', '2025-07-20 14:10:32'),
(12, 'twitter_url', '', 'text', 'general', 'Twitter Url', NULL, 1, '2025-07-20 13:57:12', '2025-07-20 14:10:32'),
(13, 'youtube_url', '', 'text', 'general', 'Youtube Url', NULL, 1, '2025-07-20 13:57:12', '2025-07-20 14:10:32'),
(14, 'business_name', '', 'text', 'general', 'Business Name', NULL, 1, '2025-07-20 13:57:12', '2025-07-20 14:10:32'),
(15, 'business_tagline', '', 'text', 'general', 'Business Tagline', NULL, 1, '2025-07-20 13:57:12', '2025-07-20 14:10:32'),
(16, 'business_description', '', 'text', 'general', 'Business Description', NULL, 1, '2025-07-20 13:57:12', '2025-07-20 14:10:32');

-- --------------------------------------------------------

--
-- Table structure for table `website_content`
--

CREATE TABLE `website_content` (
  `id` int(11) NOT NULL,
  `section` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `website_content`
--

INSERT INTO `website_content` (`id`, `section`, `title`, `content`, `image_url`, `button_text`, `button_link`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(8, 'hero', 'You want your celemoies be memorable', 'This is your time to make it happen.', NULL, 'Book Now', 'booking.html', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11'),
(9, 'about_intro', 'Meet Your MC', 'Byiringiro Valentin is a professional Master of Ceremony with years of experience in hosting a wide range of events, from elegant weddings to corporate meetings and anniversary celebrations.\n\nWith a natural charisma, excellent communication skills, and a keen attention to detail, Valentin ensures that every event runs smoothly and creates memorable experiences for all attendees.\n\nHis ability to engage audiences, manage event timelines, and adapt to unexpected situations makes him the perfect choice for your special occasion.', NULL, '', '', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11'),
(10, 'about_stats', 'Experience Statistics', '100+ Weddings, 75+ Corporate Events, 50+ Anniversaries', NULL, '', '', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11'),
(11, 'why_choose', 'Why Choose Valentin?', 'What sets Byiringiro Valentin apart as your Master of Ceremony', NULL, '', '', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11'),
(12, 'services_preview', 'Our Seviceeeeee', 'Professional MC services for all your special occasions', NULL, 'View All Services', 'services.html', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:27:28'),
(13, 'service_wedding', 'Wedding Ceremonies', 'Make your special day unforgettable with professional MC services that ensure smooth transitions, engaging entertainment, and memorable moments for you and your guests.', NULL, 'Book Wedding MC', 'booking.html', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11'),
(14, 'service_anniversary', 'Anniversary Celebrations', 'Celebrate your milestone moments with elegance and style. Professional hosting that honors your journey and creates new memories with family and friends.', NULL, 'Book Anniversary MC', 'booking.html', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11'),
(15, 'service_corporate', 'Corporate Events', 'Professional hosting for business meetings, conferences, and corporate celebrations. Maintain professionalism while keeping your audience engaged.', NULL, 'Book Corporate MC', 'booking.html', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11'),
(16, 'cta', 'Ready to Make Your Event Memorable?', 'Book Byiringiro Valentin as your Master of Ceremony today!', NULL, 'Book Now', 'booking.html', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11'),
(17, 'footer', 'ValentinMC', 'Making your events memorable', NULL, '', '', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11'),
(18, 'contact_intro', 'Get In Touch', 'Ready to make your event unforgettable? Contact us today to discuss your MC needs and book Byiringiro Valentin for your special occasion.', NULL, '', '', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11'),
(19, 'gallery_intro', 'Event Gallery', 'Take a look at some of the memorable events where Byiringiro Valentin has served as Master of Ceremony.', NULL, '', '', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11'),
(20, 'booking_intro', 'Book Your MC', 'Ready to make your event memorable? Fill out the form below and we\'ll get back to you within 24 hours to discuss your event details.', NULL, '', '', 1, 0, '2025-07-20 14:20:11', '2025-07-20 14:20:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_ref` (`booking_ref`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_booking_ref` (`booking_ref`);

--
-- Indexes for table `booking_history`
--
ALTER TABLE `booking_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_ref` (`booking_ref`),
  ADD KEY `idx_booking_ref` (`booking_ref`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_recipient_email` (`recipient_email`),
  ADD KEY `idx_email_type` (`email_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `website_content`
--
ALTER TABLE `website_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section` (`section`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `booking_history`
--
ALTER TABLE `booking_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `email_notifications`
--
ALTER TABLE `email_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `website_content`
--
ALTER TABLE `website_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD CONSTRAINT `email_notifications_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
