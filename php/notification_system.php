<?php
/**
 * Comprehensive Notification System
 * 
 * Handles all email notifications and admin alerts
 */

// Prevent multiple inclusions
if (defined('NOTIFICATION_SYSTEM_LOADED')) {
    return;
}
define('NOTIFICATION_SYSTEM_LOADED', true);

/**
 * Database connection for notifications
 */
function getNotificationDB() {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Notification DB connection failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Send booking confirmation email to client
 */
function sendBookingConfirmationEmail($booking_data) {
    $pdo = getNotificationDB();
    if (!$pdo) return false;
    
    $subject = "📋 Booking Confirmation - {$booking_data['booking_ref']}";
    $message = createBookingConfirmationTemplate($booking_data);
    
    // Log email notification
    $email_id = logEmailNotification($pdo, $booking_data['id'], $booking_data['email'], $booking_data['name'], 'booking_confirmation', $subject, $message);
    
    // Send email
    $email_sent = sendEmailNotification($booking_data['email'], $subject, $message);
    
    // Update email status
    updateEmailStatus($pdo, $email_id, $email_sent ? 'sent' : 'failed');
    
    return $email_sent;
}

/**
 * Send admin notification email for new booking
 */
function sendAdminBookingNotification($booking_data) {
    $pdo = getNotificationDB();
    if (!$pdo) return false;
    
    $admin_email = 'byirival009@gmail.com';
    $subject = "🎉 New Booking Received - {$booking_data['booking_ref']}";
    $message = createAdminBookingTemplate($booking_data);
    
    // Log email notification
    $email_id = logEmailNotification($pdo, $booking_data['id'], $admin_email, 'Administrator', 'admin_notification', $subject, $message);
    
    // Send email
    $email_sent = sendEmailNotification($admin_email, $subject, $message);
    
    // Update email status
    updateEmailStatus($pdo, $email_id, $email_sent ? 'sent' : 'failed');
    
    // Create admin panel notification
    createAdminNotification($pdo, $booking_data['id'], 'new_booking', 'New Booking Received', 
        "New booking from {$booking_data['name']} for {$booking_data['event_type']} on " . date('F j, Y', strtotime($booking_data['event_date'])), 'high');
    
    return $email_sent;
}

/**
 * Send status update email to client
 */
function sendStatusUpdateEmail($booking_data, $new_status, $admin_message = '') {
    $pdo = getNotificationDB();
    if (!$pdo) return false;
    
    $status_messages = [
        'confirmed' => ['✅ Booking Confirmed', 'Great news! Your booking has been confirmed.', '#28a745'],
        'cancelled' => ['❌ Booking Cancelled', 'We regret to inform you that your booking has been cancelled.', '#dc3545'],
        'completed' => ['🎉 Event Completed', 'Thank you for choosing our services! Your event has been completed.', '#17a2b8']
    ];
    
    $status_info = $status_messages[$new_status] ?? ['📋 Booking Update', 'Your booking status has been updated.', '#ffc107'];
    
    $subject = $status_info[0] . " - {$booking_data['booking_ref']}";
    $message = createStatusUpdateTemplate($booking_data, $new_status, $status_info[1], $status_info[2], $admin_message);
    
    // Log email notification
    $email_id = logEmailNotification($pdo, $booking_data['id'], $booking_data['email'], $booking_data['name'], 'status_update', $subject, $message);
    
    // Send email
    $email_sent = sendEmailNotification($booking_data['email'], $subject, $message);
    
    // Update email status
    updateEmailStatus($pdo, $email_id, $email_sent ? 'sent' : 'failed');
    
    // Create admin panel notification
    createAdminNotification($pdo, $booking_data['id'], 'booking_update', 'Booking Status Updated', 
        "Booking {$booking_data['booking_ref']} status changed to {$new_status}", 'medium');
    
    return $email_sent;
}

/**
 * Send custom message to client
 */
function sendCustomMessageEmail($booking_data, $custom_subject, $custom_message) {
    $pdo = getNotificationDB();
    if (!$pdo) return false;
    
    $subject = $custom_subject;
    $message = createCustomMessageTemplate($booking_data, $custom_message);
    
    // Log email notification
    $email_id = logEmailNotification($pdo, $booking_data['id'], $booking_data['email'], $booking_data['name'], 'custom_message', $subject, $message);
    
    // Send email
    $email_sent = sendEmailNotification($booking_data['email'], $subject, $message);
    
    // Update email status
    updateEmailStatus($pdo, $email_id, $email_sent ? 'sent' : 'failed');
    
    return $email_sent;
}

/**
 * Log email notification to database
 */
function logEmailNotification($pdo, $booking_id, $recipient_email, $recipient_name, $email_type, $subject, $message) {
    try {
        $sql = "INSERT INTO email_notifications (booking_id, recipient_email, recipient_name, email_type, subject, message, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking_id, $recipient_email, $recipient_name, $email_type, $subject, $message]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Error logging email notification: " . $e->getMessage());
        return null;
    }
}

/**
 * Update email notification status
 */
function updateEmailStatus($pdo, $email_id, $status, $error_message = null) {
    if (!$email_id) return;
    
    try {
        $sql = "UPDATE email_notifications SET status = ?, sent_at = ?, error_message = ? WHERE id = ?";
        $sent_at = ($status === 'sent') ? date('Y-m-d H:i:s') : null;
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $sent_at, $error_message, $email_id]);
    } catch (Exception $e) {
        error_log("Error updating email status: " . $e->getMessage());
    }
}

/**
 * Create admin panel notification
 */
function createAdminNotification($pdo, $booking_id, $type, $title, $message, $priority = 'medium') {
    try {
        $sql = "INSERT INTO admin_notifications (booking_id, type, title, message, priority, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking_id, $type, $title, $message, $priority]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Error creating admin notification: " . $e->getMessage());
        return null;
    }
}

/**
 * Send email using simple PHP mail
 */
function sendEmailNotification($to, $subject, $message) {
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Byiringiro Valentin MC Services <noreply@localhost>',
        'Reply-To: byirival009@gmail.com'
    ];
    
    $email_sent = @mail($to, $subject, $message, implode("\r\n", $headers));
    
    // Log email attempt
    $log_entry = date('Y-m-d H:i:s') . " - Email to: {$to}, Subject: {$subject}, Status: " . ($email_sent ? 'SENT' : 'FAILED') . "\n";
    error_log($log_entry, 3, __DIR__ . '/email_log.txt');
    
    return $email_sent;
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
                    <li><strong>📞 Phone:</strong> 0788764456</li>
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
 * Create admin booking notification template
 */
function createAdminBookingTemplate($booking_data) {
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
 * Create status update email template - DISABLED
 * Use the one in real_smtp_email.php instead
 */
function createStatusUpdateTemplate_DISABLED($booking_data, $status, $status_message, $status_color, $admin_message) {
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
                    <li><strong>📞 Phone:</strong> 0788764456</li>
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
 * Create custom message email template
 */
function createCustomMessageTemplate($booking_data, $custom_message) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #3498db; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .message-content { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3498db; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Message from Byiringiro Valentin MC Services</h1>
                <p>Regarding Booking: {$booking_data['booking_ref']}</p>
            </div>
            
            <div class='content'>
                <p>Dear {$booking_data['name']},</p>
                
                <div class='message-content'>
                    " . nl2br(htmlspecialchars($custom_message)) . "
                </div>
                
                <h3>Contact Information</h3>
                <p>If you have any questions:</p>
                <ul>
                    <li><strong>📧 Email:</strong> byirival009@gmail.com</li>
                    <li><strong>📞 Phone:</strong> 0788764456</li>
                </ul>
                
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
 * Get unread admin notifications count
 */
function getUnreadNotificationsCount() {
    $pdo = getNotificationDB();
    if (!$pdo) return 0;
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = FALSE");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get recent admin notifications
 */
function getRecentNotifications($limit = 10) {
    $pdo = getNotificationDB();
    if (!$pdo) return [];
    
    try {
        $sql = "SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($notification_id) {
    $pdo = getNotificationDB();
    if (!$pdo) return false;
    
    try {
        $sql = "UPDATE admin_notifications SET is_read = TRUE, read_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$notification_id]);
    } catch (Exception $e) {
        return false;
    }
}
?>
