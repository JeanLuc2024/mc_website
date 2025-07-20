# Professional Admin Panel Setup Guide

## Overview

Your MC website now includes a comprehensive, professional admin panel with advanced features for managing bookings, content, and notifications. This admin panel is completely separate from your main website and provides a secure, professional interface for business management.

## 🚀 New Features

### 1. **Real-time Notification System**
- ✅ Email notifications for new bookings
- ✅ Browser notifications when admin panel is not active
- ✅ Real-time notification center with unread counts
- ✅ Notification sounds (optional)
- ✅ Auto-refresh notification badges

### 2. **Content Management System**
- ✅ Edit website content without touching code
- ✅ Manage hero section, about page, services, contact info
- ✅ Support for text, HTML, and image content
- ✅ Version tracking and activity logging

### 3. **Enhanced Admin Features**
- ✅ Professional dashboard with analytics
- ✅ Advanced booking management
- ✅ Activity logging for security
- ✅ Settings management
- ✅ User profile management

### 4. **Security & Professional Features**
- ✅ Secure admin authentication
- ✅ Session management
- ✅ Activity logging
- ✅ Role-based access (admin/manager)
- ✅ Separate admin area from main website

## 📋 Setup Instructions

### Step 1: Database Setup

1. **Run the setup script** to create all necessary tables:
   ```
   Navigate to: http://localhost/mc_website/php/setup_admin_enhancements.php
   ```

2. **Alternative: Manual SQL execution**
   If you prefer to run SQL manually, execute:
   ```sql
   -- Run the contents of php/notifications_table.sql
   ```

### Step 2: Email Configuration

1. **Update email settings** in `php/config.php`:
   ```php
   // Update these lines with your email settings
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'your-app-password');
   define('ADMIN_EMAIL', 'your-admin-email@domain.com');
   ```

2. **For Gmail users:**
   - Enable 2-factor authentication
   - Generate an app password
   - Use the app password in SMTP_PASSWORD

### Step 3: Admin Access

1. **Default admin credentials:**
   - Username: `admin`
   - Password: `admin123`
   - **⚠️ IMPORTANT: Change this immediately after first login!**

2. **Admin panel URL:**
   ```
   http://localhost/mc_website/admin/
   ```

### Step 4: Test the System

1. **Test booking notifications:**
   - Make a test booking through the website
   - Check if you receive email notifications
   - Verify notifications appear in admin panel

2. **Test content management:**
   - Go to Admin Panel → Content Management
   - Edit some website content
   - Verify changes appear on the main website

## 🎯 How It Works

### Booking Notification Flow

1. **Client books appointment** → Website booking form
2. **System processes booking** → Saves to database
3. **Notifications triggered** → Email sent to admin & client
4. **Admin gets notified** → Real-time notification in admin panel
5. **Admin manages booking** → Through professional admin interface

### Content Management Flow

1. **Admin edits content** → Through admin panel interface
2. **Changes saved** → To database with version tracking
3. **Website updated** → Content dynamically loaded from database
4. **Activity logged** → For security and audit trail

## 📁 File Structure

```
mc_website/
├── admin/                          # Professional admin panel
│   ├── index.php                   # Admin login
│   ├── dashboard.php               # Main dashboard
│   ├── bookings.php               # Booking management
│   ├── notifications.php          # Notification center
│   ├── content-management.php     # Content management
│   ├── settings.php               # Admin settings
│   ├── api/                       # API endpoints
│   │   └── check-notifications.php
│   ├── css/                       # Admin styles
│   ├── js/                        # Admin JavaScript
│   │   └── notifications.js       # Real-time notifications
│   └── includes/                  # Admin components
│       ├── header.php
│       ├── sidebar.php
│       └── footer.php
├── php/                           # Backend logic
│   ├── config.php                 # Enhanced configuration
│   ├── notifications.php          # Notification system
│   ├── booking.php                # Enhanced booking handler
│   └── setup_admin_enhancements.php # Setup script
└── [existing website files]
```

## 🔧 Configuration Options

### Email Notifications
- Enable/disable in admin settings
- Customize email templates in `php/notifications.php`
- Configure SMTP settings in `php/config.php`

### Admin Panel Settings
- Session timeout
- Notification preferences
- Booking limits
- Maintenance mode

### Content Management
- Add new content sections
- Support for different content types
- Version tracking
- Bulk content updates

## 🛡️ Security Features

### Authentication
- Secure password hashing
- Session management
- Login attempt limiting
- Automatic logout

### Activity Logging
- All admin actions logged
- IP address tracking
- User agent logging
- Audit trail for compliance

### Access Control
- Role-based permissions
- Admin/Manager roles
- Feature-level access control
- Secure admin area

## 📱 Mobile Responsive

The admin panel is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- All modern browsers

## 🔔 Notification Types

### Email Notifications
- New booking confirmations
- Booking status updates
- System alerts
- Daily/weekly reports

### Browser Notifications
- Real-time alerts
- Unread count badges
- Sound notifications
- Desktop notifications

### In-App Notifications
- Notification center
- Real-time updates
- Mark as read functionality
- Notification history

## 🚨 Important Security Notes

1. **Change default admin password immediately**
2. **Use strong passwords for all admin accounts**
3. **Keep admin panel URL private**
4. **Regularly update email credentials**
5. **Monitor activity logs for suspicious activity**
6. **Enable HTTPS in production**

## 📞 Support & Maintenance

### Regular Tasks
- Monitor notification logs
- Review booking analytics
- Update content as needed
- Check system health
- Backup database regularly

### Troubleshooting
- Check error logs in browser console
- Verify database connections
- Test email configuration
- Review activity logs

## 🎉 Next Steps

1. **Customize the admin panel** to match your branding
2. **Set up automated backups** for your database
3. **Configure SSL certificate** for production
4. **Train staff** on using the admin panel
5. **Set up monitoring** for system health

---

**Congratulations!** Your MC website now has a professional, feature-rich admin panel that will help you manage your business efficiently and professionally. The system is designed to grow with your business and can be easily extended with additional features as needed.
