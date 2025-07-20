<?php
/**
 * Email Communication System
 * 
 * This file handles email communication between admin and clients.
 */

require_once 'config.php';

/**
 * Send email to client from admin panel
 * 
 * @param int $booking_id Booking ID
 * @param string $to_email Client email
 * @param string $subject Email subject
 * @param string $message Email message
 * @param string $email_type Type of email
 * @param int $admin_id Admin user ID
 * @param int $reply_to_email_id ID of email being replied to (optional)
 * @return array Result with success status and message
 */
function sendEmailToClient($booking_id, $to_email, $subject, $message, $email_type = 'custom_reply', $admin_id = null, $reply_to_email_id = null) {
    $result = ['success' => false, 'message' => ''];
    
    try {
        // Get database connection
        $conn = connectDB();
        if (!$conn) {
            throw new Exception('Database connection failed');
        }
        
        // Get booking details for email personalization
        $booking_sql = "SELECT * FROM bookings WHERE id = :booking_id";
        $booking_stmt = $conn->prepare($booking_sql);
        $booking_stmt->bindParam(':booking_id', $booking_id);
        $booking_stmt->execute();
        $booking = $booking_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            throw new Exception('Booking not found');
        }
        
        // Personalize the message with booking details
        $personalized_message = personalizeEmailMessage($message, $booking);
        $personalized_subject = personalizeEmailMessage($subject, $booking);
        
        // Prepare email headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
            'Reply-To: ' . ADMIN_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Create professional email template
        $html_message = createProfessionalEmailTemplate($personalized_subject, $personalized_message, $booking);
        
        // Send email
        $email_sent = mail($to_email, $personalized_subject, $html_message, implode("\r\n", $headers));
        
        if ($email_sent) {
            // Log email in database
            $log_sql = "INSERT INTO email_communications (booking_id, from_email, to_email, subject, message, email_type, sent_by_admin_id, sent_at, reply_to_email_id) 
                       VALUES (:booking_id, :from_email, :to_email, :subject, :message, :email_type, :admin_id, NOW(), :reply_to_email_id)";
            
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bindParam(':booking_id', $booking_id);
            $log_stmt->bindParam(':from_email', SMTP_FROM_EMAIL);
            $log_stmt->bindParam(':to_email', $to_email);
            $log_stmt->bindParam(':subject', $personalized_subject);
            $log_stmt->bindParam(':message', $personalized_message);
            $log_stmt->bindParam(':email_type', $email_type);
            $log_stmt->bindParam(':admin_id', $admin_id);
            $log_stmt->bindParam(':reply_to_email_id', $reply_to_email_id);
            $log_stmt->execute();
            
            // Update booking email tracking
            $update_sql = "UPDATE bookings SET last_email_sent = NOW(), email_count = email_count + 1, last_email_type = :email_type WHERE id = :booking_id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':email_type', $email_type);
            $update_stmt->bindParam(':booking_id', $booking_id);
            $update_stmt->execute();
            
            $result['success'] = true;
            $result['message'] = 'Email sent successfully to ' . $to_email;
            
            // Log activity
            error_log("Email sent to client: {$to_email} for booking: {$booking['booking_ref']}");
            
        } else {
            throw new Exception('Failed to send email');
        }
        
    } catch (Exception $e) {
        $result['message'] = 'Error: ' . $e->getMessage();
        error_log("Email sending error: " . $e->getMessage());
    }
    
    return $result;
}

/**
 * Personalize email message with booking details
 * 
 * @param string $message Original message
 * @param array $booking Booking details
 * @return string Personalized message
 */
function personalizeEmailMessage($message, $booking) {
    $replacements = [
        '{client_name}' => $booking['name'],
        '{booking_ref}' => $booking['booking_ref'],
        '{event_type}' => $booking['event_type'],
        '{event_date}' => formatDate($booking['event_date']),
        '{event_time}' => formatTime($booking['event_time']),
        '{event_location}' => $booking['event_location'],
        '{guests}' => $booking['guests'],
        '{package}' => $booking['package'] ?? 'Standard',
        '{phone}' => $booking['phone'],
        '{email}' => $booking['email']
    ];
    
    return str_replace(array_keys($replacements), array_values($replacements), $message);
}

/**
 * Create professional email template
 * 
 * @param string $subject Email subject
 * @param string $message Email message
 * @param array $booking Booking details
 * @return string HTML email template
 */
function createProfessionalEmailTemplate($subject, $message, $booking) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>' . htmlspecialchars($subject) . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
            .header { background: #2c3e50; color: white; padding: 30px 20px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; }
            .header p { margin: 5px 0 0 0; opacity: 0.9; }
            .content { padding: 30px 20px; }
            .message { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3498db; }
            .booking-info { background: #e8f4f8; padding: 15px; border-radius: 6px; margin: 20px 0; }
            .booking-info h3 { margin: 0 0 10px 0; color: #2c3e50; }
            .booking-detail { margin: 5px 0; }
            .booking-detail strong { color: #2c3e50; }
            .footer { background: #34495e; color: white; padding: 20px; text-align: center; }
            .footer p { margin: 5px 0; }
            .contact-info { margin: 15px 0; }
            .contact-info a { color: #3498db; text-decoration: none; }
            .signature { margin: 30px 0; padding: 20px; background: #f1f2f6; border-radius: 6px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Byiringiro Valentin</h1>
                <p>Professional Master of Ceremony</p>
            </div>
            
            <div class="content">
                <div class="message">
                    ' . nl2br(htmlspecialchars($message)) . '
                </div>
                
                <div class="booking-info">
                    <h3>Your Booking Reference</h3>
                    <div class="booking-detail"><strong>Reference:</strong> ' . htmlspecialchars($booking['booking_ref']) . '</div>
                    <div class="booking-detail"><strong>Event Type:</strong> ' . htmlspecialchars($booking['event_type']) . '</div>
                    <div class="booking-detail"><strong>Date:</strong> ' . formatDate($booking['event_date']) . '</div>
                    <div class="booking-detail"><strong>Time:</strong> ' . formatTime($booking['event_time']) . '</div>
                    <div class="booking-detail"><strong>Location:</strong> ' . htmlspecialchars($booking['event_location']) . '</div>
                </div>
                
                <div class="signature">
                    <p><strong>Best regards,</strong></p>
                    <p><strong>Byiringiro Valentin</strong><br>
                    Master of Ceremony</p>
                    
                    <div class="contact-info">
                        <p><strong>Contact Information:</strong></p>
                        <p>📞 Phone: +123 456 7890</p>
                        <p>📧 Email: <a href="mailto:valentin@mcservices.com">valentin@mcservices.com</a></p>
                        <p>📍 Location: Kigali, Rwanda</p>
                        <p>🌐 Website: <a href="' . SITE_URL . '">' . SITE_URL . '</a></p>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; 2025 Byiringiro Valentin MC Services. All Rights Reserved.</p>
                <p>Making your events memorable and special</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Get email templates
 * 
 * @param string $category Template category (optional)
 * @return array List of email templates
 */
function getEmailTemplates($category = null) {
    try {
        $conn = connectDB();
        if (!$conn) {
            return [];
        }
        
        $sql = "SELECT * FROM email_templates WHERE is_active = TRUE";
        if ($category) {
            $sql .= " AND template_category = :category";
        }
        $sql .= " ORDER BY template_name";
        
        $stmt = $conn->prepare($sql);
        if ($category) {
            $stmt->bindParam(':category', $category);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error fetching email templates: " . $e->getMessage());
        return [];
    }
}

/**
 * Get email history for a booking
 * 
 * @param int $booking_id Booking ID
 * @return array Email history
 */
function getEmailHistory($booking_id) {
    try {
        $conn = connectDB();
        if (!$conn) {
            return [];
        }
        
        $sql = "SELECT ec.*, au.full_name as sent_by_name 
                FROM email_communications ec 
                LEFT JOIN admin_users au ON ec.sent_by_admin_id = au.id 
                WHERE ec.booking_id = :booking_id 
                ORDER BY ec.sent_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error fetching email history: " . $e->getMessage());
        return [];
    }
}

/**
 * Get email communication statistics
 * 
 * @return array Statistics
 */
function getEmailStats() {
    try {
        $conn = connectDB();
        if (!$conn) {
            return [];
        }
        
        $stats = [];
        
        // Total emails sent
        $total_sql = "SELECT COUNT(*) as total FROM email_communications";
        $total_stmt = $conn->prepare($total_sql);
        $total_stmt->execute();
        $stats['total_emails'] = $total_stmt->fetch()['total'];
        
        // Emails sent today
        $today_sql = "SELECT COUNT(*) as today FROM email_communications WHERE DATE(sent_at) = CURDATE()";
        $today_stmt = $conn->prepare($today_sql);
        $today_stmt->execute();
        $stats['emails_today'] = $today_stmt->fetch()['today'];
        
        // Emails by type
        $type_sql = "SELECT email_type, COUNT(*) as count FROM email_communications GROUP BY email_type";
        $type_stmt = $conn->prepare($type_sql);
        $type_stmt->execute();
        $stats['by_type'] = $type_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error fetching email stats: " . $e->getMessage());
        return [];
    }
}
?>
