<?php
/**
 * Create Sample Data for Dashboard Demo
 * 
 * This script creates realistic sample data to demonstrate the dashboard
 */

echo "<h2>📊 Creating Sample Data for Dashboard Demo...</h2>";

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Include notification system
require_once 'notification_system.php';

echo "<h3>1. Creating Sample Bookings</h3>";

// Sample booking data
$sample_bookings = [
    [
        'name' => 'Sarah Johnson',
        'email' => 'sarah.johnson@email.com',
        'phone' => '+250788123456',
        'event_type' => 'Wedding Ceremony',
        'event_date' => date('Y-m-d', strtotime('+15 days')),
        'event_time' => '14:00:00',
        'event_location' => 'Kigali Convention Centre',
        'guests' => 150,
        'package' => 'Premium Package',
        'message' => 'Looking forward to having you as our MC for our special day!',
        'status' => 'pending'
    ],
    [
        'name' => 'Robert Uwimana',
        'email' => 'robert.uwimana@company.com',
        'phone' => '+250788234567',
        'event_type' => 'Corporate Meeting',
        'event_date' => date('Y-m-d', strtotime('+8 days')),
        'event_time' => '09:00:00',
        'event_location' => 'Radisson Blu Hotel',
        'guests' => 80,
        'package' => 'Basic Package',
        'message' => 'Annual company meeting, need professional MC services.',
        'status' => 'confirmed'
    ],
    [
        'name' => 'Marie Claire Mukamana',
        'email' => 'marie.mukamana@email.com',
        'phone' => '+250788345678',
        'event_type' => 'Anniversary Celebration',
        'event_date' => date('Y-m-d', strtotime('+22 days')),
        'event_time' => '18:00:00',
        'event_location' => 'Lake Kivu Resort',
        'guests' => 100,
        'package' => 'Deluxe Package',
        'message' => '25th wedding anniversary celebration with family and friends.',
        'status' => 'pending'
    ],
    [
        'name' => 'Jean Baptiste Nzeyimana',
        'email' => 'jean.nzeyimana@email.com',
        'phone' => '+250788456789',
        'event_type' => 'Wedding Ceremony',
        'event_date' => date('Y-m-d', strtotime('+30 days')),
        'event_time' => '16:00:00',
        'event_location' => 'Nyanza Cultural Site',
        'guests' => 200,
        'package' => 'Deluxe Package',
        'message' => 'Traditional wedding ceremony, need MC familiar with Rwandan customs.',
        'status' => 'confirmed'
    ],
    [
        'name' => 'Grace Uwimana',
        'email' => 'grace.uwimana@email.com',
        'phone' => '+250788567890',
        'event_type' => 'Corporate Meeting',
        'event_date' => date('Y-m-d', strtotime('+5 days')),
        'event_time' => '10:00:00',
        'event_location' => 'Kigali Heights',
        'guests' => 50,
        'package' => 'Basic Package',
        'message' => 'Product launch event for our new software.',
        'status' => 'pending'
    ],
    [
        'name' => 'David Mugisha',
        'email' => 'david.mugisha@email.com',
        'phone' => '+250788678901',
        'event_type' => 'Anniversary Celebration',
        'event_date' => date('Y-m-d', strtotime('-2 days')),
        'event_time' => '19:00:00',
        'event_location' => 'Serena Hotel',
        'guests' => 75,
        'package' => 'Premium Package',
        'message' => '10th wedding anniversary celebration.',
        'status' => 'completed'
    ],
    [
        'name' => 'Alice Nyirahabimana',
        'email' => 'alice.nyirahabimana@email.com',
        'phone' => '+250788789012',
        'event_type' => 'Wedding Ceremony',
        'event_date' => date('Y-m-d', strtotime('+45 days')),
        'event_time' => '15:00:00',
        'event_location' => 'Volcanoes National Park',
        'guests' => 120,
        'package' => 'Premium Package',
        'message' => 'Destination wedding with mountain views.',
        'status' => 'pending'
    ],
    [
        'name' => 'Emmanuel Habimana',
        'email' => 'emmanuel.habimana@email.com',
        'phone' => '+250788890123',
        'event_type' => 'Corporate Meeting',
        'event_date' => date('Y-m-d', strtotime('-5 days')),
        'event_time' => '14:00:00',
        'event_location' => 'BK Arena',
        'guests' => 300,
        'package' => 'Deluxe Package',
        'message' => 'Annual shareholders meeting.',
        'status' => 'completed'
    ],
    [
        'name' => 'Immaculée Uwimana',
        'email' => 'immaculee.uwimana@email.com',
        'phone' => '+250788901234',
        'event_type' => 'Anniversary Celebration',
        'event_date' => date('Y-m-d', strtotime('+12 days')),
        'event_time' => '17:00:00',
        'event_location' => 'Akagera National Park',
        'guests' => 60,
        'package' => 'Basic Package',
        'message' => 'Silver wedding anniversary in nature setting.',
        'status' => 'confirmed'
    ],
    [
        'name' => 'Patrick Nkurunziza',
        'email' => 'patrick.nkurunziza@email.com',
        'phone' => '+250789012345',
        'event_type' => 'Wedding Ceremony',
        'event_date' => date('Y-m-d', strtotime('+60 days')),
        'event_time' => '13:00:00',
        'event_location' => 'Gisozi Memorial',
        'guests' => 180,
        'package' => 'Premium Package',
        'message' => 'Church wedding ceremony followed by reception.',
        'status' => 'pending'
    ]
];

$created_bookings = 0;
$booking_ids = [];

foreach ($sample_bookings as $booking) {
    try {
        // Generate booking reference
        $booking_ref = 'MC-' . date('ymd') . '-' . strtoupper(substr(md5($booking['name'] . time()), 0, 6));
        
        // Insert booking
        $sql = "INSERT INTO bookings (booking_ref, name, email, phone, event_type, event_date, event_time, event_location, guests, package, message, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $created_at = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $booking_ref,
            $booking['name'],
            $booking['email'],
            $booking['phone'],
            $booking['event_type'],
            $booking['event_date'],
            $booking['event_time'],
            $booking['event_location'],
            $booking['guests'],
            $booking['package'],
            $booking['message'],
            $booking['status'],
            $created_at
        ]);
        
        $booking_id = $pdo->lastInsertId();
        $booking_ids[] = $booking_id;
        $created_bookings++;
        
        echo "<p style='color: green;'>✅ Created booking: {$booking['name']} - {$booking['event_type']} ({$booking['status']})</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error creating booking for {$booking['name']}: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>2. Creating Sample Notifications</h3>";

// Sample notifications
$sample_notifications = [
    [
        'type' => 'new_booking',
        'title' => 'New Wedding Booking Received',
        'message' => 'Sarah Johnson has requested MC services for her wedding ceremony.',
        'priority' => 'high',
        'is_read' => false
    ],
    [
        'type' => 'booking_update',
        'title' => 'Booking Status Updated',
        'message' => 'Robert Uwimana\'s corporate meeting has been confirmed.',
        'priority' => 'medium',
        'is_read' => false
    ],
    [
        'type' => 'email_sent',
        'title' => 'Confirmation Email Sent',
        'message' => 'Booking confirmation email sent to Marie Claire Mukamana.',
        'priority' => 'low',
        'is_read' => true
    ],
    [
        'type' => 'new_booking',
        'title' => 'Anniversary Celebration Booking',
        'message' => 'New anniversary celebration booking from Grace Uwimana.',
        'priority' => 'high',
        'is_read' => false
    ],
    [
        'type' => 'system_alert',
        'title' => 'System Maintenance Complete',
        'message' => 'Email notification system has been updated successfully.',
        'priority' => 'medium',
        'is_read' => true
    ],
    [
        'type' => 'booking_update',
        'title' => 'Event Completed',
        'message' => 'David Mugisha\'s anniversary celebration has been marked as completed.',
        'priority' => 'low',
        'is_read' => false
    ],
    [
        'type' => 'new_booking',
        'title' => 'Destination Wedding Inquiry',
        'message' => 'Alice Nyirahabimana inquired about MC services for destination wedding.',
        'priority' => 'high',
        'is_read' => false
    ],
    [
        'type' => 'email_sent',
        'title' => 'Status Update Email Sent',
        'message' => 'Status update email sent to Emmanuel Habimana.',
        'priority' => 'low',
        'is_read' => true
    ],
    [
        'type' => 'booking_update',
        'title' => 'Booking Confirmed',
        'message' => 'Immaculée Uwimana\'s anniversary celebration has been confirmed.',
        'priority' => 'medium',
        'is_read' => false
    ],
    [
        'type' => 'new_booking',
        'title' => 'Church Wedding Booking',
        'message' => 'Patrick Nkurunziza has requested MC services for church wedding.',
        'priority' => 'high',
        'is_read' => false
    ]
];

$created_notifications = 0;

foreach ($sample_notifications as $index => $notification) {
    try {
        $booking_id = isset($booking_ids[$index]) ? $booking_ids[$index] : null;
        $created_at = date('Y-m-d H:i:s', strtotime('-' . rand(1, 10) . ' hours'));
        
        $sql = "INSERT INTO admin_notifications (booking_id, type, title, message, priority, is_read, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $booking_id,
            $notification['type'],
            $notification['title'],
            $notification['message'],
            $notification['priority'],
            $notification['is_read'],
            $created_at
        ]);
        
        $created_notifications++;
        echo "<p style='color: green;'>✅ Created notification: {$notification['title']}</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error creating notification: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>3. Creating Sample Email Records</h3>";

// Sample email records
$email_types = ['booking_confirmation', 'status_update', 'admin_notification', 'custom_message'];
$email_statuses = ['sent', 'sent', 'sent', 'failed']; // Mostly sent, some failed

$created_emails = 0;

foreach ($booking_ids as $index => $booking_id) {
    try {
        // Get booking details
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($booking) {
            // Create 2-3 email records per booking
            $num_emails = rand(2, 3);
            
            for ($i = 0; $i < $num_emails; $i++) {
                $email_type = $email_types[array_rand($email_types)];
                $status = $email_statuses[array_rand($email_statuses)];
                
                $subjects = [
                    'booking_confirmation' => "📋 Booking Confirmation - {$booking['booking_ref']}",
                    'status_update' => "✅ Booking Status Update - {$booking['booking_ref']}",
                    'admin_notification' => "🎉 New Booking Received - {$booking['booking_ref']}",
                    'custom_message' => "Message from Byiringiro Valentin MC Services"
                ];
                
                $subject = $subjects[$email_type];
                $message = "Sample email content for {$email_type}";
                $created_at = date('Y-m-d H:i:s', strtotime('-' . rand(1, 20) . ' hours'));
                $sent_at = ($status === 'sent') ? $created_at : null;
                
                $sql = "INSERT INTO email_notifications (booking_id, recipient_email, recipient_name, email_type, subject, message, status, sent_at, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $booking_id,
                    $booking['email'],
                    $booking['name'],
                    $email_type,
                    $subject,
                    $message,
                    $status,
                    $sent_at,
                    $created_at
                ]);
                
                $created_emails++;
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error creating email records: " . $e->getMessage() . "</p>";
    }
}

echo "<p style='color: green;'>✅ Created {$created_emails} email records</p>";

echo "<h3>4. Sample Data Summary</h3>";

// Get final statistics
try {
    $stats = [
        'total_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
        'pending_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
        'confirmed_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn(),
        'completed_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetchColumn(),
        'unread_notifications' => $pdo->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = FALSE")->fetchColumn(),
        'total_emails' => $pdo->query("SELECT COUNT(*) FROM email_notifications")->fetchColumn(),
        'sent_emails' => $pdo->query("SELECT COUNT(*) FROM email_notifications WHERE status = 'sent'")->fetchColumn()
    ];
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
    echo "<h4>🎉 Sample Data Created Successfully!</h4>";
    echo "<ul>";
    echo "<li><strong>Bookings Created:</strong> {$created_bookings}</li>";
    echo "<li><strong>Notifications Created:</strong> {$created_notifications}</li>";
    echo "<li><strong>Email Records Created:</strong> {$created_emails}</li>";
    echo "</ul>";
    
    echo "<h5>📊 Current Dashboard Statistics:</h5>";
    echo "<ul>";
    echo "<li><strong>Total Bookings:</strong> {$stats['total_bookings']}</li>";
    echo "<li><strong>Pending Bookings:</strong> {$stats['pending_bookings']}</li>";
    echo "<li><strong>Confirmed Bookings:</strong> {$stats['confirmed_bookings']}</li>";
    echo "<li><strong>Completed Bookings:</strong> {$stats['completed_bookings']}</li>";
    echo "<li><strong>Unread Notifications:</strong> {$stats['unread_notifications']}</li>";
    echo "<li><strong>Total Emails:</strong> {$stats['total_emails']}</li>";
    echo "<li><strong>Sent Emails:</strong> {$stats['sent_emails']}</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error getting final statistics: " . $e->getMessage() . "</p>";
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🎛️ View Your Amazing Dashboard Now!</h4>";
echo "<p><strong>Admin Dashboard:</strong> <a href='../admin/dashboard.php' target='_blank'>http://localhost/mc_website/admin/dashboard.php</a></p>";
echo "<p><strong>Login:</strong> admin / admin123</p>";

echo "<h5>✨ Dashboard Features to See:</h5>";
echo "<ul>";
echo "<li>🎨 <strong>Animated stat cards</strong> with hover effects</li>";
echo "<li>📊 <strong>Real-time statistics</strong> with sample data</li>";
echo "<li>🔔 <strong>Notification widget</strong> with unread count</li>";
echo "<li>📋 <strong>Recent bookings</strong> and notifications</li>";
echo "<li>🎯 <strong>Interactive elements</strong> and smooth animations</li>";
echo "</ul>";

echo "<p><strong>Test the complete workflow:</strong></p>";
echo "<ol>";
echo "<li>Login to admin dashboard</li>";
echo "<li>See animated stat cards with sample data</li>";
echo "<li>Check notification widget in header</li>";
echo "<li>View recent bookings and notifications</li>";
echo "<li>Navigate to bookings page to manage sample bookings</li>";
echo "<li>Test status updates and email notifications</li>";
echo "</ol>";
echo "</div>";

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Sample Data</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        h4, h5 { color: inherit; margin-bottom: 10px; }
        p { line-height: 1.6; }
        ul, ol { line-height: 1.8; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Content will be inserted here by PHP -->
    </div>
</body>
</html>
