<?php
/**
 * Notification System - DISABLED
 *
 * This file has been disabled to prevent duplicate notifications.
 * The real_smtp_email.php file is now used instead.
 */

// DISABLED - Use real_smtp_email.php instead
exit('This notification system has been disabled. Use real_smtp_email.php instead.');

require_once 'config.php';

/**
 * Send email notification for new booking
 * 
 * @param array $booking_data Booking information
 * @return bool Success status
 */
function sendBookingNotification($booking_data) {
    if (!ENABLE_EMAIL_NOTIFICATIONS) {
        return true; // Notifications disabled
    }
    
    try {
        // Email subject
        $subject = "New Booking Received - " . $booking_data['booking_ref'];
        
        // Email body
        $message = generateBookingEmailTemplate($booking_data);
        
        // Headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
            'Reply-To: ' . SMTP_FROM_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Send email to admin
        $admin_sent = mail(ADMIN_EMAIL, $subject, $message, implode("\r\n", $headers));
        
        // Send confirmation email to client
        $client_subject = "Booking Confirmation - " . $booking_data['booking_ref'];
        $client_message = generateClientConfirmationTemplate($booking_data);
        $client_sent = mail($booking_data['email'], $client_subject, $client_message, implode("\r\n", $headers));
        
        // Log notification status
        if ($admin_sent && $client_sent) {
            error_log("Booking notifications sent successfully for: " . $booking_data['booking_ref']);
            return true;
        } else {
            error_log("Failed to send booking notifications for: " . $booking_data['booking_ref']);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate admin email template for new booking
 * 
 * @param array $booking_data Booking information
 * @return string HTML email content
 */
function generateBookingEmailTemplate($booking_data) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>New Booking Notification</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; }
            .booking-details { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .detail-row { margin: 10px 0; }
            .label { font-weight: bold; color: #2c3e50; }
            .value { margin-left: 10px; }
            .footer { text-align: center; padding: 20px; color: #666; }
            .btn { background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>New Booking Received!</h1>
                <p>Booking Reference: ' . htmlspecialchars($booking_data['booking_ref']) . '</p>
            </div>
            
            <div class="content">
                <h2>Booking Details</h2>
                <div class="booking-details">
                    <div class="detail-row">
                        <span class="label">Client Name:</span>
                        <span class="value">' . htmlspecialchars($booking_data['name']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Email:</span>
                        <span class="value">' . htmlspecialchars($booking_data['email']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Phone:</span>
                        <span class="value">' . htmlspecialchars($booking_data['phone']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Event Date:</span>
                        <span class="value">' . formatDate($booking_data['event_date']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Event Time:</span>
                        <span class="value">' . formatTime($booking_data['event_time']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Event Type:</span>
                        <span class="value">' . htmlspecialchars($booking_data['event_type']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Location:</span>
                        <span class="value">' . htmlspecialchars($booking_data['event_location']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Number of Guests:</span>
                        <span class="value">' . htmlspecialchars($booking_data['guests']) . '</span>
                    </div>';
    
    if (!empty($booking_data['package'])) {
        $html .= '
                    <div class="detail-row">
                        <span class="label">Package:</span>
                        <span class="value">' . htmlspecialchars($booking_data['package']) . '</span>
                    </div>';
    }
    
    if (!empty($booking_data['message'])) {
        $html .= '
                    <div class="detail-row">
                        <span class="label">Message:</span>
                        <span class="value">' . nl2br(htmlspecialchars($booking_data['message'])) . '</span>
                    </div>';
    }
    
    $html .= '
                </div>
                
                <div style="text-align: center; margin: 20px 0;">
                    <a href="' . SITE_URL . '/admin/bookings.php" class="btn">View in Admin Panel</a>
                </div>
            </div>
            
            <div class="footer">
                <p>This is an automated notification from Valentin MC Services.</p>
                <p>Please log in to your admin panel to manage this booking.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Generate client confirmation email template
 * 
 * @param array $booking_data Booking information
 * @return string HTML email content
 */
function generateClientConfirmationTemplate($booking_data) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Booking Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; }
            .booking-details { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .detail-row { margin: 10px 0; }
            .label { font-weight: bold; color: #2c3e50; }
            .value { margin-left: 10px; }
            .footer { text-align: center; padding: 20px; color: #666; }
            .status { background: #f39c12; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Booking Confirmation</h1>
                <p>Thank you for choosing Valentin MC Services!</p>
            </div>
            
            <div class="content">
                <p>Dear ' . htmlspecialchars($booking_data['name']) . ',</p>
                <p>We have received your booking request and it is currently being reviewed. You will receive a confirmation email once your booking has been approved.</p>
                
                <h3>Your Booking Details:</h3>
                <div class="booking-details">
                    <div class="detail-row">
                        <span class="label">Booking Reference:</span>
                        <span class="value">' . htmlspecialchars($booking_data['booking_ref']) . '</span>
                        <span class="status">PENDING</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Event Date:</span>
                        <span class="value">' . formatDate($booking_data['event_date']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Event Time:</span>
                        <span class="value">' . formatTime($booking_data['event_time']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Event Type:</span>
                        <span class="value">' . htmlspecialchars($booking_data['event_type']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Location:</span>
                        <span class="value">' . htmlspecialchars($booking_data['event_location']) . '</span>
                    </div>
                </div>
                
                <p><strong>What happens next?</strong></p>
                <ul>
                    <li>We will review your booking request within 24 hours</li>
                    <li>You will receive a confirmation email with further details</li>
                    <li>If you have any questions, please contact us directly</li>
                </ul>
                
                <p>Thank you for choosing Valentin MC Services. We look forward to making your event memorable!</p>
            </div>
            
            <div class="footer">
                <p><strong>Contact Information:</strong></p>
                <p>Email: ' . ADMIN_EMAIL . '</p>
                <p>Phone: +123 456 7890</p>
                <p>Website: ' . SITE_URL . '</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Create notification record in database
 * 
 * @param string $type Notification type
 * @param string $title Notification title
 * @param string $message Notification message
 * @param array $data Additional data
 * @return bool Success status
 */
function createNotification($type, $title, $message, $data = []) {
    try {
        $conn = connectDB();
        if (!$conn) {
            return false;
        }
        
        $sql = "INSERT INTO notifications (type, title, message, data, created_at) 
                VALUES (:type, :title, :message, :data, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':data', json_encode($data));
        
        return $stmt->execute();
        
    } catch (Exception $e) {
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}
?>
