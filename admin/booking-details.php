<?php
/**
 * Booking Details Page
 * 
 * This page displays detailed information about a specific booking.
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Include required files
require_once '../php/config.php';
require_once '../php/email_communication.php';

// Set page title for header
$page_title = "Booking Details";

// Get database connection
$conn = connectDB();

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$booking_id) {
    header('Location: bookings.php');
    exit;
}

// Get booking details
try {
    $booking_sql = "SELECT * FROM bookings WHERE id = :booking_id";
    $booking_stmt = $conn->prepare($booking_sql);
    $booking_stmt->bindParam(':booking_id', $booking_id);
    $booking_stmt->execute();
    $booking = $booking_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        header('Location: bookings.php');
        exit;
    }
} catch (Exception $e) {
    $error_message = "Error loading booking details: " . $e->getMessage();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitizeInput($_POST['status']);
    
    if (in_array($new_status, ['pending', 'confirmed', 'cancelled'])) {
        try {
            $update_sql = "UPDATE bookings SET status = :status, updated_at = NOW() WHERE id = :id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':status', $new_status);
            $update_stmt->bindParam(':id', $booking_id);
            
            if ($update_stmt->execute()) {
                $booking['status'] = $new_status; // Update local variable
                $success_message = "Booking status updated successfully.";
                
                // Log activity
                error_log("Booking status updated: {$booking['booking_ref']} to {$new_status} by admin {$_SESSION['admin_username']}");
            } else {
                $error_message = "Failed to update booking status.";
            }
        } catch (Exception $e) {
            $error_message = "Error updating status: " . $e->getMessage();
        }
    } else {
        $error_message = "Invalid status selected.";
    }
}

// Get email history for this booking
$email_history = getEmailHistory($booking_id);

// Include header
include_once 'includes/header.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-calendar-check"></i> Booking Details</h1>
        <div class="breadcrumb">
            <a href="bookings.php">Bookings</a> > Booking Details
        </div>
        <div class="header-actions">
            <a href="email-client.php?booking_id=<?php echo $booking_id; ?>" class="btn-primary">
                <i class="fas fa-envelope"></i> Email Client
            </a>
            <a href="bookings.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="notification success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success_message; ?></span>
            <button class="close-notification">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="notification error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error_message; ?></span>
            <button class="close-notification">&times;</button>
        </div>
    <?php endif; ?>

    <div class="booking-details-container">
        <!-- Main Booking Information -->
        <div class="booking-info-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Booking Information</h3>
                <div class="status-container">
                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                    <button class="btn-secondary btn-sm" onclick="openStatusModal()">
                        <i class="fas fa-edit"></i> Change Status
                    </button>
                </div>
            </div>
            
            <div class="card-content">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Booking Reference</label>
                        <value><?php echo htmlspecialchars($booking['booking_ref']); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Created Date</label>
                        <value><?php echo date('M j, Y g:i A', strtotime($booking['created_at'])); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Last Updated</label>
                        <value><?php echo date('M j, Y g:i A', strtotime($booking['updated_at'])); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Event Type</label>
                        <value><?php echo htmlspecialchars($booking['event_type']); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Event Date</label>
                        <value><?php echo formatDate($booking['event_date']); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Event Time</label>
                        <value><?php echo formatTime($booking['event_time']); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Event Location</label>
                        <value><?php echo htmlspecialchars($booking['event_location']); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Number of Guests</label>
                        <value><?php echo htmlspecialchars($booking['guests']); ?></value>
                    </div>
                    <?php if ($booking['package']): ?>
                    <div class="info-item">
                        <label>Package</label>
                        <value><?php echo htmlspecialchars($booking['package']); ?></value>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($booking['message']): ?>
                <div class="message-section">
                    <label>Client Message</label>
                    <div class="message-content">
                        <?php echo nl2br(htmlspecialchars($booking['message'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Client Information -->
        <div class="client-info-card">
            <div class="card-header">
                <h3><i class="fas fa-user"></i> Client Information</h3>
                <div class="contact-actions">
                    <a href="mailto:<?php echo htmlspecialchars($booking['email']); ?>" class="btn-link">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                    <a href="tel:<?php echo htmlspecialchars($booking['phone']); ?>" class="btn-link">
                        <i class="fas fa-phone"></i> Call
                    </a>
                </div>
            </div>
            
            <div class="card-content">
                <div class="client-details">
                    <div class="detail-item">
                        <i class="fas fa-user"></i>
                        <div>
                            <label>Full Name</label>
                            <value><?php echo htmlspecialchars($booking['name']); ?></value>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <label>Email Address</label>
                            <value><?php echo htmlspecialchars($booking['email']); ?></value>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <label>Phone Number</label>
                            <value><?php echo htmlspecialchars($booking['phone']); ?></value>
                        </div>
                    </div>
                </div>
                
                <div class="email-summary">
                    <h4>Email Communication</h4>
                    <div class="email-stats">
                        <div class="stat">
                            <span class="number"><?php echo count($email_history); ?></span>
                            <span class="label">Emails Sent</span>
                        </div>
                        <div class="stat">
                            <span class="number"><?php echo $booking['last_email_sent'] ? date('M j', strtotime($booking['last_email_sent'])) : 'Never'; ?></span>
                            <span class="label">Last Email</span>
                        </div>
                    </div>
                    <a href="email-client.php?booking_id=<?php echo $booking_id; ?>" class="btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Send Email to Client
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Email History -->
    <?php if (!empty($email_history)): ?>
        <div class="email-history-card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Email History</h3>
                <span class="email-count"><?php echo count($email_history); ?> emails</span>
            </div>
            
            <div class="card-content">
                <div class="email-timeline">
                    <?php foreach ($email_history as $email): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="email-header">
                                    <h4><?php echo htmlspecialchars($email['subject']); ?></h4>
                                    <div class="email-meta">
                                        <span class="email-type"><?php echo ucwords(str_replace('_', ' ', $email['email_type'])); ?></span>
                                        <span class="email-date"><?php echo date('M j, Y g:i A', strtotime($email['sent_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="email-preview">
                                    <?php echo nl2br(htmlspecialchars(substr($email['message'], 0, 150))); ?>
                                    <?php if (strlen($email['message']) > 150): ?>
                                        <span class="read-more">... <a href="#" onclick="toggleEmailContent(<?php echo $email['id']; ?>)">Read more</a></span>
                                    <?php endif; ?>
                                </div>
                                <div class="email-full-content" id="email-content-<?php echo $email['id']; ?>" style="display: none;">
                                    <?php echo nl2br(htmlspecialchars($email['message'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Booking Status</h3>
            <span class="close-modal">&times;</span>
        </div>
        <form method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label>Select New Status:</label>
                    <select name="status" class="form-control" required>
                        <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeStatusModal()">Cancel</button>
                <button type="submit" name="update_status" class="btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<style>
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.breadcrumb {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 10px;
}

.breadcrumb a {
    color: #3498db;
    text-decoration: none;
}

.booking-details-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.booking-info-card, .client-info-card, .email-history-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 16px;
}

.card-content {
    padding: 20px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 5px;
}

.info-item value {
    font-size: 14px;
    color: #2c3e50;
    font-weight: 500;
}

.message-section {
    border-top: 1px solid #e9ecef;
    padding-top: 20px;
}

.message-section label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 10px;
    display: block;
}

.message-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #3498db;
    font-size: 14px;
    line-height: 1.5;
}

.status-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.contact-actions {
    display: flex;
    gap: 10px;
}

.client-details {
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
}

.detail-item i {
    color: #3498db;
    width: 20px;
    text-align: center;
}

.detail-item div {
    flex: 1;
}

.detail-item label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 2px;
    display: block;
}

.detail-item value {
    font-size: 14px;
    color: #2c3e50;
    font-weight: 500;
}

.email-summary {
    border-top: 1px solid #e9ecef;
    padding-top: 20px;
}

.email-summary h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 14px;
}

.email-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.stat {
    text-align: center;
}

.stat .number {
    display: block;
    font-size: 18px;
    font-weight: 600;
    color: #3498db;
}

.stat .label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
}

.btn-block {
    width: 100%;
    text-align: center;
}

.email-history-card {
    grid-column: 1 / -1;
}

.email-timeline {
    position: relative;
}

.timeline-item {
    display: flex;
    margin-bottom: 20px;
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    bottom: -20px;
    width: 2px;
    background: #e9ecef;
}

.timeline-marker {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: #3498db;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 15px;
}

.timeline-content {
    flex: 1;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.email-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.email-header h4 {
    margin: 0;
    font-size: 14px;
    color: #2c3e50;
}

.email-meta {
    text-align: right;
    font-size: 12px;
}

.email-type {
    background: #e9ecef;
    color: #495057;
    padding: 2px 8px;
    border-radius: 12px;
    margin-right: 10px;
}

.email-date {
    color: #6c757d;
}

.email-preview {
    font-size: 13px;
    color: #495057;
    line-height: 1.4;
}

.read-more a {
    color: #3498db;
    text-decoration: none;
}

@media (max-width: 768px) {
    .booking-details-container {
        grid-template-columns: 1fr;
    }

    .content-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
        justify-content: flex-start;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Modal functionality
const modal = document.getElementById('statusModal');
const closeBtn = document.querySelector('.close-modal');

function openStatusModal() {
    modal.style.display = 'block';
}

function closeStatusModal() {
    modal.style.display = 'none';
}

closeBtn.addEventListener('click', closeStatusModal);

window.addEventListener('click', function(event) {
    if (event.target === modal) {
        closeStatusModal();
    }
});

// Toggle email content
function toggleEmailContent(emailId) {
    const content = document.getElementById('email-content-' + emailId);
    if (content.style.display === 'none') {
        content.style.display = 'block';
    } else {
        content.style.display = 'none';
    }
}

// Close notifications
document.querySelectorAll('.close-notification').forEach(button => {
    button.addEventListener('click', function() {
        this.parentElement.style.display = 'none';
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>
