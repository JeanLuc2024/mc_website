/**
 * Real-time Notification System for Admin Panel
 * 
 * This script handles real-time notifications, browser notifications,
 * and notification sounds for the admin panel.
 */

class NotificationManager {
    constructor() {
        this.checkInterval = 30000; // Check every 30 seconds
        this.lastCheck = new Date().toISOString();
        this.notificationSound = null;
        this.isPageVisible = true;
        this.unreadCount = 0;
        
        this.init();
    }
    
    init() {
        // Request notification permission
        this.requestNotificationPermission();
        
        // Load notification sound
        this.loadNotificationSound();
        
        // Set up page visibility detection
        this.setupPageVisibility();
        
        // Start checking for notifications
        this.startNotificationCheck();
        
        // Update notification badge
        this.updateNotificationBadge();
        
        // Set up event listeners
        this.setupEventListeners();
    }
    
    requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    console.log('Notification permission granted');
                } else {
                    console.log('Notification permission denied');
                }
            });
        }
    }
    
    loadNotificationSound() {
        try {
            this.notificationSound = new Audio('sounds/notification.mp3');
            this.notificationSound.volume = 0.5;
        } catch (error) {
            console.log('Could not load notification sound:', error);
        }
    }
    
    setupPageVisibility() {
        document.addEventListener('visibilitychange', () => {
            this.isPageVisible = !document.hidden;
            
            if (this.isPageVisible) {
                // Page became visible, check for new notifications
                this.checkForNotifications();
            }
        });
    }
    
    startNotificationCheck() {
        // Initial check
        this.checkForNotifications();
        
        // Set up interval
        setInterval(() => {
            this.checkForNotifications();
        }, this.checkInterval);
    }
    
    async checkForNotifications() {
        try {
            const response = await fetch('api/check-notifications.php');
            const data = await response.json();
            
            if (data.success) {
                // Update unread count
                this.updateUnreadCount(data.unread_count);
                
                // Show browser notification for new notifications
                if (data.new_notifications > 0 && !this.isPageVisible) {
                    this.showBrowserNotification(data.new_notifications);
                }
                
                // Play sound for new notifications
                if (data.new_notifications > 0) {
                    this.playNotificationSound();
                }
                
                // Update last check time
                this.lastCheck = new Date().toISOString();
            }
        } catch (error) {
            console.error('Error checking notifications:', error);
        }
    }
    
    updateUnreadCount(count) {
        this.unreadCount = count;
        this.updateNotificationBadge();
        this.updatePageTitle();
    }
    
    updateNotificationBadge() {
        // Update sidebar badge
        const sidebarBadge = document.querySelector('.sidebar-nav a[href="notifications.php"] .badge');
        if (sidebarBadge) {
            if (this.unreadCount > 0) {
                sidebarBadge.textContent = this.unreadCount;
                sidebarBadge.style.display = 'inline-block';
            } else {
                sidebarBadge.style.display = 'none';
            }
        }
        
        // Update header notification icon
        const headerNotificationIcon = document.querySelector('.header-notifications');
        if (headerNotificationIcon) {
            const badge = headerNotificationIcon.querySelector('.notification-badge');
            if (badge) {
                if (this.unreadCount > 0) {
                    badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    }
    
    updatePageTitle() {
        const originalTitle = document.title.replace(/^\(\d+\)\s*/, '');
        
        if (this.unreadCount > 0) {
            document.title = `(${this.unreadCount}) ${originalTitle}`;
        } else {
            document.title = originalTitle;
        }
    }
    
    showBrowserNotification(count) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const title = count === 1 ? 'New Notification' : `${count} New Notifications`;
            const body = count === 1 ? 
                'You have a new notification in your admin panel.' :
                `You have ${count} new notifications in your admin panel.`;
            
            const notification = new Notification(title, {
                body: body,
                icon: '../images/notification-icon.png',
                badge: '../images/notification-badge.png',
                tag: 'admin-notification',
                requireInteraction: false
            });
            
            notification.onclick = () => {
                window.focus();
                notification.close();
                
                // Navigate to notifications page if not already there
                if (!window.location.pathname.includes('notifications.php')) {
                    window.location.href = 'notifications.php';
                }
            };
            
            // Auto-close after 5 seconds
            setTimeout(() => {
                notification.close();
            }, 5000);
        }
    }
    
    playNotificationSound() {
        if (this.notificationSound) {
            try {
                this.notificationSound.currentTime = 0;
                this.notificationSound.play().catch(error => {
                    console.log('Could not play notification sound:', error);
                });
            } catch (error) {
                console.log('Error playing notification sound:', error);
            }
        }
    }
    
    setupEventListeners() {
        // Mark notification as read when clicked
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-item.unread')) {
                const notificationItem = e.target.closest('.notification-item');
                const notificationId = notificationItem.dataset.notificationId;
                
                if (notificationId) {
                    this.markAsRead(notificationId);
                }
            }
        });
        
        // Handle mark all as read
        const markAllReadBtn = document.querySelector('[data-action="mark-all-read"]');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }
    }
    
    async markAsRead(notificationId) {
        try {
            const response = await fetch(`api/mark-notification-read.php?id=${notificationId}`);
            const data = await response.json();
            
            if (data.success) {
                // Update UI
                const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('unread');
                    notificationItem.classList.add('read');
                }
                
                // Update unread count
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                this.updateNotificationBadge();
                this.updatePageTitle();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }
    
    async markAllAsRead() {
        try {
            const response = await fetch('api/mark-all-notifications-read.php');
            const data = await response.json();
            
            if (data.success) {
                // Update UI
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('read');
                });
                
                // Update unread count
                this.unreadCount = 0;
                this.updateNotificationBadge();
                this.updatePageTitle();
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }
    
    // Public method to manually trigger notification check
    checkNow() {
        this.checkForNotifications();
    }
    
    // Public method to show a custom notification
    showCustomNotification(title, message, type = 'info') {
        // Create in-app notification
        this.createInAppNotification(title, message, type);
        
        // Show browser notification if page is not visible
        if (!this.isPageVisible && 'Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '../images/notification-icon.png'
            });
        }
    }
    
    createInAppNotification(title, message, type) {
        const notification = document.createElement('div');
        notification.className = `in-app-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <h4>${title}</h4>
                <p>${message}</p>
            </div>
            <button class="close-btn">&times;</button>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
        
        // Close button functionality
        notification.querySelector('.close-btn').addEventListener('click', () => {
            notification.remove();
        });
    }
}

// Initialize notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
});

// CSS for in-app notifications
const notificationStyles = `
<style>
.in-app-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 15px;
    max-width: 350px;
    z-index: 10000;
    border-left: 4px solid #3498db;
    animation: slideInRight 0.3s ease-out;
}

.in-app-notification.success {
    border-left-color: #27ae60;
}

.in-app-notification.error {
    border-left-color: #e74c3c;
}

.in-app-notification.warning {
    border-left-color: #f39c12;
}

.in-app-notification .notification-content h4 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
}

.in-app-notification .notification-content p {
    margin: 0;
    font-size: 13px;
    color: #7f8c8d;
    line-height: 1.4;
}

.in-app-notification .close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    font-size: 18px;
    color: #bdc3c7;
    cursor: pointer;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.in-app-notification .close-btn:hover {
    color: #7f8c8d;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #e74c3c;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
`;

// Inject styles
document.head.insertAdjacentHTML('beforeend', notificationStyles);
