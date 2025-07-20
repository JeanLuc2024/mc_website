<?php
/**
 * Real SMTP Email System
 * 
 * Proper SMTP implementation that actually connects to Gmail servers
 */

require_once 'config.php';

/**
 * Send email using REAL SMTP connection to Gmail
 */
function sendRealSMTPEmail($to, $subject, $message, $from_name = null, $reply_to = null) {
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
        return false;
    }
    
    try {
        // Create socket connection to Gmail SMTP
        $socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 30);
        if (!$socket) {
            error_log("SMTP Connection Error: {$errstr} ({$errno})");
            return false;
        }
        
        // Read initial response
        $response = fgets($socket);
        if (strpos($response, '220') !== 0) {
            fclose($socket);
            error_log("SMTP Initial Response Error: {$response}");
            return false;
        }
        
        // Send EHLO
        fputs($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
        $response = readSMTPResponse($socket);
        
        // Start TLS encryption
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket);
        if (strpos($response, '220') !== 0) {
            fclose($socket);
            error_log("SMTP STARTTLS Error: {$response}");
            return false;
        }
        
        // Enable TLS encryption
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            error_log("SMTP TLS Encryption Error");
            return false;
        }
        
        // Send EHLO again after TLS
        fputs($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
        $response = readSMTPResponse($socket);
        
        // Authenticate with Gmail
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket);
        if (strpos($response, '334') !== 0) {
            fclose($socket);
            error_log("SMTP AUTH LOGIN Error: {$response}");
            return false;
        }
        
        // Send username (base64 encoded)
        fputs($socket, base64_encode($smtp_username) . "\r\n");
        $response = fgets($socket);
        if (strpos($response, '334') !== 0) {
            fclose($socket);
            error_log("SMTP Username Error: {$response}");
            return false;
        }
        
        // Send password (base64 encoded)
        fputs($socket, base64_encode($smtp_password) . "\r\n");
        $response = fgets($socket);
        if (strpos($response, '235') !== 0) {
            fclose($socket);
            error_log("SMTP Authentication Error: {$response}");
            return false;
        }
        
        // Send MAIL FROM
        fputs($socket, "MAIL FROM: <{$smtp_from_email}>\r\n");
        $response = fgets($socket);
        if (strpos($response, '250') !== 0) {
            fclose($socket);
            error_log("SMTP MAIL FROM Error: {$response}");
            return false;
        }
        
        // Send RCPT TO
        fputs($socket, "RCPT TO: <{$to}>\r\n");
        $response = fgets($socket);
        if (strpos($response, '250') !== 0) {
            fclose($socket);
            error_log("SMTP RCPT TO Error: {$response}");
            return false;
        }
        
        // Send DATA command
        fputs($socket, "DATA\r\n");
        $response = fgets($socket);
        if (strpos($response, '354') !== 0) {
            fclose($socket);
            error_log("SMTP DATA Error: {$response}");
            return false;
        }
        
        // Prepare email headers and body
        $email_data = "From: {$smtp_from_name} <{$smtp_from_email}>\r\n";
        $email_data .= "To: {$to}\r\n";
        $email_data .= "Reply-To: {$reply_to_email}\r\n";
        $email_data .= "Subject: {$subject}\r\n";
        $email_data .= "MIME-Version: 1.0\r\n";
        $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_data .= "X-Mailer: MC Booking System\r\n";
        $email_data .= "X-Priority: 3\r\n";
        $email_data .= "\r\n";
        $email_data .= $message;
        $email_data .= "\r\n.\r\n";
        
        // Send email data
        fputs($socket, $email_data);
        $response = fgets($socket);
        if (strpos($response, '250') !== 0) {
            fclose($socket);
            error_log("SMTP Send Error: {$response}");
            return false;
        }
        
        // Send QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        // Log successful email
        $log_entry = date('Y-m-d H:i:s') . " - REAL SMTP SUCCESS: Email sent to {$to}, Subject: {$subject}\n";
        error_log($log_entry, 3, __DIR__ . '/real_smtp_log.txt');
        
        return true;
        
    } catch (Exception $e) {
        error_log("SMTP Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Read SMTP response (handle multi-line responses)
 */
function readSMTPResponse($socket) {
    $response = '';
    while (true) {
        $line = fgets($socket);
        $response .= $line;
        if (substr($line, 3, 1) === ' ') {
            break;
        }
    }
    return $response;
}

/**
 * Send booking confirmation email using real SMTP
 */
function sendRealBookingConfirmationSMTP($booking_data) {
    $subject = "📋 Booking Confirmation - " . $booking_data['booking_ref'];
    $message = createBookingConfirmationTemplate($booking_data);
    
    return sendRealSMTPEmail(
        $booking_data['email'],
        $subject,
        $message,
        'Byiringiro Valentin MC Services',
        ADMIN_EMAIL
    );
}

/**
 * Send admin notification email using real SMTP
 */
function sendRealAdminNotificationSMTP($booking_data) {
    $subject = "🎉 New Booking Received - " . $booking_data['booking_ref'];
    $message = createAdminNotificationTemplate($booking_data);
    
    return sendRealSMTPEmail(
        ADMIN_EMAIL,
        $subject,
        $message,
        'MC Booking System',
        $booking_data['email']
    );
}

/**
 * Send status update email using real SMTP
 */
function sendRealStatusUpdateSMTP($booking_data, $new_status, $admin_message = '') {
    $status_messages = [
        'confirmed' => ['✅ Booking Confirmed', 'Great news! Your booking has been confirmed.', '#28a745'],
        'cancelled' => ['❌ Booking Cancelled', 'We regret to inform you that your booking has been cancelled.', '#dc3545'],
        'completed' => ['🎉 Event Completed', 'Thank you for choosing our services! Your event has been completed.', '#17a2b8']
    ];
    
    $status_info = $status_messages[$new_status] ?? ['📋 Booking Update', 'Your booking status has been updated.', '#ffc107'];
    
    $subject = $status_info[0] . " - " . $booking_data['booking_ref'];
    $message = createStatusUpdateTemplate($booking_data, $new_status, $status_info[1], $status_info[2], $admin_message);
    
    return sendRealSMTPEmail(
        $booking_data['email'],
        $subject,
        $message,
        'Byiringiro Valentin MC Services',
        ADMIN_EMAIL
    );
}

/**
 * Test real SMTP configuration
 */
function testRealSMTPConfiguration($test_email) {
    $test_subject = '🔧 REAL SMTP TEST - ' . date('Y-m-d H:i:s');
    $test_message = createRealTestEmailTemplate();
    
    return sendRealSMTPEmail($test_email, $test_subject, $test_message);
}

/**
 * Create booking confirmation email template
 */
function createBookingConfirmationTemplate($booking_data) {
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
 * Create real test email template
 */
function createRealTestEmailTemplate() {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #28a745; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .success-box { background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 REAL SMTP SUCCESS!</h1>
            </div>
            
            <div class='content'>
                <div class='success-box'>
                    <h3>✅ Real SMTP Email Working!</h3>
                    <p>This email was sent using REAL SMTP authentication with Gmail servers!</p>
                </div>
                
                <p><strong>Configuration Details:</strong></p>
                <ul>
                    <li><strong>SMTP Host:</strong> " . SMTP_HOST . "</li>
                    <li><strong>SMTP Port:</strong> " . SMTP_PORT . "</li>
                    <li><strong>Authentication:</strong> Gmail App Password</li>
                    <li><strong>Encryption:</strong> TLS</li>
                    <li><strong>From Email:</strong> " . SMTP_FROM_EMAIL . "</li>
                    <li><strong>Admin Email:</strong> " . ADMIN_EMAIL . "</li>
                </ul>
                
                <p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                
                <p>Your MC booking system email notifications are now working with REAL SMTP!</p>
            </div>
            
            <div class='footer'>
                <p>MC Booking System - Real SMTP Test</p>
            </div>
        </div>
    </body>
    </html>";
}
?>
