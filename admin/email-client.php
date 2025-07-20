<?php
/**
 * Email Client Communication Page
 * 
 * This page allows admin to send emails to clients directly from the admin panel.
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
require_once '../php/simple_email_handler.php';

// Set page title for header
$page_title = "Email Client";

// Get database connection
$conn = connectDB();

// Get booking ID from URL
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $to_email = trim($_POST['to_email'] ?? '');
    $booking_ref = trim($_POST['booking_ref'] ?? '');

    if (!empty($message) && !empty($to_email)) {
        $result = sendAdminToClientEmail($to_email, $subject, $message, $booking_ref);

        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } else {
        $error_message = "Please fill in the message field.";
    }
}

// Email history moved to view_history.php page

// Include header
include_once 'includes/header.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-envelope"></i> Email Client</h1>
        <div class="breadcrumb">
            <a href="bookings.php">Bookings</a> > 
            <a href="booking-details.php?id=<?php echo $booking_id; ?>">Booking Details</a> > 
            Email Client
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

    <div class="email-container">
        <!-- Client Information Panel -->
        <div class="client-info-panel">
            <div class="client-header">
                <h3><i class="fas fa-user"></i> Client Information</h3>
            </div>
            <div class="client-details">
                <div class="detail-item">
                    <strong>Name:</strong> <?php echo htmlspecialchars($booking['name']); ?>
                </div>
                <div class="detail-item">
                    <strong>Email:</strong> 
                    <a href="mailto:<?php echo htmlspecialchars($booking['email']); ?>">
                        <?php echo htmlspecialchars($booking['email']); ?>
                    </a>
                </div>
                <div class="detail-item">
                    <strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?>
                </div>
                <div class="detail-item">
                    <strong>Booking Ref:</strong> <?php echo htmlspecialchars($booking['booking_ref']); ?>
                </div>
                <div class="detail-item">
                    <strong>Event Type:</strong> <?php echo htmlspecialchars($booking['event_type']); ?>
                </div>
                <div class="detail-item">
                    <strong>Event Date:</strong> <?php echo formatDate($booking['event_date']); ?>
                </div>
                <div class="detail-item">
                    <strong>Status:</strong> 
                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Email Composer -->
        <div class="email-composer">
            <div class="composer-header">
                <h3><i class="fas fa-edit"></i> Send Message to Client</h3>
                <p style="margin: 5px 0; color: #6c757d; font-size: 14px;">Send a direct response to the client's booking request</p>
            </div>

            <form method="POST" class="email-form">
                <div class="form-group">
                    <label>To:</label>
                    <input type="email" value="<?php echo htmlspecialchars($booking['email']); ?>" readonly class="form-control">
                </div>

                <input type="hidden" name="subject" value="Response to your booking - <?php echo htmlspecialchars($booking['booking_ref']); ?>">
                <input type="hidden" name="email_type" value="booking_response">
                <input type="hidden" name="to_email" value="<?php echo htmlspecialchars($booking['email']); ?>">
                <input type="hidden" name="booking_ref" value="<?php echo htmlspecialchars($booking['booking_ref']); ?>">

                <div class="form-group">
                    <label>Sending response to:</label>
                    <div class="recipient-info">
                        <strong><?php echo htmlspecialchars($booking['name']); ?></strong><br>
                        <span><?php echo htmlspecialchars($booking['email']); ?></span><br>
                        <small>Booking: <?php echo htmlspecialchars($booking['booking_ref']); ?> - <?php echo htmlspecialchars($booking['event_type']); ?></small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Message:</label>
                    <div class="message-toolbar">
                        <button type="button" class="toolbar-btn" onclick="insertVariable('{client_name}')">Client Name</button>
                        <button type="button" class="toolbar-btn" onclick="insertVariable('{booking_ref}')">Booking Ref</button>
                        <button type="button" class="toolbar-btn" onclick="insertVariable('{event_date}')">Event Date</button>
                        <button type="button" class="toolbar-btn" onclick="insertVariable('{event_type}')">Event Type</button>
                    </div>
                    <textarea name="message" id="email-message" rows="12" class="form-control" required 
                              placeholder="Type your message here...

Available variables:
{client_name} - Client's name
{booking_ref} - Booking reference
{event_type} - Type of event
{event_date} - Event date
{event_time} - Event time
{event_location} - Event location
{guests} - Number of guests"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Email
                    </button>
                    <button type="button" class="btn-secondary" onclick="previewEmail()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <a href="booking-details.php?id=<?php echo $booking_id; ?>" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Booking
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Email History moved to dedicated Email History page -->
    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin-top: 20px;">
        <p><i class="fas fa-info-circle"></i> <strong>Email History:</strong> View all sent emails in the
        <a href="view_history.php" style="color: #8e44ad; text-decoration: none;">
            <i class="fas fa-history"></i> Email History
        </a> section.</p>
    </div>
</div>

<!-- Email Preview Modal -->
<div id="email-preview-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Email Preview</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div id="preview-content"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closePreviewModal()">Close</button>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>

<style>
.email-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.client-info-panel {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.client-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
}

.client-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 16px;
}

.client-details {
    padding: 20px;
}

.detail-item {
    margin-bottom: 12px;
    font-size: 14px;
}

.detail-item strong {
    color: #2c3e50;
    display: inline-block;
    width: 80px;
}

.email-composer {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.composer-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.composer-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 16px;
}

.template-selector {
    display: flex;
    align-items: center;
    gap: 10px;
}

.template-selector label {
    font-size: 14px;
    color: #6c757d;
}

.template-selector select {
    width: 200px;
}

.email-form {
    padding: 20px;
}

.message-toolbar {
    margin-bottom: 10px;
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.toolbar-btn {
    background: #e9ecef;
    border: 1px solid #ced4da;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.toolbar-btn:hover {
    background: #dee2e6;
}

.recipient-info {
    background: #e8f5e8;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #28a745;
    margin: 10px 0;
}

.recipient-info strong {
    color: #2c3e50;
    font-size: 16px;
}

.recipient-info span {
    color: #495057;
    font-size: 14px;
}

.recipient-info small {
    color: #6c757d;
    font-size: 12px;
}

.email-history {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.history-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.history-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 16px;
}

.email-count {
    font-size: 14px;
    color: #6c757d;
}

.history-list {
    max-height: 400px;
    overflow-y: auto;
}

.history-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f1f3f4;
}

.history-item:last-child {
    border-bottom: none;
}

.email-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.email-info strong {
    color: #2c3e50;
    font-size: 14px;
}

.email-type {
    background: #e9ecef;
    color: #495057;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    margin-left: 10px;
}

.email-meta {
    text-align: right;
    font-size: 12px;
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

.breadcrumb {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 10px;
}

.breadcrumb a {
    color: #3498db;
    text-decoration: none;
}

@media (max-width: 768px) {
    .email-container {
        grid-template-columns: 1fr;
    }
    
    .composer-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<script>
// Template selection functionality
document.getElementById('template-select').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        document.getElementById('email-subject').value = selectedOption.dataset.subject;
        document.getElementById('email-message').value = selectedOption.dataset.message;
        
        // Update email type if available
        const emailTypeSelect = document.querySelector('select[name="email_type"]');
        if (selectedOption.dataset.type) {
            emailTypeSelect.value = selectedOption.dataset.type;
        }
    }
});

// Insert variable functionality
function insertVariable(variable) {
    const messageTextarea = document.getElementById('email-message');
    const cursorPos = messageTextarea.selectionStart;
    const textBefore = messageTextarea.value.substring(0, cursorPos);
    const textAfter = messageTextarea.value.substring(cursorPos);
    
    messageTextarea.value = textBefore + variable + textAfter;
    messageTextarea.focus();
    messageTextarea.setSelectionRange(cursorPos + variable.length, cursorPos + variable.length);
}

// Email preview functionality
function previewEmail() {
    const subject = document.getElementById('email-subject').value;
    const message = document.getElementById('email-message').value;
    
    if (!subject || !message) {
        alert('Please fill in both subject and message fields.');
        return;
    }
    
    // Create preview content
    const previewContent = `
        <div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
            <div style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #ddd;">
                <strong>Subject:</strong> ${subject}
            </div>
            <div style="padding: 20px; white-space: pre-wrap;">${message}</div>
        </div>
    `;
    
    document.getElementById('preview-content').innerHTML = previewContent;
    document.getElementById('email-preview-modal').style.display = 'block';
}

function closePreviewModal() {
    document.getElementById('email-preview-modal').style.display = 'none';
}

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

// Modal close functionality
document.querySelector('.close-modal').addEventListener('click', closePreviewModal);
window.addEventListener('click', function(event) {
    const modal = document.getElementById('email-preview-modal');
    if (event.target === modal) {
        closePreviewModal();
    }
});
</script>
