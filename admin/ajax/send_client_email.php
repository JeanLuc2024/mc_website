<?php
/**
 * Send Email to Client - AJAX Handler
 * 
 * Handles sending emails from admin to clients
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Include required files
require_once '../../php/config.php';
require_once '../../php/enhanced_smtp.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $client_email = trim($_POST['client_email'] ?? '');
    $client_name = trim($_POST['client_name'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $booking_id = intval($_POST['booking_id'] ?? 0);
    
    // Validation
    if (empty($client_email) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Email and message are required']);
        exit;
    }
    
    if (!filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Default subject if empty
    if (empty($subject)) {
        $subject = 'Message from Byiringiro Valentin MC Services';
    }
    
    // Create professional email template
    $html_message = createAdminToClientEmailTemplate($client_name, $message);
    
    try {
        // Send email using enhanced SMTP
        $email_sent = sendSMTPEmail(
            $client_email,
            $subject,
            $html_message,
            'Byiringiro Valentin MC Services',
            ADMIN_EMAIL
        );
        
        if ($email_sent) {
            // Log the email in database if booking_id provided
            if ($booking_id > 0) {
                try {
                    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Insert email record
                    $stmt = $pdo->prepare("INSERT INTO email_notifications (booking_id, recipient_email, recipient_name, email_type, subject, message, status, sent_at) VALUES (?, ?, ?, ?, ?, ?, 'sent', NOW())");
                    $stmt->execute([$booking_id, $client_email, $client_name, 'custom_message', $subject, $message]);
                    
                    $pdo = null;
                } catch (Exception $e) {
                    // Log error but don't fail the email send
                    error_log("Email logging error: " . $e->getMessage());
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Email sent successfully to ' . $client_email
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send email. Please check your email configuration.'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send email. Error: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

/**
 * Create professional email template for admin to client communication
 */
function createAdminToClientEmailTemplate($client_name, $message) {
    $greeting = !empty($client_name) ? "Dear {$client_name}," : "Dear Valued Client,";
    
    return "
    <html>
    <head>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background-color: #f4f4f4;
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background: #fff; 
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: 300;
            }
            .header p {
                margin: 5px 0 0 0;
                opacity: 0.9;
                font-size: 14px;
            }
            .content { 
                padding: 30px 20px; 
            }
            .message-box { 
                background: #f8f9fa; 
                padding: 20px; 
                border-radius: 8px; 
                margin: 20px 0; 
                border-left: 4px solid #3498db;
                font-size: 16px;
                line-height: 1.8;
            }
            .contact-info {
                background: #e8f4f8;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
            .contact-info h3 {
                margin-top: 0;
                color: #2c3e50;
            }
            .contact-info ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .contact-info li {
                padding: 5px 0;
                font-size: 14px;
            }
            .footer { 
                background: #34495e; 
                color: white; 
                padding: 20px; 
                text-align: center; 
                font-size: 12px;
            }
            .footer p {
                margin: 0;
                opacity: 0.8;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Byiringiro Valentin</h1>
                <p>Master of Ceremony Services</p>
            </div>
            
            <div class='content'>
                <p>{$greeting}</p>
                
                <div class='message-box'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                
                <p>Thank you for choosing our services. If you have any questions or need further assistance, please don't hesitate to contact us.</p>
                
                <div class='contact-info'>
                    <h3>📞 Contact Information</h3>
                    <ul>
                        <li>📧 <strong>Email:</strong> byirival009@gmail.com</li>
                        <li>📱 <strong>Phone:</strong> 0788764456</li>
                        <li>📍 <strong>Location:</strong> Kigali, Rwanda</li>
                    </ul>
                </div>
                
                <p>Best regards,<br>
                <strong>Byiringiro Valentin</strong><br>
                <em>Professional Master of Ceremony</em></p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2025 Byiringiro Valentin MC Services. All Rights Reserved.</p>
                <p>Professional MC services for weddings, corporate events, and special occasions.</p>
            </div>
        </div>
    </body>
    </html>";
}
?>
