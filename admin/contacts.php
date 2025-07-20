<?php
/**
 * Admin Contacts Page
 * 
 * This page displays all contact messages with filtering and management options.
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

// Get database connection
$conn = connectDB();

// Initialize variables
$contacts = [];
$total_contacts = 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_term = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$total_pages = 1;

// Check if database connection was successful
if ($conn) {
    try {
        // Build query based on filters
        $query = "SELECT * FROM contacts WHERE 1=1";
        $count_query = "SELECT COUNT(*) FROM contacts WHERE 1=1";
        
        $params = [];
        
        // Add status filter if not 'all'
        if ($filter_status === 'read') {
            $query .= " AND is_read = 1";
            $count_query .= " AND is_read = 1";
        } elseif ($filter_status === 'unread') {
            $query .= " AND is_read = 0";
            $count_query .= " AND is_read = 0";
        }
        
        // Add search term if provided
        if (!empty($search_term)) {
            $query .= " AND (name LIKE :search OR email LIKE :search OR subject LIKE :search OR message LIKE :search)";
            $count_query .= " AND (name LIKE :search OR email LIKE :search OR subject LIKE :search OR message LIKE :search)";
            $params[':search'] = "%$search_term%";
        }
        
        // Get total count for pagination
        $stmt = $conn->prepare($count_query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $total_contacts = $stmt->fetchColumn();
        
        // Calculate pagination
        $total_pages = ceil($total_contacts / $items_per_page);
        $offset = ($current_page - 1) * $items_per_page;
        
        // Add pagination to query
        $query .= " ORDER BY created_at DESC LIMIT :offset, :limit";
        
        // Get contacts
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt->execute();
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        error_log("Contacts page error: " . $e->getMessage());
    }
}

// Process mark as read/unread if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_read') {
    if (isset($_POST['contact_id'])) {
        $contact_id = (int)$_POST['contact_id'];
        $is_read = isset($_POST['is_read']) ? (int)$_POST['is_read'] : 0;
        $new_status = $is_read ? 0 : 1; // Toggle status
        
        try {
            $stmt = $conn->prepare("UPDATE contacts SET is_read = :is_read, updated_at = NOW() WHERE id = :id");
            $stmt->bindParam(':is_read', $new_status);
            $stmt->bindParam(':id', $contact_id);
            $stmt->execute();
            
            // Redirect to refresh the page
            header('Location: contacts.php?status=' . $filter_status . '&search=' . urlencode($search_term) . '&page=' . $current_page . '&updated=1');
            exit;
        } catch(PDOException $e) {
            error_log("Status update error: " . $e->getMessage());
        }
    }
}

// Close connection
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contacts | Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>ValentinMC</h2>
                <p>Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="bookings.php">
                            <i class="fas fa-calendar-check"></i>
                            <span>Bookings</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="contacts.php">
                            <i class="fas fa-envelope"></i>
                            <span>Contacts</span>
                        </a>
                    </li>
                    <li>
                        <a href="profile.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <p>&copy; 2025 Byiringiro Valentin</p>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="header-left">
                    <button id="sidebar-toggle" class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Manage Contacts</h1>
                </div>
                
                <div class="header-right">
                    <div class="admin-profile">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                        <div class="profile-img">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <!-- Notification for status update -->
                <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
                    <div class="notification success">
                        <i class="fas fa-check-circle"></i>
                        <p>Message status updated successfully!</p>
                        <button class="close-notification"><i class="fas fa-times"></i></button>
                    </div>
                <?php endif; ?>
                
                <!-- Filters and Search -->
                <div class="filters-container">
                    <div class="status-filters">
                        <a href="contacts.php?status=all<?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                           class="filter-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>">
                            All
                        </a>
                        <a href="contacts.php?status=unread<?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                           class="filter-btn <?php echo $filter_status === 'unread' ? 'active' : ''; ?>">
                            Unread
                        </a>
                        <a href="contacts.php?status=read<?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                           class="filter-btn <?php echo $filter_status === 'read' ? 'active' : ''; ?>">
                            Read
                        </a>
                    </div>
                    
                    <div class="search-container">
                        <form action="contacts.php" method="GET">
                            <input type="hidden" name="status" value="<?php echo $filter_status; ?>">
                            <div class="search-input">
                                <input type="text" name="search" placeholder="Search messages..." value="<?php echo htmlspecialchars($search_term); ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Contacts Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>Messages (<?php echo $total_contacts; ?>)</h2>
                        <div class="export-btn">
                            <a href="export-contacts.php?status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search_term); ?>" class="btn-secondary">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($contacts)): ?>
                                    <tr>
                                        <td colspan="6" class="no-data">No messages found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($contacts as $contact): ?>
                                        <tr class="<?php echo $contact['is_read'] ? '' : 'unread-row'; ?>">
                                            <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                            <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                            <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($contact['created_at'])); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $contact['is_read'] ? 'read' : 'unread'; ?>">
                                                    <?php echo $contact['is_read'] ? 'Read' : 'Unread'; ?>
                                                </span>
                                            </td>
                                            <td class="actions-cell">
                                                <a href="contact-details.php?id=<?php echo $contact['id']; ?>" class="action-btn view-btn" title="View Message">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form method="POST" action="contacts.php" style="display: inline;">
                                                    <input type="hidden" name="action" value="toggle_read">
                                                    <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                                    <input type="hidden" name="is_read" value="<?php echo $contact['is_read']; ?>">
                                                    <button type="submit" class="action-btn <?php echo $contact['is_read'] ? 'unread-btn' : 'read-btn'; ?>" 
                                                            title="<?php echo $contact['is_read'] ? 'Mark as Unread' : 'Mark as Read'; ?>">
                                                        <i class="fas <?php echo $contact['is_read'] ? 'fa-envelope' : 'fa-envelope-open'; ?>"></i>
                                                    </button>
                                                </form>
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
                                <a href="contacts.php?status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search_term); ?>&page=<?php echo $current_page - 1; ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="contacts.php?status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search_term); ?>&page=<?php echo $i; ?>" 
                                   class="page-link <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="contacts.php?status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search_term); ?>&page=<?php echo $current_page + 1; ?>" class="page-link">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Close notification
        const closeNotification = document.querySelector('.close-notification');
        if (closeNotification) {
            closeNotification.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
            
            // Auto-hide notification after 5 seconds
            setTimeout(function() {
                const notification = document.querySelector('.notification');
                if (notification) {
                    notification.style.display = 'none';
                }
            }, 5000);
        }
        
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const adminContainer = document.querySelector('.admin-container');
        
        sidebarToggle.addEventListener('click', function() {
            adminContainer.classList.toggle('sidebar-collapsed');
        });
    </script>
</body>
</html>
