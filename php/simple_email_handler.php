<?php
/**
 * Simple Email Handler for Admin-Client Communication
 * 
 * This handles sending emails from admin to clients
 */

// Include email configuration and REAL SMTP
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/real_smtp_email.php';

/**
 * Send email from admin to client
 * 
 * @param string $to_email Client email
 * @param string $subject Email subject
 * @param string $message Email message
 * @param string $booking_ref Booking reference
 * @return array Result with success status and message
 */
function sendAdminToClientEmail($to_email, $subject, $message, $booking_ref = '') {
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    try {
        // Create professional email template
        $email_body = createAdminResponseEmail($message, $booking_ref);

        // Send email using REAL SMTP system
        $email_sent = sendRealSMTPEmail(
            $to_email,
            $subject,
            $email_body,
            'Byiringiro Valentin MC Services',
            ADMIN_EMAIL
        );
        
        if ($email_sent) {
            $result['success'] = true;
            $result['message'] = "Email sent successfully to {$to_email}";

            // Log the email communication
            logEmailCommunication($to_email, $subject, $message, $booking_ref);

            // Move booking to history after successful admin response
            if (!empty($booking_ref)) {
                moveBookingToHistory($booking_ref);
                $result['message'] .= " Booking moved to history.";
            }

        } else {
            $result['message'] = "Failed to send email. Please check your email configuration.";
        }
        
    } catch (Exception $e) {
        $result['message'] = "Error sending email: " . $e->getMessage();
        error_log("Admin email error: " . $e->getMessage());
    }
    
    return $result;
}

/**
 * Create professional email template for admin responses
 * 
 * @param string $message Admin message
 * @param string $booking_ref Booking reference
 * @return string HTML email content
 */
function createAdminResponseEmail($message, $booking_ref = '') {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .message-content { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3498db; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 14px; }
            .contact-info { background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Byiringiro Valentin MC Services</h1>
                <p>Professional Master of Ceremony</p>
                " . (!empty($booking_ref) ? "<p>Regarding Booking: {$booking_ref}</p>" : "") . "
            </div>
            
            <div class='content'>
                <div class='message-content'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                
                <div class='contact-info'>
                    <h3>Contact Information</h3>
                    <p><strong>📧 Email:</strong> byirival009@gmail.com</p>
                    <p><strong>📞 Phone:</strong> 0788764456</p>
                    <p><strong>📍 Location:</strong> Kigali, Rwanda</p>
                </div>
                
                <p>Thank you for choosing our services. We look forward to making your event special!</p>
                
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
 * Log email communication to database
 * 
 * @param string $to_email Recipient email
 * @param string $subject Email subject
 * @param string $message Email message
 * @param string $booking_ref Booking reference
 */
function logEmailCommunication($to_email, $subject, $message, $booking_ref = '') {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get booking ID first
        $booking_id = null;
        if (!empty($booking_ref)) {
            $stmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_ref = ?");
            $stmt->execute([$booking_ref]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            $booking_id = $booking ? $booking['id'] : null;
        }

        $sql = "INSERT INTO email_notifications (booking_id, recipient_email, email_type, subject, message, status, sent_at)
                VALUES (?, ?, 'admin_response', ?, ?, 'sent', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking_id, $to_email, $subject, $message]);
        
    } catch (Exception $e) {
        error_log("Email logging error: " . $e->getMessage());
    }
}

/**
 * Get email history for a booking
 * 
 * @param string $booking_ref Booking reference
 * @return array Email history
 */
function getEmailHistory($booking_ref) {
    $emails = [];
    
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get booking ID first
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_ref = ?");
        $stmt->execute([$booking_ref]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return [];
        }

        $sql = "SELECT * FROM email_notifications WHERE booking_id = ? ORDER BY sent_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking['id']]);
        
        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Email history error: " . $e->getMessage());
    }
    
    return $emails;
}

// Handle AJAX requests for email sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_email') {
    header('Content-Type: application/json');
    
    $to_email = trim($_POST['to_email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $booking_ref = trim($_POST['booking_ref'] ?? '');
    
    if (empty($to_email) || empty($message)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email and message are required'
        ]);
        exit;
    }
    
    if (empty($subject)) {
        $subject = "Response to your booking" . (!empty($booking_ref) ? " - {$booking_ref}" : "");
    }
    
    $result = sendAdminToClientEmail($to_email, $subject, $message, $booking_ref);
    echo json_encode($result);
    exit;
}

/**
 * Move booking to history after admin response
 *
 * @param string $booking_ref Booking reference
 * @return bool Success status
 */
function moveBookingToHistory($booking_ref) {
    try {
        $conn = connectDB();
        if (!$conn) {
            return false;
        }

        // Get the booking data
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_ref = ?");
        $stmt->execute([$booking_ref]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return false;
        }

        // Insert into booking_history
        $history_sql = "INSERT INTO booking_history
                       (original_booking_id, booking_ref, name, email, phone, event_date, event_time,
                        event_type, event_location, guests, package, message, status, admin_notes, original_created_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?, ?)";

        $history_stmt = $conn->prepare($history_sql);
        $history_result = $history_stmt->execute([
            $booking['id'],
            $booking['booking_ref'],
            $booking['name'],
            $booking['email'],
            $booking['phone'],
            $booking['event_date'],
            $booking['event_time'],
            $booking['event_type'],
            $booking['event_location'],
            $booking['guests'],
            $booking['package'],
            $booking['message'],
            $booking['admin_notes'] ?? '',
            $booking['created_at']
        ]);

        if ($history_result) {
            // Delete from active bookings
            $delete_stmt = $conn->prepare("DELETE FROM bookings WHERE booking_ref = ?");
            $delete_result = $delete_stmt->execute([$booking_ref]);

            return $delete_result;
        }

        return false;

    } catch (Exception $e) {
        error_log("Error moving booking to history: " . $e->getMessage());
        return false;
    }
}
?>
