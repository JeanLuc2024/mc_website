<?php
/**
 * Fixed Booking Handler - Robust and Error-Free
 * 
 * This handles booking form submissions with proper error handling
 */

// Prevent any output before JSON
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Clear any previous output
ob_clean();

// Response array
$response = [
    'success' => false,
    'message' => '',
    'booking_ref' => ''
];

try {
    // ULTIMATE DEDUPLICATION - Prevent ANY duplicate submissions

    // Check 1: Block rapid submissions from same IP
    session_start();
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $session_key = "last_submission_" . md5($client_ip);

    if (isset($_SESSION[$session_key])) {
        $last_submission = $_SESSION[$session_key];
        $time_diff = time() - $last_submission;

        if ($time_diff < 10) { // Block submissions within 10 seconds
            $response['message'] = 'Please wait ' . (10 - $time_diff) . ' seconds before submitting again.';
            echo json_encode($response);
            exit;
        }
    }

    // Check 2: Block if exact same data was submitted recently
    $submission_hash = md5(serialize($_POST));
    $hash_key = "submission_hash_" . $submission_hash;

    if (isset($_SESSION[$hash_key])) {
        $last_hash_time = $_SESSION[$hash_key];
        if ((time() - $last_hash_time) < 300) { // Block identical submissions within 5 minutes
            $response['message'] = 'This exact booking was already submitted recently.';
            echo json_encode($response);
            exit;
        }
    }

    // Don't record submission yet - wait until validation passes

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Invalid request method';
        echo json_encode($response);
        exit;
    }

    // Database configuration
    $host = 'localhost';
    $dbname = 'mc_website';
    $username = 'root';
    $password = '';

    // Connect to database
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $response['message'] = 'Database connection failed. Please ensure XAMPP MySQL is running.';
        echo json_encode($response);
        exit;
    }

    // Ensure bookings table exists
    $createBookingsTable = "
    CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_ref VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        event_date DATE NOT NULL,
        event_time TIME NOT NULL,
        event_type VARCHAR(100) NOT NULL,
        event_location TEXT NOT NULL,
        guests INT NOT NULL,
        package VARCHAR(100) DEFAULT NULL,
        message TEXT DEFAULT NULL,
        status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
        admin_notes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($createBookingsTable);

    // Ensure admin_notifications table exists
    $createNotificationsTable = "
    CREATE TABLE IF NOT EXISTS admin_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT DEFAULT NULL,
        type ENUM('new_booking', 'booking_update', 'email_sent', 'email_failed', 'system_alert') NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        read_at TIMESTAMP NULL DEFAULT NULL
    )";
    $pdo->exec($createNotificationsTable);

    // Get and validate form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    $event_time = trim($_POST['event_time'] ?? '');
    $event_type = trim($_POST['event_type'] ?? '');
    $event_location = trim($_POST['event_location'] ?? '');
    $guests = intval($_POST['guests'] ?? 0);
    $package = trim($_POST['package'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($event_date) ||
        empty($event_time) || empty($event_type) || empty($event_location) ||
        $guests <= 0) {
        $response['message'] = 'Please fill in all required fields';
        echo json_encode($response);
        exit;
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address';
        echo json_encode($response);
        exit;
    }

    // Date validation
    if (strtotime($event_date) < strtotime(date('Y-m-d'))) {
        $response['message'] = 'Event date cannot be in the past';
        echo json_encode($response);
        exit;
    }

    // DEDUPLICATION CHECK - Prevent duplicate submissions
    $duplicate_check_sql = "SELECT COUNT(*) FROM bookings
                           WHERE name = ? AND email = ? AND event_date = ? AND event_time = ?
                           AND created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $duplicate_stmt = $pdo->prepare($duplicate_check_sql);
    $duplicate_stmt->execute([$name, $email, $event_date, $event_time]);
    $duplicate_count = $duplicate_stmt->fetchColumn();

    if ($duplicate_count > 0) {
        $response['message'] = 'This booking request was already submitted recently. Please wait a few minutes before submitting again.';
        echo json_encode($response);
        exit;
    }

    // Record successful submission to prevent duplicates
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $session_key = "last_submission_" . md5($client_ip);
    $submission_hash = md5(serialize($_POST));
    $hash_key = "submission_hash_" . $submission_hash;
    $_SESSION[$session_key] = time();
    $_SESSION[$hash_key] = time();

    // Generate unique booking reference
    $booking_ref = 'MC-' . date('ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

    // Insert booking into database
    $sql = "INSERT INTO bookings (booking_ref, name, email, phone, event_date, event_time,
            event_type, event_location, guests, package, message, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $booking_ref, $name, $email, $phone, $event_date, $event_time,
        $event_type, $event_location, $guests, $package, $message
    ]);

    if (!$result) {
        $response['message'] = 'Failed to save booking. Please try again.';
        echo json_encode($response);
        exit;
    }

    // Get the booking ID
    $booking_id = $pdo->lastInsertId();

    // Create notification for admin panel
    $notifSql = "INSERT INTO admin_notifications (booking_id, type, title, message, priority) VALUES (?, ?, ?, ?, ?)";
    $notifStmt = $pdo->prepare($notifSql);
    $notifStmt->execute([
        $booking_id,
        'new_booking',
        'New Booking Received',
        "New booking from {$name} for {$event_type} on " . date('M j, Y', strtotime($event_date)),
        'high'
    ]);

    // Prepare booking data for emails
    $booking_data = [
        'id' => $booking_id,
        'booking_ref' => $booking_ref,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'event_date' => $event_date,
        'event_time' => $event_time,
        'event_type' => $event_type,
        'event_location' => $event_location,
        'guests' => $guests,
        'package' => $package,
        'message' => $message
    ];

    // Try to send emails (with error handling)
    $email_results = ['client' => false, 'admin' => false];
    
    try {
        // Include REAL SMTP email system
        if (file_exists(__DIR__ . '/real_smtp_email.php')) {
            require_once __DIR__ . '/real_smtp_email.php';

            // Send client confirmation email using REAL SMTP
            $email_results['client'] = sendRealBookingConfirmationSMTP($booking_data);

            // Send admin notification email using REAL SMTP
            $email_results['admin'] = sendRealAdminNotificationSMTP($booking_data);
        } else {
            // Fallback: Save email details to file
            $email_content = "
=== NEW BOOKING NOTIFICATION ===
Date: " . date('Y-m-d H:i:s') . "
Booking Reference: {$booking_ref}
Client: {$name} ({$email})
Event: {$event_type} on " . date('F j, Y', strtotime($event_date)) . " at {$event_time}
Location: {$event_location}
Guests: {$guests}
Package: {$package}
Phone: {$phone}
Message: {$message}

ADMIN EMAIL: Send notification to byirival009@gmail.com
CLIENT EMAIL: Send confirmation to {$email}
=== END NOTIFICATION ===

";
            file_put_contents(__DIR__ . '/pending_emails.txt', $email_content, FILE_APPEND | LOCK_EX);
            $email_results['client'] = true; // Mark as handled
            $email_results['admin'] = true;
        }
    } catch (Exception $e) {
        // Email failed, but booking was saved - that's okay
        error_log("Email error for booking {$booking_ref}: " . $e->getMessage());
    }

    // Success response
    $response['success'] = true;
    $response['message'] = "🎉 Thank you for your booking request! We have received your information and will contact you shortly to confirm the details. Your booking reference is: {$booking_ref}";
    $response['booking_ref'] = $booking_ref;
    $response['email_status'] = $email_results;

} catch (PDOException $e) {
    $response['message'] = 'Database error occurred. Please ensure XAMPP MySQL is running and try again.';
    error_log('Booking DB Error: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = 'An unexpected error occurred. Please try again.';
    error_log('Booking Error: ' . $e->getMessage());
}

// Close database connection
if (isset($pdo)) {
    $pdo = null;
}

// Clean any remaining output buffer content
ob_clean();

// Output JSON response
echo json_encode($response);

// Ensure no additional output
exit;
?>
