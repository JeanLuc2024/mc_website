# Email Communication System Guide

## 🎉 **YES! You can now reply to clients directly through your admin panel!**

Your MC website now includes a comprehensive email communication system that allows you (Valentin) to professionally communicate with clients directly through the admin panel using their booking email addresses.

## ✅ **What You Can Do Now:**

### **1. Direct Email Communication**
- **Reply to clients** using their booking email addresses
- **Send professional emails** with your branding
- **Use pre-made templates** for common scenarios
- **Track all email history** for each booking

### **2. Professional Email Templates**
- **6 pre-built templates** for different situations
- **Customizable templates** with your personal touch
- **Variable replacement** (client name, booking details, etc.)
- **Professional email formatting** with your signature

### **3. Easy Access from Admin Panel**
- **Email button** next to each booking in the bookings table
- **Dedicated email composer** with rich features
- **Email history tracking** for each client
- **Mobile-responsive** email interface

## 🚀 **How to Use the Email System:**

### **Step 1: Setup the Email System**
1. **Run the setup script:**
   ```
   http://localhost/mc_website/php/setup_email_communication.php
   ```

2. **This will create:**
   - Email communications tracking table
   - Email templates with professional content
   - Email history system
   - Email attachments support

### **Step 2: Access Email Features**

#### **From Bookings Page:**
1. Go to **Admin Panel → Bookings**
2. Click the **📧 Email** button next to any booking
3. This opens the email composer for that specific client

#### **From Booking Details:**
1. Go to **Admin Panel → Bookings**
2. Click **👁️ View** to see booking details
3. Click **📧 Email Client** button
4. Compose and send your email

### **Step 3: Compose Professional Emails**

#### **Using Templates (Recommended):**
1. **Select a template** from the dropdown:
   - **Booking Confirmation** - Confirm their booking
   - **Booking Follow-up** - Check on upcoming events
   - **Pricing Information** - Send pricing details
   - **Availability Check** - Respond to availability inquiries
   - **Event Cancellation** - Handle cancellations professionally
   - **Custom Thank You** - Thank clients after events

2. **Template auto-fills** subject and message
3. **Customize as needed** for the specific client
4. **Send the email**

#### **Writing Custom Emails:**
1. **Choose "Custom Reply"** as email type
2. **Write your own subject** and message
3. **Use variables** like {client_name}, {booking_ref}, etc.
4. **Preview before sending**

## 📧 **Available Email Templates:**

### **1. Booking Confirmation**
- **When to use:** After confirming a booking
- **Purpose:** Officially confirm the event details
- **Includes:** All booking details, your contact info

### **2. Booking Follow-up**
- **When to use:** A few days before the event
- **Purpose:** Check if everything is ready
- **Includes:** Reminder of event details, offer assistance

### **3. Pricing Information**
- **When to use:** When clients ask about pricing
- **Purpose:** Provide detailed pricing for all services
- **Includes:** Wedding ($500), Anniversary ($400), Corporate ($300)

### **4. Availability Check**
- **When to use:** When clients inquire about dates
- **Purpose:** Professional response to availability requests
- **Includes:** Promise to check and respond within 24 hours

### **5. Event Cancellation**
- **When to use:** When clients cancel bookings
- **Purpose:** Handle cancellations professionally
- **Includes:** Confirmation of cancellation, refund information

### **6. Custom Thank You**
- **When to use:** After completing an event
- **Purpose:** Thank clients and encourage future bookings
- **Includes:** Personal thanks, feedback request, future services

## 🔧 **Smart Variables System:**

Use these variables in your emails - they automatically get replaced with real booking data:

- **{client_name}** → Client's full name
- **{booking_ref}** → Booking reference (e.g., BK-ABC123)
- **{event_type}** → Wedding, Anniversary, Corporate Meeting
- **{event_date}** → Formatted event date (e.g., January 15, 2025)
- **{event_time}** → Formatted event time (e.g., 2:00 PM)
- **{event_location}** → Event venue/location
- **{guests}** → Number of guests
- **{package}** → Selected package (if any)

## 📊 **Email Tracking & History:**

### **What Gets Tracked:**
- **All emails sent** to each client
- **Email timestamps** and types
- **Who sent the email** (admin user)
- **Email content** and subjects
- **Read status** and response tracking

### **Where to View History:**
- **Booking Details Page** - See all emails for that booking
- **Email Client Page** - View history while composing
- **Client Information Panel** - Quick email stats

## 🎯 **Professional Email Features:**

### **Automatic Email Formatting:**
- **Professional header** with your name and title
- **Branded email template** with your colors
- **Booking information box** with key details
- **Professional signature** with contact information
- **Mobile-responsive design**

### **Email Personalization:**
- **Client's name** in greeting
- **Specific booking details** included
- **Event-specific information**
- **Your professional signature**

## 📱 **Mobile-Friendly Interface:**

- **Responsive design** works on phones and tablets
- **Touch-friendly buttons** and forms
- **Easy template selection** on mobile
- **Quick variable insertion** tools

## 🔐 **Security & Professional Features:**

### **Email Security:**
- **Secure admin-only access**
- **Activity logging** for all emails sent
- **Email history preservation**
- **Professional email headers**

### **Professional Branding:**
- **Your name and title** in email headers
- **Professional email signature**
- **Consistent branding** across all emails
- **Contact information** included

## 📋 **Common Email Scenarios:**

### **Scenario 1: Client Books Wedding**
1. **Automatic notification** sent to you
2. **You receive booking details** in admin panel
3. **Click Email button** next to booking
4. **Select "Booking Confirmation" template**
5. **Customize message** if needed
6. **Send professional confirmation**

### **Scenario 2: Client Asks About Pricing**
1. **Client contacts you** about services
2. **Create booking** in admin panel (even if pending)
3. **Use "Pricing Information" template**
4. **Send detailed pricing** with all packages
5. **Follow up** as needed

### **Scenario 3: Event is Next Week**
1. **Go to upcoming bookings**
2. **Click Email button** for the event
3. **Use "Booking Follow-up" template**
4. **Check if they need anything**
5. **Ensure smooth event execution**

## 🎉 **Benefits for Your Business:**

### **Professional Communication:**
- **Consistent branding** in all emails
- **Professional templates** save time
- **No more manual email composition**
- **Automatic booking details** inclusion

### **Better Client Relationships:**
- **Timely communication** with clients
- **Professional appearance** builds trust
- **Easy follow-up** on bookings
- **Complete communication history**

### **Time Savings:**
- **Pre-written templates** for common scenarios
- **Automatic variable replacement**
- **One-click email access** from bookings
- **No switching between systems**

### **Business Growth:**
- **Professional image** attracts more clients
- **Better client satisfaction** through communication
- **Easy upselling** through follow-up emails
- **Referral requests** in thank you emails

## 🚨 **Important Notes:**

1. **Update your email settings** in `php/config.php` with your actual email credentials
2. **Test the system** with a sample booking first
3. **Customize templates** to match your personal style
4. **Always preview emails** before sending
5. **Keep professional tone** in all communications

## 📞 **Next Steps:**

1. **Run the setup script** to initialize the email system
2. **Test with a sample booking** to ensure everything works
3. **Customize email templates** to match your style
4. **Start using the system** for all client communications
5. **Monitor email history** to track client interactions

---

**Congratulations!** You now have a professional email communication system that allows you to maintain excellent client relationships while saving time and presenting a professional image. Your clients will appreciate the timely, professional communication, and you'll find it much easier to manage all your client interactions from one central location.
