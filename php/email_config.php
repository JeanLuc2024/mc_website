<?php
/**
 * Email Configuration for Gmail SMTP
 *
 * This file configures email sending using Gmail SMTP instead of local mail server
 */

// Prevent multiple inclusions
if (defined('EMAIL_CONFIG_LOADED')) {
    return;
}
define('EMAIL_CONFIG_LOADED', true);

// Email configuration - only define if not already defined
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'smtp.gmail.com');
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', 587);
}
if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', 'izabayojeanlucseverin@gmail.com');
}
if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', ''); // You need to set your Gmail App Password here
}
if (!defined('SMTP_ENCRYPTION')) {
    define('SMTP_ENCRYPTION', 'tls');
}

/**
 * Send email using Gmail SMTP
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @param string $from_name Sender name
 * @return bool Success status
 */
function sendEmailSMTP($to, $subject, $message, $from_name = 'Byiringiro Valentin MC Services') {
    // If SMTP password is not set, fall back to basic mail
    if (empty(SMTP_PASSWORD)) {
        return sendEmailBasic($to, $subject, $message, $from_name);
    }
    
    // Create email headers for SMTP
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . SMTP_USERNAME . '>',
        'Reply-To: ' . SMTP_USERNAME,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // For now, we'll use the basic mail function
    // In production, you would use PHPMailer or similar library
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

/**
 * Send email using basic PHP mail function
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @param string $from_name Sender name
 * @return bool Success status
 */
function sendEmailBasic($to, $subject, $message, $from_name = 'Byiringiro Valentin MC Services') {
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <noreply@valentinmc.com>',
        'Reply-To: izabayojeanlucseverin@gmail.com'
    ];
    
    return @mail($to, $subject, $message, implode("\r\n", $headers));
}

/**
 * Send booking notification emails
 * 
 * @param array $booking_data Booking information
 * @return array Results of email sending
 */
function sendBookingEmails($booking_data) {
    $results = [
        'admin_sent' => false,
        'client_sent' => false,
        'errors' => []
    ];
    
    $admin_email = 'izabayojeanlucseverin@gmail.com';
    $formatted_date = date('F j, Y', strtotime($booking_data['event_date']));
    $formatted_time = date('g:i A', strtotime($booking_data['event_time']));
    
    // Admin notification email
    $admin_subject = "🎉 New Booking Received - {$booking_data['booking_ref']}";
    $admin_body = createAdminEmailTemplate($booking_data, $formatted_date, $formatted_time);
    
    // Client confirmation email
    $client_subject = "✅ Booking Confirmation - {$booking_data['booking_ref']}";
    $client_body = createClientEmailTemplate($booking_data, $formatted_date, $formatted_time);
    
    // Send admin email
    try {
        $results['admin_sent'] = sendEmailSMTP($admin_email, $admin_subject, $admin_body);
        if (!$results['admin_sent']) {
            $results['errors'][] = "Failed to send admin notification";
        }
    } catch (Exception $e) {
        $results['errors'][] = "Admin email error: " . $e->getMessage();
    }
    
    // Send client email
    try {
        $results['client_sent'] = sendEmailSMTP($booking_data['email'], $client_subject, $client_body);
        if (!$results['client_sent']) {
            $results['errors'][] = "Failed to send client confirmation";
        }
    } catch (Exception $e) {
        $results['errors'][] = "Client email error: " . $e->getMessage();
    }
    
    // Log results
    error_log("Email results - Admin: " . ($results['admin_sent'] ? 'SUCCESS' : 'FAILED') . 
              ", Client: " . ($results['client_sent'] ? 'SUCCESS' : 'FAILED'));
    
    if (!empty($results['errors'])) {
        error_log("Email errors: " . implode(', ', $results['errors']));
    }
    
    return $results;
}

/**
 * Create admin email template
 */
function createAdminEmailTemplate($booking_data, $formatted_date, $formatted_time) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #3498db; }
            .label { font-weight: bold; color: #2c3e50; }
            .btn { background: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 New Booking Received!</h1>
                <p>Booking Reference: {$booking_data['booking_ref']}</p>
            </div>
            <div class='content'>
                <h2>Client Information</h2>
                <div class='info-box'>
                    <p><span class='label'>Name:</span> {$booking_data['name']}</p>
                    <p><span class='label'>Email:</span> {$booking_data['email']}</p>
                    <p><span class='label'>Phone:</span> {$booking_data['phone']}</p>
                </div>
                
                <h2>Event Details</h2>
                <div class='info-box'>
                    <p><span class='label'>Type:</span> {$booking_data['event_type']}</p>
                    <p><span class='label'>Date:</span> {$formatted_date}</p>
                    <p><span class='label'>Time:</span> {$formatted_time}</p>
                    <p><span class='label'>Location:</span> {$booking_data['event_location']}</p>
                    <p><span class='label'>Guests:</span> {$booking_data['guests']}</p>
                    <p><span class='label'>Package:</span> {$booking_data['package']}</p>
                </div>
                
                " . (!empty($booking_data['message']) ? "<h2>Additional Message</h2><div class='info-box'>{$booking_data['message']}</div>" : "") . "
                
                <div style='text-align: center; margin: 20px 0;'>
                    <a href='http://localhost/mc_website/admin/bookings.php' class='btn'>
                        📋 View in Admin Panel
                    </a>
                </div>
                
                <p><strong>Next Steps:</strong></p>
                <ol>
                    <li>Review booking details in admin panel</li>
                    <li>Contact client to confirm availability</li>
                    <li>Update booking status</li>
                    <li>Send confirmation email to client</li>
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
 * Create client email template
 */
function createClientEmailTemplate($booking_data, $formatted_date, $formatted_time) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #27ae60; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .info-box { background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #27ae60; }
            .label { font-weight: bold; color: #2c3e50; }
            .highlight { background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✅ Thank You for Your Booking!</h1>
                <p>Booking Reference: {$booking_data['booking_ref']}</p>
            </div>
            <div class='content'>
                <p>Dear {$booking_data['name']},</p>
                
                <p>Thank you for choosing <strong>Byiringiro Valentin MC Services</strong> for your special event. We have received your booking request and will contact you shortly to confirm the details.</p>
                
                <h3>Your Booking Summary</h3>
                <div class='info-box'>
                    <p><span class='label'>Event Type:</span> {$booking_data['event_type']}</p>
                    <p><span class='label'>Date:</span> {$formatted_date}</p>
                    <p><span class='label'>Time:</span> {$formatted_time}</p>
                    <p><span class='label'>Location:</span> {$booking_data['event_location']}</p>
                    <p><span class='label'>Expected Guests:</span> {$booking_data['guests']}</p>
                </div>
                
                <h3>What Happens Next?</h3>
                <ol>
                    <li><strong>Review:</strong> We will review your booking request within 24 hours</li>
                    <li><strong>Contact:</strong> We'll call or email you to confirm availability and discuss details</li>
                    <li><strong>Planning:</strong> Work together to plan your perfect event</li>
                    <li><strong>Confirmation:</strong> Receive final confirmation with all arrangements</li>
                </ol>
                
                <div class='highlight'>
                    <p><strong>📝 Important:</strong> Please save your booking reference <strong>{$booking_data['booking_ref']}</strong> for all future communications.</p>
                </div>
                
                <h3>Contact Information</h3>
                <p>If you have any questions or need to make changes:</p>
                <ul>
                    <li><strong>📧 Email:</strong> izabayojeanlucseverin@gmail.com</li>
                    <li><strong>📞 Phone:</strong> +123 456 7890</li>
                </ul>
                
                <p>We look forward to making your event memorable and special!</p>
                
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
 * Test email sending capability
 * 
 * @return array Test results
 */
function testEmailSystem() {
    $test_results = [
        'smtp_configured' => !empty(SMTP_PASSWORD),
        'basic_mail_works' => false,
        'test_email_sent' => false,
        'recommendations' => []
    ];
    
    // Test basic mail function
    $test_email = 'izabayojeanlucseverin@gmail.com';
    $test_subject = 'Email System Test - ' . date('Y-m-d H:i:s');
    $test_message = '<h1>Email Test Successful!</h1><p>Your email system is working properly.</p>';
    
    $test_results['test_email_sent'] = sendEmailBasic($test_email, $test_subject, $test_message);
    $test_results['basic_mail_works'] = $test_results['test_email_sent'];
    
    // Provide recommendations
    if (!$test_results['smtp_configured']) {
        $test_results['recommendations'][] = 'Configure Gmail SMTP for better email delivery';
    }
    
    if (!$test_results['basic_mail_works']) {
        $test_results['recommendations'][] = 'Install local mail server (hMailServer or MailHog)';
        $test_results['recommendations'][] = 'Configure PHP mail settings in php.ini';
        $test_results['recommendations'][] = 'Use Gmail SMTP with app password';
    }
    
    return $test_results;
}
?>
