<?php
/**
 * Simple Mail System
 * 
 * This provides a simple email system that works without complex SMTP setup
 */

// Prevent multiple inclusions
if (defined('SIMPLE_MAIL_LOADED')) {
    return;
}
define('SIMPLE_MAIL_LOADED', true);

/**
 * Send email using simple PHP mail with fallback options
 */
function sendSimpleEmail($to, $subject, $message, $from_name = 'Byiringiro Valentin MC Services') {
    $result = [
        'success' => false,
        'message' => '',
        'method' => ''
    ];
    
    // Try different email methods
    
    // Method 1: Try basic PHP mail
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <noreply@localhost>',
        'Reply-To: izabayojeanlucseverin@gmail.com'
    ];
    
    $email_sent = @mail($to, $subject, $message, implode("\r\n", $headers));
    
    if ($email_sent) {
        $result['success'] = true;
        $result['message'] = "Email sent successfully using PHP mail";
        $result['method'] = 'php_mail';
        logEmailAttempt($to, $subject, 'SUCCESS', 'PHP Mail');
        return $result;
    }
    
    // Method 2: Log email for manual sending
    logEmailForManualSending($to, $subject, $message);
    
    $result['success'] = true; // Consider it successful since we logged it
    $result['message'] = "Email logged for manual sending (mail server not configured)";
    $result['method'] = 'manual_log';
    
    return $result;
}

/**
 * Send booking status notification to client
 */
function sendBookingStatusNotification($booking_data, $status, $admin_message = '') {
    $client_email = $booking_data['email'];
    $client_name = $booking_data['name'];
    $booking_ref = $booking_data['booking_ref'];
    
    // Create status-specific subject and content
    switch ($status) {
        case 'confirmed':
            $subject = "✅ Booking Confirmed - {$booking_ref}";
            $status_message = "Great news! Your booking has been confirmed.";
            $status_color = "#28a745";
            break;
        case 'cancelled':
        case 'rejected':
            $subject = "❌ Booking Update - {$booking_ref}";
            $status_message = "We regret to inform you that your booking could not be confirmed.";
            $status_color = "#dc3545";
            break;
        default:
            $subject = "📋 Booking Status Update - {$booking_ref}";
            $status_message = "Your booking status has been updated.";
            $status_color = "#ffc107";
    }
    
    $email_body = createStatusNotificationEmail($client_name, $booking_ref, $status, $status_message, $status_color, $admin_message);
    
    return sendSimpleEmail($client_email, $subject, $email_body);
}

/**
 * Send initial booking confirmation to client
 */
function sendBookingConfirmation($booking_data) {
    $client_email = $booking_data['email'];
    $client_name = $booking_data['name'];
    $booking_ref = $booking_data['booking_ref'];
    
    $subject = "📋 Booking Received - {$booking_ref}";
    $email_body = createBookingConfirmationEmail($booking_data);
    
    return sendSimpleEmail($client_email, $subject, $email_body);
}

/**
 * Create status notification email template
 */
function createStatusNotificationEmail($client_name, $booking_ref, $status, $status_message, $status_color, $admin_message) {
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
                <p>Reference: {$booking_ref}</p>
            </div>
            
            <div class='content'>
                <p>Dear {$client_name},</p>
                
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
                <p>If you have any questions, please contact us:</p>
                <ul>
                    <li><strong>📧 Email:</strong> izabayojeanlucseverin@gmail.com</li>
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
 * Create booking confirmation email template
 */
function createBookingConfirmationEmail($booking_data) {
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
                <h1>📋 Booking Received</h1>
                <p>Reference: {$booking_data['booking_ref']}</p>
            </div>
            
            <div class='content'>
                <p>Dear {$booking_data['name']},</p>
                
                <p>Thank you for your booking request. We have received your information and will review it shortly.</p>
                
                <div class='status-pending'>
                    <h3>📋 Current Status: PENDING</h3>
                    <p>Your booking is currently under review. We will contact you within 24 hours to confirm availability and discuss details.</p>
                </div>
                
                <h3>Your Booking Details</h3>
                <div class='info-box'>
                    <p><strong>Event Type:</strong> {$booking_data['event_type']}</p>
                    <p><strong>Date:</strong> {$formatted_date}</p>
                    <p><strong>Time:</strong> {$formatted_time}</p>
                    <p><strong>Location:</strong> {$booking_data['event_location']}</p>
                    <p><strong>Expected Guests:</strong> {$booking_data['guests']}</p>
                </div>
                
                <h3>What Happens Next?</h3>
                <ol>
                    <li><strong>Review:</strong> We will review your booking request</li>
                    <li><strong>Contact:</strong> We'll contact you to confirm availability</li>
                    <li><strong>Confirmation:</strong> You'll receive a status update email</li>
                    <li><strong>Planning:</strong> We'll work together to plan your event</li>
                </ol>
                
                <h3>Contact Information</h3>
                <p>If you have any questions:</p>
                <ul>
                    <li><strong>📧 Email:</strong> izabayojeanlucseverin@gmail.com</li>
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
 * Log email attempt for debugging
 */
function logEmailAttempt($to, $subject, $status, $method) {
    $log_entry = date('Y-m-d H:i:s') . " - Email to: {$to}, Subject: {$subject}, Status: {$status}, Method: {$method}\n";
    error_log($log_entry, 3, __DIR__ . '/email_log.txt');
}

/**
 * Log email for manual sending when mail server is not available
 */
function logEmailForManualSending($to, $subject, $message) {
    $log_entry = "
=== EMAIL FOR MANUAL SENDING ===
Date: " . date('Y-m-d H:i:s') . "
To: {$to}
Subject: {$subject}
Message: " . strip_tags($message) . "
=====================================

";
    
    file_put_contents(__DIR__ . '/manual_emails.txt', $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Get manual emails that need to be sent
 */
function getManualEmails() {
    $file_path = __DIR__ . '/manual_emails.txt';
    if (file_exists($file_path)) {
        return file_get_contents($file_path);
    }
    return '';
}

/**
 * Clear manual emails log
 */
function clearManualEmails() {
    $file_path = __DIR__ . '/manual_emails.txt';
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}
?>
