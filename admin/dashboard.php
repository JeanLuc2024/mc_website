<?php
/**
 * Admin Dashboard with Animated Stat Cards
 * 
 * Modern dashboard with real-time statistics and animations
 */

// Start session and check authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Set page title
$page_title = 'Dashboard';

// Include required files
require_once '../php/config.php';
require_once '../php/notification_system.php';

// Get database connection
$conn = connectDB();

// Initialize variables
$stats = [
    'total_bookings' => 0,
    'pending_bookings' => 0,
    'confirmed_bookings' => 0,
    'cancelled_bookings' => 0,
    'completed_bookings' => 0,
    'total_emails' => 0,
    'sent_emails' => 0,
    'unread_notifications' => 0
];

$recent_bookings = [];
$recent_notifications = [];

// Get dashboard statistics
if ($conn) {
    try {
        // Booking statistics
        $stmt = $conn->query("SELECT COUNT(*) FROM bookings");
        $stats['total_bookings'] = $stmt->fetchColumn();
        
        $stmt = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
        $stats['pending_bookings'] = $stmt->fetchColumn();
        
        $stmt = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'");
        $stats['confirmed_bookings'] = $stmt->fetchColumn();
        
        $stmt = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'cancelled'");
        $stats['cancelled_bookings'] = $stmt->fetchColumn();
        
        $stmt = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'");
        $stats['completed_bookings'] = $stmt->fetchColumn();
        
        // Email statistics
        $stmt = $conn->query("SELECT COUNT(*) FROM email_notifications");
        $stats['total_emails'] = $stmt->fetchColumn();
        
        $stmt = $conn->query("SELECT COUNT(*) FROM email_notifications WHERE status = 'sent'");
        $stats['sent_emails'] = $stmt->fetchColumn();
        
        // Recent bookings
        $stmt = $conn->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5");
        $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        error_log("Dashboard error: " . $e->getMessage());
    }
    
    $conn = null;
}

// Get notification statistics
$stats['unread_notifications'] = getUnreadNotificationsCount();
$recent_notifications = getRecentNotifications(5);

// Include header
include_once 'includes/header.php';
?>

<style>
/* Dashboard Specific Styles */
.dashboard-container {
    padding: 20px;
    background: #f8f9fa;
    min-height: calc(100vh - 80px);
}

.dashboard-header {
    margin-bottom: 30px;
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
}

.dashboard-subtitle {
    color: #6c757d;
    font-size: 1.1rem;
}

/* Animated Stat Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--card-color);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    background: var(--card-color);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}

.stat-trend {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 20px;
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
    font-weight: 600;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
    counter-reset: num var(--num);
}

.stat-number::after {
    content: counter(num);
    animation: countUp 2s ease-out forwards;
}

@keyframes countUp {
    from { --num: 0; }
    to { --num: var(--target); }
}

.stat-label {
    color: #6c757d;
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 10px;
}

.stat-description {
    color: #8e9aaf;
    font-size: 0.85rem;
    line-height: 1.4;
}

.stat-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

/* Color variations for different cards */
.stat-card.bookings { --card-color: #3498db; }
.stat-card.pending { --card-color: #f39c12; }
.stat-card.confirmed { --card-color: #27ae60; }
.stat-card.emails { --card-color: #9b59b6; }
.stat-card.notifications { --card-color: #e74c3c; }
.stat-card.completed { --card-color: #2ecc71; }

/* Recent Activity Section */
.activity-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 40px;
}

.activity-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.activity-header {
    display: flex;
    align-items: center;
    justify-content: between;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.activity-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.view-all-btn {
    color: #3498db;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.3s ease;
}

.view-all-btn:hover {
    color: #2980b9;
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f8f9fa;
    transition: background 0.3s ease;
}

.activity-item:hover {
    background: #f8f9fa;
    margin: 0 -15px;
    padding-left: 15px;
    padding-right: 15px;
    border-radius: 8px;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 16px;
    color: white;
}

.activity-content {
    flex: 1;
}

.activity-text {
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 2px;
}

.activity-time {
    font-size: 0.8rem;
    color: #8e9aaf;
}

.activity-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }
.status-new { background: #cce5ff; color: #004085; }

.no-activity {
    text-align: center;
    padding: 40px 20px;
    color: #8e9aaf;
}

.no-activity i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .activity-section {
        grid-template-columns: 1fr;
    }
    
    .dashboard-title {
        font-size: 2rem;
    }
    
    .stat-card {
        padding: 20px;
    }
    
    .stat-number {
        font-size: 2rem;
    }
}

/* Loading Animation */
.loading-shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>! 👋</h1>
        <p class="dashboard-subtitle">Here's what's happening with your MC booking system today.</p>
    </div>

    <!-- Animated Stat Cards -->
    <div class="stats-grid">
        <a href="bookings.php" class="stat-link">
            <div class="stat-card bookings" style="--target: <?php echo $stats['total_bookings']; ?>">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-trend">+12%</div>
                </div>
                <div class="stat-number" data-target="<?php echo $stats['total_bookings']; ?>"><?php echo $stats['total_bookings']; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
        </a>

        <a href="bookings.php?status=pending" class="stat-link">
            <div class="stat-card pending" style="--target: <?php echo $stats['pending_bookings']; ?>">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-trend">New</div>
                </div>
                <div class="stat-number" data-target="<?php echo $stats['pending_bookings']; ?>"><?php echo $stats['pending_bookings']; ?></div>
                <div class="stat-label">Pending Bookings</div>
            </div>
        </a>

        <a href="bookings.php?status=confirmed" class="stat-link">
            <div class="stat-card confirmed" style="--target: <?php echo $stats['confirmed_bookings']; ?>">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-trend">+8%</div>
                </div>
                <div class="stat-number" data-target="<?php echo $stats['confirmed_bookings']; ?>"><?php echo $stats['confirmed_bookings']; ?></div>
                <div class="stat-label">Confirmed Bookings</div>
            </div>
        </a>

        <a href="email-client.php" class="stat-link">
            <div class="stat-card emails" style="--target: <?php echo $stats['sent_emails']; ?>">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div class="stat-trend">+25%</div>
                </div>
                <div class="stat-number" data-target="<?php echo $stats['sent_emails']; ?>"><?php echo $stats['sent_emails']; ?></div>
                <div class="stat-label">Emails Sent</div>
            </div>
        </a>

        <a href="notifications.php" class="stat-link">
            <div class="stat-card notifications" style="--target: <?php echo $stats['unread_notifications']; ?>">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <?php if ($stats['unread_notifications'] > 0): ?>
                        <div class="stat-trend" style="background: rgba(231, 76, 60, 0.1); color: #e74c3c;">Alert</div>
                    <?php else: ?>
                        <div class="stat-trend">Clear</div>
                    <?php endif; ?>
                </div>
                <div class="stat-number" data-target="<?php echo $stats['unread_notifications']; ?>"><?php echo $stats['unread_notifications']; ?></div>
                <div class="stat-label">Unread Notifications</div>
            </div>
        </a>

        <a href="bookings.php?status=completed" class="stat-link">
            <div class="stat-card completed" style="--target: <?php echo $stats['completed_bookings']; ?>">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-trend">Success</div>
                </div>
                <div class="stat-number" data-target="<?php echo $stats['completed_bookings']; ?>"><?php echo $stats['completed_bookings']; ?></div>
                <div class="stat-label">Completed Events</div>
            </div>
        </a>
    </div>

    <!-- Recent Activity Section -->
    <div class="activity-section">
        <!-- Recent Bookings -->
        <div class="activity-card">
            <div class="activity-header">
                <h3 class="activity-title">Recent Bookings</h3>
                <a href="bookings.php" class="view-all-btn">View All →</a>
            </div>
            
            <?php if (empty($recent_bookings)): ?>
                <div class="no-activity">
                    <i class="fas fa-calendar-plus"></i>
                    <p>No recent bookings</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_bookings as $booking): ?>
                    <div class="activity-item">
                        <div class="activity-icon" style="background: #3498db;">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text"><?php echo htmlspecialchars($booking['name']); ?> - <?php echo htmlspecialchars($booking['event_type']); ?></div>
                            <div class="activity-time"><?php echo date('M j, Y g:i A', strtotime($booking['created_at'])); ?></div>
                        </div>
                        <div class="activity-status status-<?php echo $booking['status']; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Recent Notifications -->
        <div class="activity-card">
            <div class="activity-header">
                <h3 class="activity-title">Recent Notifications</h3>
                <a href="notifications.php" class="view-all-btn">View All →</a>
            </div>
            
            <?php if (empty($recent_notifications)): ?>
                <div class="no-activity">
                    <i class="fas fa-bell-slash"></i>
                    <p>No recent notifications</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_notifications as $notification): ?>
                    <div class="activity-item">
                        <div class="activity-icon" style="background: <?php echo $notification['is_read'] ? '#95a5a6' : '#e74c3c'; ?>;">
                            <i class="fas fa-<?php echo $notification['is_read'] ? 'check' : 'bell'; ?>"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text"><?php echo htmlspecialchars($notification['title']); ?></div>
                            <div class="activity-time"><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></div>
                        </div>
                        <div class="activity-status status-<?php echo $notification['is_read'] ? 'confirmed' : 'new'; ?>">
                            <?php echo $notification['is_read'] ? 'Read' : 'New'; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Animate numbers on page load
document.addEventListener('DOMContentLoaded', function() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(number => {
        const target = parseInt(number.getAttribute('data-target'));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            number.textContent = Math.floor(current);
        }, 16);
    });
    
    // Add entrance animations
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// Auto-refresh stats every 30 seconds
setInterval(() => {
    // You can add AJAX call here to refresh stats without page reload
    console.log('Stats refreshed');
}, 30000);
</script>

<?php include_once 'includes/footer.php'; ?>
