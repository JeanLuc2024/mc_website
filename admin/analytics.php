<?php
/**
 * Admin Analytics Page
 * 
 * This page displays detailed analytics and statistics for bookings and contacts.
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
$page_title = "Analytics & Statistics";

// Initialize variables
$total_bookings = 0;
$bookings_by_status = [];
$bookings_by_month = [];
$bookings_by_event_type = [];
$contacts_by_month = [];

// Get database connection
$conn = connectDB();

// Check if database connection was successful
if ($conn) {
    try {
        // Get total bookings count
        $stmt = $conn->query("SELECT COUNT(*) FROM bookings");
        $total_bookings = $stmt->fetchColumn();
        
        // Get bookings by status
        $stmt = $conn->query("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
        $bookings_by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get bookings by month (last 6 months)
        $stmt = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                            FROM bookings 
                            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                            GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                            ORDER BY month ASC");
        $bookings_by_month = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get bookings by event type
        $stmt = $conn->query("SELECT event_type, COUNT(*) as count FROM bookings GROUP BY event_type ORDER BY count DESC");
        $bookings_by_event_type = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get contacts by month (last 6 months)
        $stmt = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                            FROM contacts 
                            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                            GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                            ORDER BY month ASC");
        $contacts_by_month = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        error_log("Analytics page error: " . $e->getMessage());
    }
    
    // Close connection
    $conn = null;
}

// Include header
include_once 'includes/header.php';
?>

<div class="content-body">
    <div class="page-header">
        <h2><i class="fas fa-chart-line"></i> Analytics & Statistics</h2>
        <p>Detailed insights about your bookings and contacts</p>
    </div>
    
    <!-- Analytics Overview -->
    <div class="analytics-overview">
        <div class="analytics-card">
            <div class="analytics-header">
                <h3>Booking Status Distribution</h3>
            </div>
            <div class="analytics-body">
                <canvas id="bookingStatusChart"></canvas>
            </div>
        </div>
        
        <div class="analytics-card">
            <div class="analytics-header">
                <h3>Event Types Distribution</h3>
            </div>
            <div class="analytics-body">
                <canvas id="eventTypeChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Trend Analysis -->
    <div class="analytics-overview">
        <div class="analytics-card full-width">
            <div class="analytics-header">
                <h3>Booking Trends (Last 6 Months)</h3>
            </div>
            <div class="analytics-body">
                <canvas id="bookingTrendsChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="analytics-overview">
        <div class="analytics-card full-width">
            <div class="analytics-header">
                <h3>Contact Message Trends (Last 6 Months)</h3>
            </div>
            <div class="analytics-body">
                <canvas id="contactTrendsChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Key Metrics -->
    <div class="analytics-metrics">
        <h3>Key Performance Metrics</h3>
        <div class="metrics-container">
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="metric-info">
                    <h4>Confirmation Rate</h4>
                    <?php
                    $confirmation_rate = 0;
                    foreach ($bookings_by_status as $status) {
                        if ($status['status'] == 'confirmed') {
                            $confirmation_rate = ($total_bookings > 0) ? round(($status['count'] / $total_bookings) * 100) : 0;
                            break;
                        }
                    }
                    ?>
                    <p class="metric-value"><?php echo $confirmation_rate; ?>%</p>
                    <p class="metric-desc">of total bookings confirmed</p>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="metric-info">
                    <h4>Most Popular Event</h4>
                    <?php
                    $most_popular_event = !empty($bookings_by_event_type) ? $bookings_by_event_type[0]['event_type'] : 'N/A';
                    $event_count = !empty($bookings_by_event_type) ? $bookings_by_event_type[0]['count'] : 0;
                    ?>
                    <p class="metric-value"><?php echo htmlspecialchars($most_popular_event); ?></p>
                    <p class="metric-desc"><?php echo $event_count; ?> bookings</p>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="metric-info">
                    <h4>Monthly Growth</h4>
                    <?php
                    $growth_rate = 0;
                    if (count($bookings_by_month) >= 2) {
                        $current_month = $bookings_by_month[count($bookings_by_month) - 1]['count'];
                        $previous_month = $bookings_by_month[count($bookings_by_month) - 2]['count'];
                        $growth_rate = ($previous_month > 0) ? round((($current_month - $previous_month) / $previous_month) * 100) : 0;
                    }
                    ?>
                    <p class="metric-value"><?php echo $growth_rate; ?>%</p>
                    <p class="metric-desc">compared to last month</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Prepare data for charts
const statusLabels = [];
const statusData = [];
<?php foreach ($bookings_by_status as $status): ?>
    statusLabels.push('<?php echo ucfirst($status["status"]); ?>');
    statusData.push(<?php echo $status["count"]; ?>);
<?php endforeach; ?>

const eventTypeLabels = [];
const eventTypeData = [];
<?php foreach ($bookings_by_event_type as $event_type): ?>
    eventTypeLabels.push('<?php echo $event_type["event_type"]; ?>');
    eventTypeData.push(<?php echo $event_type["count"]; ?>);
<?php endforeach; ?>

const bookingMonths = [];
const bookingCounts = [];
<?php foreach ($bookings_by_month as $month_data): ?>
    bookingMonths.push('<?php echo date("M Y", strtotime($month_data["month"] . "-01")); ?>');
    bookingCounts.push(<?php echo $month_data["count"]; ?>);
<?php endforeach; ?>

const contactMonths = [];
const contactCounts = [];
<?php foreach ($contacts_by_month as $month_data): ?>
    contactMonths.push('<?php echo date("M Y", strtotime($month_data["month"] . "-01")); ?>');
    contactCounts.push(<?php echo $month_data["count"]; ?>);
<?php endforeach; ?>

// Create charts
document.addEventListener('DOMContentLoaded', function() {
    // Booking Status Chart
    const statusCtx = document.getElementById('bookingStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: [
                    '#f39c12', // pending - orange
                    '#2ecc71', // confirmed - green
                    '#e74c3c'  // cancelled - red
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Event Type Chart
    const eventTypeCtx = document.getElementById('eventTypeChart').getContext('2d');
    new Chart(eventTypeCtx, {
        type: 'doughnut',
        data: {
            labels: eventTypeLabels,
            datasets: [{
                data: eventTypeData,
                backgroundColor: [
                    '#8e44ad', // primary
                    '#3498db', // info
                    '#2ecc71', // success
                    '#f39c12', // warning
                    '#e74c3c', // danger
                    '#1abc9c', // teal
                    '#d35400'  // orange
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Booking Trends Chart
    const bookingTrendsCtx = document.getElementById('bookingTrendsChart').getContext('2d');
    new Chart(bookingTrendsCtx, {
        type: 'line',
        data: {
            labels: bookingMonths,
            datasets: [{
                label: 'Number of Bookings',
                data: bookingCounts,
                backgroundColor: 'rgba(142, 68, 173, 0.2)',
                borderColor: 'rgba(142, 68, 173, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
    
    // Contact Trends Chart
    const contactTrendsCtx = document.getElementById('contactTrendsChart').getContext('2d');
    new Chart(contactTrendsCtx, {
        type: 'bar',
        data: {
            labels: contactMonths,
            datasets: [{
                label: 'Number of Contact Messages',
                data: contactCounts,
                backgroundColor: 'rgba(52, 152, 219, 0.5)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?>