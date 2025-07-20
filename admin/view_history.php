<?php
/**
 * View Email History Page
 * 
 * This page displays all admin responses sent to clients.
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
$page_title = "Email History";

// Get database connection
$conn = connectDB();

// Initialize variables
$email_history = [];
$search_term = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 15;
$total_pages = 1;

// Check if database connection was successful
if ($conn) {
    try {
        // Build query for completed bookings history
        $query = "SELECT bh.*, 'completed_booking' as record_type, bh.moved_to_history_at as sent_at,
                         'Booking Completed' as subject, 'Admin responded to this booking' as message,
                         'custom_message' as email_type, bh.name as client_name
                  FROM booking_history bh
                  WHERE 1=1";
        $count_query = "SELECT COUNT(*) FROM booking_history bh WHERE 1=1";
        
        $params = [];
        
        // Add search filter if provided
        if (!empty($search_term)) {
            $query .= " AND (bh.name LIKE :search OR bh.booking_ref LIKE :search OR bh.event_type LIKE :search)";
            $count_query .= " AND (bh.name LIKE :search OR bh.booking_ref LIKE :search OR bh.event_type LIKE :search)";
            $params[':search'] = '%' . $search_term . '%';
        }
        
        // Get total count for pagination
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->execute($params);
        $total_emails = $count_stmt->fetchColumn();
        $total_pages = ceil($total_emails / $items_per_page);
        
        // Add pagination
        $offset = ($current_page - 1) * $items_per_page;
        $query .= " ORDER BY bh.moved_to_history_at DESC LIMIT :limit OFFSET :offset";
        
        // Execute main query
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $email_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        error_log("Email history error: " . $e->getMessage());
    }
}

// Close database connection
$conn = null;

// Include header
include_once 'includes/header.php';
?>

<style>
/* Email History Specific Styles */
.history-container {
    padding: 20px;
    background: #f8f9fa;
    min-height: calc(100vh - 80px);
}

.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow-x: auto;
    margin-top: 20px;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.history-table th {
    background: #8e44ad;
    color: white;
    padding: 12px 8px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #7d3c98;
}

.history-table td {
    padding: 12px 8px;
    border-bottom: 1px solid #eee;
    vertical-align: top;
}

.history-table tr:hover {
    background: #f8f9fa;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.completed {
    background: #d4edda;
    color: #155724;
}

.message-preview, .response-preview {
    max-width: 200px;
    word-wrap: break-word;
    font-size: 13px;
    line-height: 1.4;
}

.read-more {
    color: #8e44ad;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-direction: column;
    align-items: center;
}

.btn-action {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-email {
    background: #3498db;
    color: white;
}

.btn-email:hover {
    background: #2980b9;
    color: white;
}

.btn-delete {
    background: #e74c3c;
    color: white;
}

.btn-delete:hover {
    background: #c0392b;
}

.admin-response {
    max-width: 250px;
}

.history-header {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.history-header h1 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.history-stats {
    display: flex;
    gap: 20px;
    margin-top: 15px;
}

.stat-item {
    background: #8e44ad;
    color: white;
    padding: 10px 15px;
    border-radius: 6px;
    text-align: center;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    display: block;
}

.search-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.search-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.search-btn {
    background: #8e44ad;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
}

.search-btn:hover {
    background: #7d3c98;
}

.email-list {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.email-item {
    border-bottom: 1px solid #eee;
    padding: 20px;
    transition: background-color 0.3s;
}

.email-item:hover {
    background-color: #f8f9fa;
}

.email-item:last-child {
    border-bottom: none;
}

.email-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.email-info h3 {
    color: #2c3e50;
    margin: 0 0 5px 0;
    font-size: 1.1rem;
}

.client-info {
    color: #6c757d;
    font-size: 0.9rem;
}

.email-meta {
    text-align: right;
    color: #6c757d;
    font-size: 0.85rem;
}

.email-type {
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    margin-left: 10px;
}

.email-preview {
    color: #495057;
    line-height: 1.5;
    margin-top: 10px;
}

.email-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
}

.action-link {
    color: #8e44ad;
    text-decoration: none;
    font-size: 0.9rem;
    padding: 5px 10px;
    border: 1px solid #8e44ad;
    border-radius: 4px;
    transition: all 0.3s;
}

.action-link:hover {
    background: #8e44ad;
    color: white;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    gap: 5px;
}

.page-link {
    padding: 8px 12px;
    background: white;
    border: 1px solid #ddd;
    color: #8e44ad;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s;
}

.page-link:hover,
.page-link.active {
    background: #8e44ad;
    color: white;
    border-color: #8e44ad;
}

.no-emails {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

.no-emails i {
    font-size: 3rem;
    margin-bottom: 15px;
    color: #dee2e6;
}
</style>

<div class="main-content">
    <div class="history-container">
        <!-- Header -->
        <div class="history-header">
            <h1><i class="fas fa-history"></i> Completed Bookings History</h1>
            <p>View all bookings that have been responded to and completed</p>
            
            <div class="history-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($email_history); ?></span>
                    <span>This Page</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $total_emails ?? 0; ?></span>
                    <span>Total Emails</span>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="search-container">
            <form method="GET" class="search-form">
                <input type="text" name="search" class="search-input" 
                       placeholder="Search by client name, booking reference, subject, or message content..." 
                       value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if (!empty($search_term)): ?>
                    <a href="view_history.php" class="search-btn" style="background: #6c757d;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- History Table -->
        <div class="table-container">
            <?php if (!empty($email_history)): ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>Client Info</th>
                            <th>Event Details</th>
                            <th>Completed Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($email_history as $email): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($email['booking_ref']); ?></strong>
                                    <br><small class="status-badge completed">Completed</small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($email['client_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($email['email']); ?></small><br>
                                    <small><?php echo htmlspecialchars($email['phone']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($email['event_type']); ?></strong><br>
                                    <small><?php echo date('M j, Y', strtotime($email['event_date'])); ?> at <?php echo date('g:i A', strtotime($email['event_time'])); ?></small><br>
                                    <small><?php echo htmlspecialchars($email['event_location']); ?></small><br>
                                    <small><?php echo $email['guests']; ?> guests | <?php echo htmlspecialchars($email['package']); ?></small>
                                </td>

                                <td>
                                    <?php echo date('M j, Y', strtotime($email['sent_at'])); ?><br>
                                    <small><?php echo date('g:i A', strtotime($email['sent_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="deleteFromHistory('<?php echo $email['booking_ref']; ?>')" class="btn-action btn-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-emails">
                    <i class="fas fa-inbox"></i>
                    <h3>No Completed Bookings Found</h3>
                    <p>
                        <?php if (!empty($search_term)): ?>
                            No completed bookings found matching your search criteria.
                        <?php else: ?>
                            No bookings have been completed yet. When you respond to a client via email, their booking will automatically move here.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="view_history.php?search=<?php echo urlencode($search_term); ?>&page=<?php echo $current_page - 1; ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="view_history.php?search=<?php echo urlencode($search_term); ?>&page=<?php echo $i; ?>" 
                       class="page-link <?php echo $i === $current_page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="view_history.php?search=<?php echo urlencode($search_term); ?>&page=<?php echo $current_page + 1; ?>" class="page-link">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteFromHistory(bookingRef) {
    if (confirm('Are you sure you want to delete this booking from history? This action cannot be undone.')) {
        fetch('delete_from_history.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'booking_ref=' + encodeURIComponent(bookingRef)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Booking deleted from history successfully.');
                location.reload();
            } else {
                alert('Error deleting booking: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error deleting booking: ' + error.message);
        });
    }
}
</script>

<?php include_once 'includes/footer.php'; ?>
