<?php
/**
 * Real-time Notifications Widget for Admin Panel
 */

require_once '../php/notification_system.php';

// Get notification counts and recent notifications
$unread_count = getUnreadNotificationsCount();
$recent_notifications = getRecentNotifications(5);
?>

<div class="notifications-widget">
    <div class="notifications-header" onclick="toggleNotifications()">
        <i class="fas fa-bell"></i>
        <span class="notification-text">Notifications</span>
        <?php if ($unread_count > 0): ?>
            <span class="notification-badge"><?php echo $unread_count; ?></span>
        <?php endif; ?>
        <i class="fas fa-chevron-down toggle-icon"></i>
    </div>
    
    <div class="notifications-dropdown" id="notificationsDropdown" style="display: none;">
        <div class="notifications-header-dropdown">
            <h4>Recent Notifications</h4>
            <?php if ($unread_count > 0): ?>
                <button class="mark-all-read-btn" onclick="markAllAsRead()">Mark All Read</button>
            <?php endif; ?>
        </div>
        
        <div class="notifications-list">
            <?php if (empty($recent_notifications)): ?>
                <div class="no-notifications">
                    <i class="fas fa-inbox"></i>
                    <p>No notifications yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" 
                         data-id="<?php echo $notification['id']; ?>"
                         onclick="markAsRead(<?php echo $notification['id']; ?>)">
                        
                        <div class="notification-icon">
                            <?php
                            $icons = [
                                'new_booking' => 'fas fa-calendar-plus',
                                'booking_update' => 'fas fa-edit',
                                'email_sent' => 'fas fa-envelope',
                                'email_failed' => 'fas fa-exclamation-triangle',
                                'system_alert' => 'fas fa-bell'
                            ];
                            $icon_class = $icons[$notification['type']] ?? 'fas fa-info-circle';
                            ?>
                            <i class="<?php echo $icon_class; ?>"></i>
                        </div>
                        
                        <div class="notification-content">
                            <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                            <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                            <div class="notification-time"><?php echo timeAgo($notification['created_at']); ?></div>
                        </div>
                        
                        <div class="notification-priority priority-<?php echo $notification['priority']; ?>">
                            <?php if ($notification['priority'] === 'urgent'): ?>
                                <i class="fas fa-exclamation"></i>
                            <?php elseif ($notification['priority'] === 'high'): ?>
                                <i class="fas fa-arrow-up"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="notifications-footer">
            <a href="notifications.php" class="view-all-link">View All Notifications</a>
        </div>
    </div>
</div>

<style>
.notifications-widget {
    position: relative;
    display: inline-block;
}

.notifications-header {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 8px 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    min-width: 140px;
}

.notifications-header:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.notification-badge {
    background: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.toggle-icon {
    margin-left: auto;
    transition: transform 0.3s ease;
}

.notifications-widget.open .toggle-icon {
    transform: rotate(180deg);
}

.notifications-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    width: 350px;
    max-height: 400px;
    z-index: 1000;
    margin-top: 5px;
}

.notifications-header-dropdown {
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notifications-header-dropdown h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 16px;
}

.mark-all-read-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.mark-all-read-btn:hover {
    background: #0056b3;
}

.notifications-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f1f3f4;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.notification-item:hover {
    background: #f8f9fa;
}

.notification-item.unread {
    background: #e3f2fd;
    border-left: 3px solid #2196f3;
}

.notification-item.read {
    opacity: 0.7;
}

.notification-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.notification-item[data-type="new_booking"] .notification-icon {
    background: #e8f5e8;
    color: #28a745;
}

.notification-item[data-type="booking_update"] .notification-icon {
    background: #fff3cd;
    color: #ffc107;
}

.notification-item[data-type="email_sent"] .notification-icon {
    background: #d4edda;
    color: #28a745;
}

.notification-item[data-type="email_failed"] .notification-icon {
    background: #f8d7da;
    color: #dc3545;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
    margin-bottom: 4px;
}

.notification-message {
    color: #6c757d;
    font-size: 13px;
    line-height: 1.4;
    margin-bottom: 4px;
}

.notification-time {
    color: #adb5bd;
    font-size: 11px;
}

.notification-priority {
    width: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.priority-urgent {
    color: #dc3545;
}

.priority-high {
    color: #fd7e14;
}

.no-notifications {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.no-notifications i {
    font-size: 32px;
    margin-bottom: 10px;
    opacity: 0.5;
}

.notifications-footer {
    padding: 10px 15px;
    border-top: 1px solid #dee2e6;
    text-align: center;
}

.view-all-link {
    color: #007bff;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
}

.view-all-link:hover {
    text-decoration: underline;
}
</style>

<script>
function toggleNotifications() {
    const dropdown = document.getElementById('notificationsDropdown');
    const widget = document.querySelector('.notifications-widget');
    
    if (dropdown.style.display === 'none') {
        dropdown.style.display = 'block';
        widget.classList.add('open');
        // Refresh notifications when opened
        refreshNotifications();
    } else {
        dropdown.style.display = 'none';
        widget.classList.remove('open');
    }
}

function markAsRead(notificationId) {
    fetch('ajax/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({id: notificationId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-id="${notificationId}"]`);
            if (item) {
                item.classList.remove('unread');
                item.classList.add('read');
            }
            updateNotificationBadge();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    fetch('ajax/mark_all_notifications_read.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                item.classList.add('read');
            });
            updateNotificationBadge();
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateNotificationBadge() {
    const badge = document.querySelector('.notification-badge');
    const markAllBtn = document.querySelector('.mark-all-read-btn');
    
    if (badge) {
        badge.style.display = 'none';
    }
    if (markAllBtn) {
        markAllBtn.style.display = 'none';
    }
}

function refreshNotifications() {
    fetch('ajax/get_notifications.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update notification count
            const badge = document.querySelector('.notification-badge');
            if (data.unread_count > 0) {
                if (badge) {
                    badge.textContent = data.unread_count;
                    badge.style.display = 'inline-block';
                } else {
                    // Create badge if it doesn't exist
                    const header = document.querySelector('.notifications-header');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.textContent = data.unread_count;
                    header.insertBefore(newBadge, header.querySelector('.toggle-icon'));
                }
            } else if (badge) {
                badge.style.display = 'none';
            }
        }
    })
    .catch(error => console.error('Error refreshing notifications:', error));
}

// Auto-refresh notifications every 30 seconds
setInterval(refreshNotifications, 30000);

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const widget = document.querySelector('.notifications-widget');
    if (!widget.contains(event.target)) {
        const dropdown = document.getElementById('notificationsDropdown');
        dropdown.style.display = 'none';
        widget.classList.remove('open');
    }
});
</script>

<?php
/**
 * Helper function to format time ago
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';
    
    return date('M j', strtotime($datetime));
}
?>
