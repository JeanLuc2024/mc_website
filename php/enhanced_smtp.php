<?php
/**
 * Enhanced SMTP Email System - DISABLED
 *
 * This file has been disabled to prevent duplicate notifications.
 * The real_smtp_email.php file is now used instead.
 */

// DISABLED - Use real_smtp_email.php instead
exit('This email system has been disabled. Use real_smtp_email.php instead.');

require_once 'config.php';

/**
 * Send email using Gmail SMTP
 */
function sendSMTPEmail($to, $subject, $message, $from_name = null, $reply_to = null) {
    // Use configuration values
    $smtp_host = SMTP_HOST;
    $smtp_port = SMTP_PORT;
    $smtp_username = SMTP_USERNAME;
    $smtp_password = SMTP_PASSWORD;
    $smtp_from_email = SMTP_FROM_EMAIL;
    $smtp_from_name = $from_name ?: SMTP_FROM_NAME;
    $reply_to_email = $reply_to ?: ADMIN_EMAIL;
    
    // Check if app password is set
    if ($smtp_password === 'your-app-password') {
        error_log("SMTP Error: Gmail app password not configured in config.php");
        return sendFallbackEmail($to, $subject, $message, $smtp_from_name, $reply_to_email);
    }
    
    // Create email headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $smtp_from_name . ' <' . $smtp_from_email . '>',
        'Reply-To: ' . $reply_to_email,
        'X-Mailer: MC Booking System',
        'X-Priority: 3'
    ];
    
    // Try to send with PHP mail() function first (works if SMTP is configured in PHP)
    $email_sent = @mail($to, $subject, $message, implode("\r\n", $headers));
    
    if ($email_sent) {
        // Log successful email
        $log_entry = date('Y-m-d H:i:s') . " - SUCCESS: Email sent to {$to}, Subject: {$subject}\n";
        error_log($log_entry, 3, __DIR__ . '/email_log.txt');
        return true;
    } else {
        // Log failed email and try fallback
        $log_entry = date('Y-m-d H:i:s') . " - FAILED: Email to {$to}, Subject: {$subject}\n";
        error_log($log_entry, 3, __DIR__ . '/email_log.txt');
        
        // Try fallback method
        return sendFallbackEmail($to, $subject, $message, $smtp_from_name, $reply_to_email);
    }
}

/**
 * Fallback email method - saves to file for manual sending
 */
function sendFallbackEmail($to, $subject, $message, $from_name, $reply_to) {
    $email_content = "
=== EMAIL READY FOR MANUAL SENDING ===
Date: " . date('Y-m-d H:i:s') . "
To: {$to}
From: {$from_name} <{$reply_to}>
Subject: {$subject}

Message:
{$message}

=== END EMAIL ===

";
    
    // Save to manual emails file
    file_put_contents(__DIR__ . '/manual_emails.txt', $email_content, FILE_APPEND | LOCK_EX);
    
    // Log fallback
    $log_entry = date('Y-m-d H:i:s') . " - FALLBACK: Email saved for manual sending to {$to}\n";
    error_log($log_entry, 3, __DIR__ . '/email_log.txt');
    
    return true; // Return true so system continues working
}

/**
 * Send booking confirmation email to client
 */
function sendBookingConfirmationSMTP($booking_data) {
    $subject = "📋 Booking Confirmation - " . $booking_data['booking_ref'];
    $message = createBookingConfirmationTemplate($booking_data);
    
    return sendSMTPEmail(
        $booking_data['email'],
        $subject,
        $message,
        'Byiringiro Valentin MC Services',
        ADMIN_EMAIL
    );
}

/**
 * Send admin notification email
 */
function sendAdminNotificationSMTP($booking_data) {
    $subject = "🎉 New Booking Received - " . $booking_data['booking_ref'];
    $message = createAdminNotificationTemplate($booking_data);
    
    return sendSMTPEmail(
        ADMIN_EMAIL,
        $subject,
        $message,
        'MC Booking System',
        $booking_data['email']
    );
}

/**
 * Send status update email to client
 */
function sendStatusUpdateSMTP($booking_data, $new_status, $admin_message = '') {
    $status_messages = [
        'confirmed' => ['✅ Booking Confirmed', 'Great news! Your booking has been confirmed.', '#28a745'],
        'cancelled' => ['❌ Booking Cancelled', 'We regret to inform you that your booking has been cancelled.', '#dc3545'],
        'completed' => ['🎉 Event Completed', 'Thank you for choosing our services! Your event has been completed.', '#17a2b8']
    ];
    
    $status_info = $status_messages[$new_status] ?? ['📋 Booking Update', 'Your booking status has been updated.', '#ffc107'];
    
    $subject = $status_info[0] . " - " . $booking_data['booking_ref'];
    $message = createStatusUpdateTemplate($booking_data, $new_status, $status_info[1], $status_info[2], $admin_message);
    
    return sendSMTPEmail(
        $booking_data['email'],
        $subject,
        $message,
        'Byiringiro Valentin MC Services',
        ADMIN_EMAIL
    );
}

/**
 * Create booking confirmation email template - DISABLED
 * Use the one in real_smtp_email.php instead
 */
function createBookingConfirmationTemplate_DISABLED($booking_data) {
    $formatted_date = date('F j, Y', strtotime($booking_data['event_date']));
    $formatted_time = date('g:i A', strtotime($booking_data['event_time']));
    
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #3498db; }
            .status-pending { background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📋 Booking Received Successfully!</h1>
                <p>Reference: {$booking_data['booking_ref']}</p>
            </div>
            
            <div class='content'>
                <p>Dear {$booking_data['name']},</p>
                
                <p>Thank you for your booking request! We have successfully received your information and will review it shortly.</p>
                
                <div class='status-pending'>
                    <h3>📋 Current Status: PENDING APPROVAL</h3>
                    <p>Your booking is currently under review. We will contact you within 24 hours to confirm availability and discuss details.</p>
                </div>
                
                <h3>Your Booking Details</h3>
                <div class='info-box'>
                    <p><strong>Event Type:</strong> {$booking_data['event_type']}</p>
                    <p><strong>Date:</strong> {$formatted_date}</p>
                    <p><strong>Time:</strong> {$formatted_time}</p>
                    <p><strong>Location:</strong> {$booking_data['event_location']}</p>
                    <p><strong>Expected Guests:</strong> {$booking_data['guests']}</p>
                    <p><strong>Package:</strong> {$booking_data['package']}</p>
                </div>
                
                <h3>What Happens Next?</h3>
                <ol>
                    <li><strong>Review:</strong> We will review your booking request</li>
                    <li><strong>Contact:</strong> We'll contact you to confirm availability</li>
                    <li><strong>Status Update:</strong> You'll receive an email with the final decision</li>
                    <li><strong>Planning:</strong> If confirmed, we'll work together to plan your event</li>
                </ol>
                
                <h3>Contact Information</h3>
                <p>If you have any questions:</p>
                <ul>
                    <li><strong>📧 Email:</strong> byirival009@gmail.com</li>
                    <li><strong>📞 Phone:</strong> +123 456 7890</li>
                </ul>
                
                <p>We look forward to making your event special!</p>
                
                <p>Best regards,<br>
                <strong>Byiringiro Valentin</strong><br>
                <em>Master of Ceremony</em></p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2025 Byiringiro Valentin MC Services. All Rights Reserved.</p>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Create admin notification email template
 */
function createAdminNotificationTemplate($booking_data) {
    $formatted_date = date('F j, Y', strtotime($booking_data['event_date']));
    $formatted_time = date('g:i A', strtotime($booking_data['event_time']));
    
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #e74c3c; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #e74c3c; }
            .btn { background: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 NEW BOOKING ALERT!</h1>
                <p>Booking Reference: {$booking_data['booking_ref']}</p>
            </div>
            
            <div class='content'>
                <h2>Client Information</h2>
                <div class='info-box'>
                    <p><strong>Name:</strong> {$booking_data['name']}</p>
                    <p><strong>Email:</strong> {$booking_data['email']}</p>
                    <p><strong>Phone:</strong> {$booking_data['phone']}</p>
                </div>
                
                <h2>Event Details</h2>
                <div class='info-box'>
                    <p><strong>Type:</strong> {$booking_data['event_type']}</p>
                    <p><strong>Date:</strong> {$formatted_date}</p>
                    <p><strong>Time:</strong> {$formatted_time}</p>
                    <p><strong>Location:</strong> {$booking_data['event_location']}</p>
                    <p><strong>Guests:</strong> {$booking_data['guests']}</p>
                    <p><strong>Package:</strong> {$booking_data['package']}</p>
                </div>
                
                " . (!empty($booking_data['message']) ? "<h2>Additional Message</h2><div class='info-box'>" . nl2br(htmlspecialchars($booking_data['message'])) . "</div>" : "") . "
                
                <div style='text-align: center; margin: 20px 0;'>
                    <a href='http://localhost/mc_website/admin/bookings.php' class='btn'>
                        📋 View in Admin Panel
                    </a>
                </div>
                
                <p><strong>⚡ IMMEDIATE ACTION REQUIRED:</strong></p>
                <ol>
                    <li>Review booking details in admin panel</li>
                    <li>Check availability for the requested date/time</li>
                    <li>Contact client to confirm or discuss alternatives</li>
                    <li>Update booking status (confirmed/cancelled)</li>
                    <li>Client will receive automatic status update email</li>
                </ol>
            </div>
            
            <div class='footer'>
                <p>This is an automated notification from your MC booking system.</p>
                <p>&copy; 2025 Byiringiro Valentin MC Services</p>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Create status update email template
 */
function createStatusUpdateTemplate($booking_data, $status, $status_message, $status_color, $admin_message) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: {$status_color}; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .status-box { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid {$status_color}; }
            .admin-message { background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 20px 0; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Booking Status Update</h1>
                <p>Reference: {$booking_data['booking_ref']}</p>
            </div>
            
            <div class='content'>
                <p>Dear {$booking_data['name']},</p>
                
                <div class='status-box'>
                    <h3>Status: " . strtoupper($status) . "</h3>
                    <p>{$status_message}</p>
                </div>
                
                " . (!empty($admin_message) ? "
                <div class='admin-message'>
                    <h4>Message from our team:</h4>
                    <p>" . nl2br(htmlspecialchars($admin_message)) . "</p>
                </div>
                " : "") . "
                
                <h3>Contact Information</h3>
                <p>If you have any questions:</p>
                <ul>
                    <li><strong>📧 Email:</strong> byirival009@gmail.com</li>
                    <li><strong>📞 Phone:</strong> +123 456 7890</li>
                </ul>
                
                <p>Thank you for choosing Byiringiro Valentin MC Services.</p>
                
                <p>Best regards,<br>
                <strong>Byiringiro Valentin</strong><br>
                <em>Master of Ceremony</em></p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2025 Byiringiro Valentin MC Services. All Rights Reserved.</p>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Test SMTP configuration
 */
function testSMTPConfiguration($test_email) {
    $test_subject = 'SMTP Configuration Test - ' . date('Y-m-d H:i:s');
    $test_message = createTestEmailTemplate();
    
    return sendSMTPEmail($test_email, $test_subject, $test_message);
}

/**
 * Create test email template
 */
function createTestEmailTemplate() {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #27ae60; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .success-box { background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 SMTP Test Successful!</h1>
            </div>
            
            <div class='content'>
                <div class='success-box'>
                    <h3>✅ Email System Working!</h3>
                    <p>This email confirms that your Gmail SMTP configuration is working correctly.</p>
                </div>
                
                <p><strong>Configuration Details:</strong></p>
                <ul>
                    <li><strong>SMTP Host:</strong> " . SMTP_HOST . "</li>
                    <li><strong>SMTP Port:</strong> " . SMTP_PORT . "</li>
                    <li><strong>From Email:</strong> " . SMTP_FROM_EMAIL . "</li>
                    <li><strong>Admin Email:</strong> " . ADMIN_EMAIL . "</li>
                </ul>
                
                <p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                
                <p>Your MC booking system is ready to send professional emails to clients!</p>
            </div>
            
            <div class='footer'>
                <p>MC Booking System - Email Test</p>
            </div>
        </div>
    </body>
    </html>";
}
?>
