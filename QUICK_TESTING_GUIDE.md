# 🧪 Quick Testing Guide - MC Website System

## 🚀 **IMMEDIATE TESTING STEPS**

### **Step 1: Test Client Booking System**
1. **Open booking form**: `http://localhost/mc_website/booking.html`
2. **Fill out the form** with test data:
   - Name: Test Client
   - Email: your-email@gmail.com
   - Phone: +1234567890
   - Event Date: Future date
   - Event Time: Any time
   - Event Type: Wedding
   - Location: Test Location
   - Guests: 50
   - Package: Premium
   - Message: Test booking message
3. **Submit the form** and verify success message
4. **Check for booking reference** (format: MC-YYMMDD-XXXXXX)

**Expected Result**: ✅ Success message with booking reference

---

### **Step 2: Test Admin Panel Access**
1. **Open admin login**: `http://localhost/mc_website/admin/`
2. **Login with credentials**:
   - Username: `admin`
   - Password: `admin123`
3. **Verify dashboard loads** with statistics
4. **Check navigation menu** works properly

**Expected Result**: ✅ Admin dashboard with booking statistics

---

### **Step 3: Test Booking Management**
1. **Go to bookings page**: `http://localhost/mc_website/admin/bookings.php`
2. **Verify test booking appears** in the table
3. **Click "View Details"** to see full booking information
4. **Test status update**:
   - Click edit button on booking
   - Change status to "Confirmed"
   - Add admin notes
   - Save changes
5. **Verify status changed** in booking list

**Expected Result**: ✅ Booking visible and status can be updated

---

### **Step 4: Test Email Communication**
1. **From booking details**, click "Email Client"
2. **Select email template** (e.g., "Booking Confirmation")
3. **Customize message** if needed
4. **Send email** and check for success message
5. **Check your Gmail** for the sent email

**Expected Result**: ✅ Email sent successfully (check Gmail inbox)

---

### **Step 5: Test Content Management**
1. **Go to content management**: `http://localhost/mc_website/admin/content-management.php`
2. **Edit hero section**:
   - Change title to "Test Title Update"
   - Update content
   - Save changes
3. **Open main website**: `http://localhost/mc_website/index.html`
4. **Verify changes appear** on the website

**Expected Result**: ✅ Website content updates immediately

---

### **Step 6: Test Notification System**
1. **Keep admin panel open** in one browser tab
2. **Open booking form** in another tab
3. **Submit a new booking**
4. **Switch back to admin panel**
5. **Check for notification** (red badge on notifications)
6. **Go to notifications page** to see new booking alert

**Expected Result**: ✅ Real-time notification appears

---

## 🔍 **TROUBLESHOOTING COMMON ISSUES**

### **Issue 1: Database Connection Error**
**Symptoms**: "Database connection failed" messages
**Solution**:
1. Start XAMPP MySQL service
2. Check if `mc_website` database exists
3. Run: `php php/complete_database_setup.php`

### **Issue 2: Admin Login Not Working**
**Symptoms**: "Invalid username or password"
**Solution**:
1. Verify credentials: admin/admin123
2. Check if `admin_users` table exists
3. Run: `php php/create_admin_user.php`

### **Issue 3: Emails Not Sending**
**Symptoms**: Email success message but no email received
**Solution**:
1. Check Gmail app password in `php/config.php`
2. Verify SMTP settings are correct
3. Test with: `php php/test_real_smtp.php`

### **Issue 4: Booking Form Not Submitting**
**Symptoms**: Form doesn't submit or shows errors
**Solution**:
1. Check browser console for JavaScript errors
2. Verify `php/booking_handler.php` exists
3. Check database connection

### **Issue 5: Content Changes Not Appearing**
**Symptoms**: Admin panel saves but website doesn't update
**Solution**:
1. Check file permissions on HTML files
2. Verify `php/update_website_content.php` is working
3. Clear browser cache and refresh

---

## 📊 **TESTING CHECKLIST**

### **Core Functionality**
- [ ] Client can submit booking form
- [ ] Booking appears in admin panel
- [ ] Admin can login successfully
- [ ] Admin can view booking details
- [ ] Admin can update booking status
- [ ] Admin can send emails to clients
- [ ] Admin can update website content
- [ ] Real-time notifications work
- [ ] Email delivery is working

### **User Experience**
- [ ] Forms are user-friendly
- [ ] Error messages are clear
- [ ] Success messages are informative
- [ ] Navigation is intuitive
- [ ] Pages load quickly
- [ ] Mobile responsiveness works

### **Security**
- [ ] Admin area requires login
- [ ] Sessions timeout properly
- [ ] Form validation prevents bad data
- [ ] SQL injection protection works
- [ ] XSS protection is active

---

## 🎯 **PERFORMANCE TESTING**

### **Load Testing**
1. **Submit multiple bookings** quickly
2. **Check for duplicate prevention**
3. **Verify database performance**
4. **Test with large datasets**

### **Browser Testing**
1. **Test in Chrome, Firefox, Safari**
2. **Check mobile browsers**
3. **Verify responsive design**
4. **Test JavaScript functionality**

---

## 📞 **SUPPORT & MAINTENANCE**

### **Regular Maintenance Tasks**
- **Weekly**: Check email logs and delivery
- **Monthly**: Review booking data and analytics
- **Quarterly**: Update admin passwords
- **Annually**: Backup database and files

### **Monitoring**
- **Database size** and performance
- **Email delivery rates**
- **Error logs** in PHP files
- **User feedback** and issues

---

## 🎉 **SUCCESS CRITERIA**

**Your system is working perfectly if:**
✅ Clients can book appointments easily
✅ You receive email notifications for new bookings
✅ You can manage bookings through admin panel
✅ You can communicate with clients via email
✅ You can update website content without coding
✅ All notifications and alerts work properly

**If all tests pass, your MC website system is READY FOR PRODUCTION!**
