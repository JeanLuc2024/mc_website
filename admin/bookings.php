<?php
/**
 * Admin Bookings Page
 * 
 * This page displays all bookings with filtering and management options.
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page
    header('Location: index.php');
    exit;
}

// Include database configuration
require_once '../php/config.php';

// Set page title for header
$page_title = "Manage Bookings";

// Get database connection
$conn = connectDB();

// Initialize variables
$bookings = [];
$total_bookings = 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_term = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$total_pages = 1;

// Check if database connection was successful
if ($conn) {
    try {
        // Build query based on filters
        $query = "SELECT * FROM bookings WHERE 1=1";
        $count_query = "SELECT COUNT(*) FROM bookings WHERE 1=1";
        
        $params = [];
        
        // Add status filter if not 'all'
        if ($filter_status !== 'all') {
            $query .= " AND status = :status";
            $count_query .= " AND status = :status";
            $params[':status'] = $filter_status;
        }
        
        // Add search term if provided
        if (!empty($search_term)) {
            $query .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR booking_ref LIKE :search)";
            $count_query .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR booking_ref LIKE :search)";
            $params[':search'] = "%$search_term%";
        }
        
        // Get total count for pagination
        $stmt = $conn->prepare($count_query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $total_bookings = $stmt->fetchColumn();
        
        // Calculate pagination
        $total_pages = ceil($total_bookings / $items_per_page);
        $offset = ($current_page - 1) * $items_per_page;
        
        // Add pagination to query
        $query .= " ORDER BY created_at DESC LIMIT :offset, :limit";
        
        // Get bookings
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        error_log("Bookings page error: " . $e->getMessage());
    }
}

// Status update functionality removed - admin now only responds via email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Process delete booking
    if ($_POST['action'] === 'delete_booking' && isset($_POST['booking_id'])) {
        $booking_id = (int)$_POST['booking_id'];

        try {
            $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);

            // Redirect to refresh the page
            header('Location: bookings.php?status=' . $filter_status . '&search=' . urlencode($search_term) . '&page=' . $current_page . '&deleted=1');
            exit;
        } catch(PDOException $e) {
            error_log("Delete booking error: " . $e->getMessage());
        }
    }
}

// Close connection
$conn = null;

// Include header
include_once 'includes/header.php';
?>
                <!-- Notifications removed - admin communicates via email only -->
                
                <!-- Filters and Search -->
                <div class="filters-container">
                    <div class="status-filters">
                        <a href="bookings.php?status=all<?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                           class="filter-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>">
                            All
                        </a>
                        <a href="bookings.php?status=pending<?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                           class="filter-btn <?php echo $filter_status === 'pending' ? 'active' : ''; ?>">
                            Pending
                        </a>
                        <a href="bookings.php?status=confirmed<?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                           class="filter-btn <?php echo $filter_status === 'confirmed' ? 'active' : ''; ?>">
                            Confirmed
                        </a>
                        <a href="bookings.php?status=cancelled<?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                           class="filter-btn <?php echo $filter_status === 'cancelled' ? 'active' : ''; ?>">
                            Cancelled
                        </a>
                    </div>
                    
                    <div class="search-container">
                        <form action="bookings.php" method="GET">
                            <input type="hidden" name="status" value="<?php echo $filter_status; ?>">
                            <div class="search-input">
                                <input type="text" name="search" placeholder="Search bookings..." value="<?php echo htmlspecialchars($search_term); ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Bookings Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>Bookings (<?php echo $total_bookings; ?>)</h2>
                        <div class="export-btn">
                            <a href="export-bookings.php?status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search_term); ?>" class="btn-secondary">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Event Type</th>
                                    <th>Event Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($bookings)): ?>
                                    <tr>
                                        <td colspan="8" class="no-data">No bookings found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['booking_ref']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['event_type']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($booking['event_date'])); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td class="actions-cell">
                                                <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="action-btn view-btn" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="email-client.php?booking_id=<?php echo $booking['id']; ?>" class="action-btn email-btn" title="Email Client">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                                <!-- Status change removed - admin responds via email only -->
                                                <button class="action-btn delete-btn" title="Delete Booking"
                                                        onclick="confirmDelete(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['name']); ?>')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="bookings.php?status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search_term); ?>&page=<?php echo $current_page - 1; ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="bookings.php?status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search_term); ?>&page=<?php echo $i; ?>" 
                                   class="page-link <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="bookings.php?status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search_term); ?>&page=<?php echo $current_page + 1; ?>" class="page-link">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Status modal removed - admin communicates via email only -->
    
    <script>
        // Status modal functionality removed

        // Delete confirmation function
        function confirmDelete(bookingId, clientName) {
            if (confirm(`Are you sure you want to delete the booking for ${clientName}?\n\nThis action cannot be undone.`)) {
                // Create and submit delete form
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_booking';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'booking_id';
                idInput.value = bookingId;

                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Notification functionality removed
        
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const adminContainer = document.querySelector('.admin-container');
        
        sidebarToggle.addEventListener('click', function() {
            adminContainer.classList.toggle('sidebar-collapsed');
        });
    </script>

    <style>
    .delete-btn {
        background: #dc3545 !important;
        color: white !important;
    }

    .delete-btn:hover {
        background: #c82333 !important;
        transform: scale(1.05);
    }

    .form-group textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: inherit;
        resize: vertical;
    }

    .form-group textarea:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
    }
    </style>
</body>
</html>
