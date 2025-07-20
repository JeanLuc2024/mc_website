<?php
/**
 * Contact Form Handler
 * 
 * This file processes the contact form submissions and stores them in the database.
 */

// Include database configuration
require_once 'config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get database connection
    $conn = connectDB();
    
    // Check if database connection was successful
    if (!$conn) {
        $response['message'] = "Sorry, we're experiencing technical difficulties. Please try again later.";
        outputResponse($response);
        exit;
    }
    
    // Validate and sanitize form data
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? sanitizeInput($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $response['message'] = "Please fill in all required fields.";
        outputResponse($response);
        exit;
    }
    
    // Validate email
    if (!validateEmail($email)) {
        $response['message'] = "Please enter a valid email address.";
        outputResponse($response);
        exit;
    }
    
    try {
        // Prepare SQL statement
        $sql = "INSERT INTO contacts (name, email, phone, subject, message, created_at) 
                VALUES (:name, :email, :phone, :subject, :message, NOW())";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        
        // Execute the statement
        $stmt->execute();
        
        // Send confirmation email
        sendContactConfirmationEmail($name, $email, $subject);
        
        // Send notification email to admin
        sendAdminNotificationEmail($name, $email, $phone, $subject, $message);
        
        // Set success response
        $response['success'] = true;
        $response['message'] = "Thank you for your message! We will get back to you as soon as possible.";
        
    } catch(PDOException $e) {
        // Log the error
        error_log("Contact form error: " . $e->getMessage());
        
        // Set error response
        $response['message'] = "Sorry, we couldn't process your message. Please try again later.";
    }
    
    // Close connection
    $conn = null;
    
    // Output response
    outputResponse($response);
    
} else {
    // Not a POST request
    $response['message'] = "Invalid request method.";
    outputResponse($response);
}

/**
 * Send contact confirmation email to user
 */
function sendContactConfirmationEmail($name, $email, $subject) {
    // Email subject
    $email_subject = "Thank you for contacting us - " . $subject;
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Byiringiro Valentin MC <valentin@mcservices.com>" . "\r\n";
    
    // Email message
    $message = "
    <html>
    <head>
        <title>Contact Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #8e44ad; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Thank You for Contacting Us</h1>
            </div>
            <div class='content'>
                <p>Dear $name,</p>
                <p>Thank you for reaching out to Byiringiro Valentin MC Services. We have received your message regarding \"$subject\" and will get back to you as soon as possible.</p>
                
                <p>If you have any urgent inquiries, please feel free to call us at +123 456 7890.</p>
                
                <p>Best regards,<br>
                Byiringiro Valentin<br>
                Master of Ceremony</p>
            </div>
            <div class='footer'>
                <p>&copy; 2025 Byiringiro Valentin MC Services. All Rights Reserved.</p>
                <p>Kigali, Rwanda | +123 456 7890 | valentin@mcservices.com</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Send email
    mail($email, $email_subject, $message, $headers);
}

/**
 * Send notification email to admin
 */
function sendAdminNotificationEmail($name, $email, $phone, $subject, $message) {
    // Admin email
    $admin_email = "valentin@mcservices.com";
    
    // Email subject
    $email_subject = "New Contact Form Submission - " . $subject;
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: MC Website <noreply@mcservices.com>" . "\r\n";
    $headers .= "Reply-To: $email" . "\r\n";
    
    // Email message
    $email_message = "
    <html>
    <head>
        <title>New Contact Form Submission</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #8e44ad; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; }
            .contact-details { background-color: #f9f9f9; padding: 15px; margin: 20px 0; border-left: 4px solid #8e44ad; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>New Contact Form Submission</h1>
            </div>
            <div class='content'>
                <p>You have received a new message from your website contact form.</p>
                
                <div class='contact-details'>
                    <h3>Contact Details:</h3>
                    <p><strong>Name:</strong> $name</p>
                    <p><strong>Email:</strong> $email</p>
                    <p><strong>Phone:</strong> $phone</p>
                    <p><strong>Subject:</strong> $subject</p>
                    <p><strong>Message:</strong></p>
                    <p>$message</p>
                </div>
                
                <p>Please respond to this inquiry as soon as possible.</p>
            </div>
            <div class='footer'>
                <p>&copy; 2025 Byiringiro Valentin MC Services. All Rights Reserved.</p>
                <p>This is an automated message from your website.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Send email
    mail($admin_email, $email_subject, $email_message, $headers);
}

/**
 * Output JSON response and exit
 */
function outputResponse($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
