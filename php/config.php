<?php
/**
 * Database Configuration File
 * 
 * This file contains the database connection settings for the MC website.
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'mc_website');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost/mc_website');
define('ADMIN_EMAIL', 'byirival009@gmail.com');

// Email configuration for notifications
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'byirival009@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'fvaa vjqd hwfv jewt'); // Gmail app password for Booking System
define('SMTP_FROM_EMAIL', 'byirival009@gmail.com');
define('SMTP_FROM_NAME', 'Byiringiro Valentin MC Services');
define('SMTP_ENCRYPTION', 'tls'); // Gmail requires TLS encryption
define('SMTP_AUTH', true); // Gmail requires authentication

// Notification settings
define('ENABLE_EMAIL_NOTIFICATIONS', true);
define('ENABLE_SMS_NOTIFICATIONS', false); // Set to true if you want SMS notifications

// Create database connection
function connectDB() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        // Log error instead of displaying it directly
        error_log("Connection failed: " . $e->getMessage());
        return false;
    }
}

// Function to sanitize user input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to generate a unique booking reference
function generateBookingReference() {
    return 'BK-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

// Function to format date for display
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Function to format time for display
function formatTime($time) {
    return date('g:i A', strtotime($time));
}

// Function to create a notification record
function createNotification($type, $title, $message, $data = null) {
    try {
        $conn = connectDB();
        if (!$conn) {
            return false;
        }

        $sql = "INSERT INTO notifications (type, title, message, data, created_at) VALUES (:type, :title, :message, :data, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':data', $data ? json_encode($data) : null);

        $result = $stmt->execute();
        $conn = null;
        return $result;

    } catch (Exception $e) {
        error_log("Notification creation error: " . $e->getMessage());
        return false;
    }
}

// Function to send booking notification email
function sendBookingNotification($booking_data) {
    try {
        // Admin notification email
        $admin_subject = "New Booking Received - " . $booking_data['booking_ref'];
        $admin_message = createAdminNotificationEmail($booking_data);

        $admin_headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
            'Reply-To: ' . $booking_data['email']
        ];

        $admin_sent = mail(ADMIN_EMAIL, $admin_subject, $admin_message, implode("\r\n", $admin_headers));

        // Client confirmation email
        $client_subject = "Booking Confirmation - " . $booking_data['booking_ref'];
        $client_message = createClientConfirmationEmail($booking_data);

        $client_headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
            'Reply-To: ' . ADMIN_EMAIL
        ];

        $client_sent = mail($booking_data['email'], $client_subject, $client_message, implode("\r\n", $client_headers));

        return $admin_sent && $client_sent;

    } catch (Exception $e) {
        error_log("Email notification error: " . $e->getMessage());
        return false;
    }
}

// Function to create admin notification email content
function createAdminNotificationEmail($booking_data) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>New Booking Received</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .booking-info { background: #f8f9fa; padding: 15px; border-radius: 6px; margin: 15px 0; }
            .booking-detail { margin: 8px 0; }
            .booking-detail strong { color: #2c3e50; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; }
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

                <div class="booking-info">
                    <div class="booking-detail"><strong>Client Name:</strong> ' . htmlspecialchars($booking_data['name']) . '</div>
                    <div class="booking-detail"><strong>Email:</strong> ' . htmlspecialchars($booking_data['email']) . '</div>
                    <div class="booking-detail"><strong>Phone:</strong> ' . htmlspecialchars($booking_data['phone']) . '</div>
                    <div class="booking-detail"><strong>Event Type:</strong> ' . htmlspecialchars($booking_data['event_type']) . '</div>
                    <div class="booking-detail"><strong>Event Date:</strong> ' . formatDate($booking_data['event_date']) . '</div>
                    <div class="booking-detail"><strong>Event Time:</strong> ' . formatTime($booking_data['event_time']) . '</div>
                    <div class="booking-detail"><strong>Location:</strong> ' . htmlspecialchars($booking_data['event_location']) . '</div>
                    <div class="booking-detail"><strong>Guests:</strong> ' . htmlspecialchars($booking_data['guests']) . '</div>';

    if (!empty($booking_data['package'])) {
        $html .= '<div class="booking-detail"><strong>Package:</strong> ' . htmlspecialchars($booking_data['package']) . '</div>';
    }

    if (!empty($booking_data['message'])) {
        $html .= '<div class="booking-detail"><strong>Message:</strong> ' . nl2br(htmlspecialchars($booking_data['message'])) . '</div>';
    }

    $html .= '
                </div>

                <p><strong>Next Steps:</strong></p>
                <ol>
                    <li>Review the booking details</li>
                    <li>Contact the client to confirm availability</li>
                    <li>Update booking status in admin panel</li>
                    <li>Send confirmation or follow-up email</li>
                </ol>

                <p style="text-align: center; margin: 20px 0;">
                    <a href="' . SITE_URL . '/admin/bookings.php" style="background: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">
                        View in Admin Panel
                    </a>
                </p>
            </div>

            <div class="footer">
                <p>This is an automated notification from your MC booking system.</p>
            </div>
        </div>
    </body>
    </html>';

    return $html;
}

// Function to create client confirmation email content
function createClientConfirmationEmail($booking_data) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Booking Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .booking-info { background: #e8f4f8; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #3498db; }
            .booking-detail { margin: 8px 0; }
            .booking-detail strong { color: #2c3e50; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Thank You for Your Booking!</h1>
                <p>Booking Reference: ' . htmlspecialchars($booking_data['booking_ref']) . '</p>
            </div>

            <div class="content">
                <p>Dear ' . htmlspecialchars($booking_data['name']) . ',</p>

                <p>Thank you for choosing Byiringiro Valentin MC Services for your special event. We have received your booking request and will contact you shortly to confirm the details.</p>

                <div class="booking-info">
                    <h3>Your Booking Details</h3>
                    <div class="booking-detail"><strong>Event Type:</strong> ' . htmlspecialchars($booking_data['event_type']) . '</div>
                    <div class="booking-detail"><strong>Date:</strong> ' . formatDate($booking_data['event_date']) . '</div>
                    <div class="booking-detail"><strong>Time:</strong> ' . formatTime($booking_data['event_time']) . '</div>
                    <div class="booking-detail"><strong>Location:</strong> ' . htmlspecialchars($booking_data['event_location']) . '</div>
                    <div class="booking-detail"><strong>Guests:</strong> ' . htmlspecialchars($booking_data['guests']) . '</div>
                </div>

                <p><strong>What happens next?</strong></p>
                <ol>
                    <li>We will review your booking request</li>
                    <li>Contact you within 24 hours to confirm availability</li>
                    <li>Discuss event details and requirements</li>
                    <li>Provide final confirmation and next steps</li>
                </ol>

                <p><strong>Important:</strong> Please save your booking reference <strong>' . htmlspecialchars($booking_data['booking_ref']) . '</strong> for future correspondence.</p>

                <p>If you have any questions or need to make changes, please contact us:</p>
                <ul>
                    <li><strong>Email:</strong> ' . ADMIN_EMAIL . '</li>
                    <li><strong>Phone:</strong> 0788764456</li>
                </ul>

                <p>We look forward to making your event memorable and special!</p>

                <p>Best regards,<br>
                <strong>Byiringiro Valentin</strong><br>
                Master of Ceremony</p>
            </div>

            <div class="footer">
                <p>&copy; 2025 Byiringiro Valentin MC Services. All Rights Reserved.</p>
            </div>
        </div>
    </body>
    </html>';

    return $html;
}
?>
