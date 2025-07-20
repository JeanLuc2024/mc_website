<?php
/**
 * Setup Script for Email Communication System
 * 
 * This script creates the necessary database tables for the email communication system.
 */

require_once 'config.php';

// Get database connection
$conn = connectDB();

if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

echo "<h2>Setting up Email Communication System...</h2>";

try {
    // Create email communications table
    echo "<p>Creating email communications table...</p>";
    $email_comm_sql = "
    CREATE TABLE IF NOT EXISTS email_communications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        from_email VARCHAR(255) NOT NULL,
        to_email VARCHAR(255) NOT NULL,
        subject VARCHAR(500) NOT NULL,
        message TEXT NOT NULL,
        email_type ENUM('booking_confirmation', 'booking_update', 'custom_reply', 'follow_up', 'cancellation') DEFAULT 'custom_reply',
        sent_by_admin_id INT,
        sent_at DATETIME NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        read_at DATETIME NULL,
        reply_to_email_id INT NULL,
        attachments JSON NULL,
        INDEX idx_booking_id (booking_id),
        INDEX idx_sent_at (sent_at),
        INDEX idx_email_type (email_type),
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    )";
    $conn->exec($email_comm_sql);
    echo "<p style='color: green;'>✓ Email communications table created successfully.</p>";

    // Create email templates table
    echo "<p>Creating email templates table...</p>";
    $templates_sql = "
    CREATE TABLE IF NOT EXISTS email_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        template_name VARCHAR(100) NOT NULL,
        template_category ENUM('confirmation', 'follow_up', 'cancellation', 'custom', 'pricing', 'availability') DEFAULT 'custom',
        subject_template VARCHAR(500) NOT NULL,
        message_template TEXT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_by_admin_id INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category (template_category),
        INDEX idx_active (is_active)
    )";
    $conn->exec($templates_sql);
    echo "<p style='color: green;'>✓ Email templates table created successfully.</p>";

    // Insert default email templates
    echo "<p>Inserting default email templates...</p>";
    $default_templates = [
        [
            'Booking Confirmation',
            'confirmation',
            'Your Booking is Confirmed - {booking_ref}',
            'Dear {client_name},

I am pleased to confirm your booking for {event_type} on {event_date} at {event_time}.

Booking Details:
- Reference: {booking_ref}
- Event Type: {event_type}
- Date: {event_date}
- Time: {event_time}
- Location: {event_location}
- Number of Guests: {guests}

I look forward to making your event memorable and special. If you have any questions or need to discuss any specific requirements, please don\'t hesitate to contact me.

Best regards,
Byiringiro Valentin
Master of Ceremony
Phone: +123 456 7890
Email: valentin@mcservices.com'
        ],
        [
            'Booking Follow-up',
            'follow_up',
            'Following up on your upcoming event - {booking_ref}',
            'Dear {client_name},

I hope this email finds you well. I wanted to follow up regarding your upcoming {event_type} scheduled for {event_date}.

As we approach your special day, I wanted to ensure everything is perfectly planned. Please let me know if:
- There are any changes to the event details
- You have specific requirements or requests
- You need assistance with event planning
- You have any questions about my services

I am here to ensure your event runs smoothly and creates lasting memories for you and your guests.

Looking forward to hearing from you.

Best regards,
Byiringiro Valentin
Master of Ceremony'
        ],
        [
            'Pricing Information',
            'pricing',
            'Pricing Information for MC Services',
            'Dear {client_name},

Thank you for your interest in my Master of Ceremony services. I am excited about the possibility of being part of your special event.

Here are my service packages:

**Wedding Ceremonies** - $500
- Complete ceremony hosting
- Coordination with vendors
- Timeline management
- Microphone and sound coordination

**Anniversary Celebrations** - $400
- Event hosting and coordination
- Speech coordination
- Timeline management
- Guest engagement

**Corporate Meetings** - $300
- Professional meeting facilitation
- Presentation coordination
- Time management
- Q&A session management

All packages include:
- Pre-event consultation
- Event day coordination
- Professional attire
- Backup equipment

I would be happy to discuss your specific needs and customize a package that fits your event perfectly.

Please let me know if you have any questions or would like to schedule a consultation.

Best regards,
Byiringiro Valentin
Master of Ceremony'
        ],
        [
            'Availability Check',
            'availability',
            'Checking Availability for Your Event',
            'Dear {client_name},

Thank you for reaching out about MC services for your {event_type}.

I am currently checking my availability for {event_date}. I will get back to you within 24 hours with confirmation and detailed information about my services.

In the meantime, please feel free to share any specific requirements or questions you might have about your event.

I look forward to the possibility of making your event memorable and special.

Best regards,
Byiringiro Valentin
Master of Ceremony
Phone: +123 456 7890
Email: valentin@mcservices.com'
        ],
        [
            'Event Cancellation',
            'cancellation',
            'Regarding Your Event Cancellation - {booking_ref}',
            'Dear {client_name},

I have received your request to cancel the booking for {event_type} scheduled on {event_date}.

I understand that circumstances can change, and I want to make this process as smooth as possible for you.

Your booking {booking_ref} has been cancelled as requested. If there are any refund policies that apply, I will process them according to our agreed terms.

If you need to reschedule for a future date, please don\'t hesitate to reach out. I would be happy to accommodate your new plans.

Thank you for considering my services, and I hope we can work together in the future.

Best regards,
Byiringiro Valentin
Master of Ceremony'
        ],
        [
            'Custom Thank You',
            'custom',
            'Thank You for Choosing My Services',
            'Dear {client_name},

I wanted to take a moment to personally thank you for choosing me as your Master of Ceremony for your {event_type}.

It was truly an honor to be part of your special day, and I hope the event exceeded your expectations. Seeing the joy and happiness of you and your guests made the experience incredibly rewarding for me as well.

If you have any feedback about my services or if there\'s anything I could improve for future events, I would greatly appreciate hearing from you.

Should you need MC services for future events or know someone who does, please don\'t hesitate to reach out. I would be delighted to help make more special moments memorable.

Thank you once again for trusting me with your important day.

Warm regards,
Byiringiro Valentin
Master of Ceremony
Phone: +123 456 7890
Email: valentin@mcservices.com'
        ]
    ];

    $template_insert_sql = "INSERT INTO email_templates (template_name, template_category, subject_template, message_template, is_active) VALUES (?, ?, ?, ?, TRUE) ON DUPLICATE KEY UPDATE message_template = VALUES(message_template)";
    $template_stmt = $conn->prepare($template_insert_sql);

    foreach ($default_templates as $template) {
        $template_stmt->execute($template);
    }
    echo "<p style='color: green;'>✓ Default email templates inserted successfully.</p>";

    // Create email attachments table
    echo "<p>Creating email attachments table...</p>";
    $attachments_sql = "
    CREATE TABLE IF NOT EXISTS email_attachments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email_communication_id INT NOT NULL,
        original_filename VARCHAR(255) NOT NULL,
        stored_filename VARCHAR(255) NOT NULL,
        file_size INT NOT NULL,
        mime_type VARCHAR(100) NOT NULL,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (email_communication_id) REFERENCES email_communications(id) ON DELETE CASCADE
    )";
    $conn->exec($attachments_sql);
    echo "<p style='color: green;'>✓ Email attachments table created successfully.</p>";

    // Add email tracking columns to bookings table
    echo "<p>Adding email tracking columns to bookings table...</p>";
    try {
        $conn->exec("ALTER TABLE bookings ADD COLUMN last_email_sent DATETIME NULL");
        echo "<p style='color: green;'>✓ Added last_email_sent column.</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ last_email_sent column may already exist.</p>";
    }

    try {
        $conn->exec("ALTER TABLE bookings ADD COLUMN email_count INT DEFAULT 0");
        echo "<p style='color: green;'>✓ Added email_count column.</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ email_count column may already exist.</p>";
    }

    try {
        $conn->exec("ALTER TABLE bookings ADD COLUMN last_email_type VARCHAR(50) NULL");
        echo "<p style='color: green;'>✓ Added last_email_type column.</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ last_email_type column may already exist.</p>";
    }

    echo "<h3 style='color: green;'>🎉 Email Communication System Setup Complete!</h3>";
    echo "<h4>New Features Available:</h4>";
    echo "<ul>";
    echo "<li>📧 Direct email communication with clients</li>";
    echo "<li>📝 Professional email templates</li>";
    echo "<li>📊 Email history tracking</li>";
    echo "<li>🔄 Email personalization with booking variables</li>";
    echo "<li>📱 Mobile-responsive email composer</li>";
    echo "</ul>";
    
    echo "<h4>How to Use:</h4>";
    echo "<ol>";
    echo "<li>Go to <strong>Bookings</strong> in your admin panel</li>";
    echo "<li>Click the <strong>📧 Email</strong> button next to any booking</li>";
    echo "<li>Choose from pre-made templates or write custom emails</li>";
    echo "<li>Use variables like {client_name}, {booking_ref}, etc.</li>";
    echo "<li>Send professional emails directly to clients</li>";
    echo "</ol>";
    
    echo "<p><a href='../admin/bookings.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Bookings</a></p>";

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
    <title>Email Communication Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h2 { color: #2c3e50; }
        h3 { color: #27ae60; }
        p { line-height: 1.6; }
        ul, ol { line-height: 1.8; }
    </style>
</head>
<body>
</body>
</html>
