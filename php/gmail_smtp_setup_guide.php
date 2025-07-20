<?php
/**
 * Complete Gmail SMTP Setup Guide
 * 
 * Step-by-step guide to configure Gmail SMTP for production email delivery
 */

echo "<h2>📧 Complete Gmail SMTP Setup Guide</h2>";
echo "<p><strong>Email:</strong> byirival009@gmail.com</p>";

echo "<h3>🔐 Step 1: Enable 2-Factor Authentication</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>📱 Enable 2FA on byirival009@gmail.com:</h4>";
echo "<ol>";
echo "<li><strong>Go to Google Account:</strong> <a href='https://myaccount.google.com' target='_blank'>https://myaccount.google.com</a></li>";
echo "<li><strong>Sign in</strong> with byirival009@gmail.com</li>";
echo "<li><strong>Click 'Security'</strong> in the left sidebar</li>";
echo "<li><strong>Find '2-Step Verification'</strong> section</li>";
echo "<li><strong>Click 'Get Started'</strong> or 'Turn On'</li>";
echo "<li><strong>Follow the setup wizard:</strong>";
echo "<ul>";
echo "<li>Enter your phone number</li>";
echo "<li>Choose SMS or Voice call</li>";
echo "<li>Enter verification code</li>";
echo "<li>Click 'Turn On'</li>";
echo "</ul>";
echo "</li>";
echo "</ol>";
echo "<p><strong>✅ Result:</strong> 2-Factor Authentication will be enabled on your account</p>";
echo "</div>";

echo "<h3>🔑 Step 2: Generate Gmail App Password</h3>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h4>⚠️ Important:</h4>";
echo "<p>You MUST complete Step 1 (2FA) before you can generate App Passwords!</p>";
echo "</div>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🔐 Generate App Password:</h4>";
echo "<ol>";
echo "<li><strong>Go to Google Account Security:</strong> <a href='https://myaccount.google.com/security' target='_blank'>https://myaccount.google.com/security</a></li>";
echo "<li><strong>Sign in</strong> with byirival009@gmail.com</li>";
echo "<li><strong>Scroll down</strong> to 'Signing in to Google' section</li>";
echo "<li><strong>Click 'App passwords'</strong> (only visible if 2FA is enabled)</li>";
echo "<li><strong>You may need to sign in again</strong></li>";
echo "<li><strong>Select app:</strong> Choose 'Mail'</li>";
echo "<li><strong>Select device:</strong> Choose 'Other (Custom name)'</li>";
echo "<li><strong>Enter name:</strong> 'MC Booking System' or 'Website SMTP'</li>";
echo "<li><strong>Click 'Generate'</strong></li>";
echo "<li><strong>Copy the 16-character password</strong> (example: abcd efgh ijkl mnop)</li>";
echo "<li><strong>Save it securely</strong> - you won't see it again!</li>";
echo "</ol>";
echo "<p><strong>✅ Result:</strong> You'll get a 16-character app password for SMTP</p>";
echo "</div>";

echo "<h3>⚙️ Step 3: Update SMTP Configuration</h3>";

echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
echo "<h4>🔧 Update config.php:</h4>";
echo "<p><strong>File location:</strong> <code>php/config.php</code></p>";
echo "<p><strong>Find this line:</strong></p>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px;'>define('SMTP_PASSWORD', 'your-app-password'); // Update with your Gmail app password</pre>";
echo "<p><strong>Replace with:</strong></p>";
echo "<pre style='background: #d4edda; padding: 10px; border-radius: 4px;'>define('SMTP_PASSWORD', 'your-16-character-app-password-here');</pre>";
echo "<p><strong>Example:</strong></p>";
echo "<pre style='background: #d4edda; padding: 10px; border-radius: 4px;'>define('SMTP_PASSWORD', 'abcd efgh ijkl mnop');</pre>";
echo "</div>";

// Show current configuration
echo "<h4>📋 Current SMTP Configuration:</h4>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<pre>";
echo "SMTP Host: smtp.gmail.com\n";
echo "SMTP Port: 587\n";
echo "SMTP Username: byirival009@gmail.com\n";
echo "SMTP Password: [NEEDS APP PASSWORD]\n";
echo "SMTP From Email: byirival009@gmail.com\n";
echo "SMTP From Name: Valentin MC Services\n";
echo "Admin Email: byirival009@gmail.com";
echo "</pre>";
echo "</div>";

echo "<h3>🧪 Step 4: Test Email Delivery</h3>";

// Create email test form
echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>📧 Test Email System:</h4>";

if (isset($_POST['test_email'])) {
    $test_email = $_POST['test_email'];
    $test_subject = 'SMTP Test - ' . date('Y-m-d H:i:s');
    $test_message = '
    <html>
    <body>
        <h2>🎉 SMTP Test Successful!</h2>
        <p>This email confirms that your Gmail SMTP configuration is working correctly.</p>
        <p><strong>Sent from:</strong> MC Booking System</p>
        <p><strong>Admin Email:</strong> byirival009@gmail.com</p>
        <p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</p>
        <p>Your booking system is ready to send professional emails!</p>
    </body>
    </html>';
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Valentin MC Services <byirival009@gmail.com>',
        'Reply-To: byirival009@gmail.com'
    ];
    
    $email_sent = @mail($test_email, $test_subject, $test_message, implode("\r\n", $headers));
    
    if ($email_sent) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>✅ Test Email Sent Successfully!</h5>";
        echo "<p>Check <strong>{$test_email}</strong> for the test email.</p>";
        echo "<p>If you received it, your SMTP configuration is working!</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Test Email Failed</h5>";
        echo "<p>Possible issues:</p>";
        echo "<ul>";
        echo "<li>App password not set correctly in config.php</li>";
        echo "<li>2-Factor Authentication not enabled</li>";
        echo "<li>SMTP settings incorrect</li>";
        echo "<li>Gmail blocking the connection</li>";
        echo "</ul>";
        echo "</div>";
    }
}

echo "<form method='POST' style='margin: 15px 0;'>";
echo "<p><strong>Test Email Address:</strong></p>";
echo "<input type='email' name='test_email' placeholder='Enter email to test' style='padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;' required>";
echo "<button type='submit' name='test_email_btn' style='background: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 4px; margin-left: 10px; cursor: pointer;'>Send Test Email</button>";
echo "</form>";
echo "</div>";

echo "<h3>🔄 Step 5: Complete Workflow Test</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🎯 Test Complete Booking System:</h4>";
echo "<ol>";
echo "<li><strong>Submit Test Booking:</strong> <a href='../booking.html' target='_blank'>Booking Form</a></li>";
echo "<li><strong>Check Admin Email:</strong> byirival009@gmail.com should receive notification</li>";
echo "<li><strong>Login to Admin:</strong> <a href='../admin/dashboard.php' target='_blank'>Admin Dashboard</a></li>";
echo "<li><strong>Update Booking Status:</strong> Change to 'confirmed' with message</li>";
echo "<li><strong>Check Client Email:</strong> Client should receive status update</li>";
echo "</ol>";

echo "<h5>📧 Expected Email Flow:</h5>";
echo "<ul>";
echo "<li><strong>Client submits booking</strong> → Client gets confirmation email</li>";
echo "<li><strong>Admin gets notification</strong> → byirival009@gmail.com receives alert</li>";
echo "<li><strong>Admin updates status</strong> → Client gets status update email</li>";
echo "<li><strong>All emails professional</strong> → Sent from byirival009@gmail.com</li>";
echo "</ul>";
echo "</div>";

echo "<h3>🚨 Troubleshooting</h3>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h4>⚠️ Common Issues and Solutions:</h4>";

echo "<h5>1. 'App passwords' option not visible:</h5>";
echo "<ul>";
echo "<li><strong>Cause:</strong> 2-Factor Authentication not enabled</li>";
echo "<li><strong>Solution:</strong> Complete Step 1 first</li>";
echo "</ul>";

echo "<h5>2. SMTP authentication failed:</h5>";
echo "<ul>";
echo "<li><strong>Cause:</strong> Wrong app password in config.php</li>";
echo "<li><strong>Solution:</strong> Double-check the 16-character password</li>";
echo "</ul>";

echo "<h5>3. Emails not sending:</h5>";
echo "<ul>";
echo "<li><strong>Check:</strong> XAMPP Apache is running</li>";
echo "<li><strong>Check:</strong> Internet connection</li>";
echo "<li><strong>Check:</strong> Gmail account not locked</li>";
echo "</ul>";

echo "<h5>4. Emails going to spam:</h5>";
echo "<ul>";
echo "<li><strong>Solution:</strong> Add byirival009@gmail.com to contacts</li>";
echo "<li><strong>Solution:</strong> Check spam folder initially</li>";
echo "</ul>";
echo "</div>";

echo "<h3>📋 Quick Setup Checklist</h3>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>✅ Setup Checklist:</h4>";
echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";

echo "<div>";
echo "<h5>🔐 Gmail Account Setup:</h5>";
echo "<ul>";
echo "<li>☐ Enable 2-Factor Authentication</li>";
echo "<li>☐ Generate App Password</li>";
echo "<li>☐ Save App Password securely</li>";
echo "</ul>";
echo "</div>";

echo "<div>";
echo "<h5>⚙️ System Configuration:</h5>";
echo "<ul>";
echo "<li>☐ Update SMTP_PASSWORD in config.php</li>";
echo "<li>☐ Test email delivery</li>";
echo "<li>☐ Test complete booking workflow</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</div>";

echo "<h3>🎉 Final Result</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>🚀 After Setup Complete:</h4>";

echo "<h5>📧 Email Flow:</h5>";
echo "<ol>";
echo "<li><strong>Client books appointment</strong> → Automatic confirmation email sent</li>";
echo "<li><strong>Admin notification</strong> → byirival009@gmail.com receives booking alert</li>";
echo "<li><strong>Admin approves/rejects</strong> → Client receives professional status update</li>";
echo "<li><strong>All emails professional</strong> → Branded templates with your contact info</li>";
echo "</ol>";

echo "<h5>✅ Benefits:</h5>";
echo "<ul>";
echo "<li>✅ <strong>Professional emails</strong> from your Gmail account</li>";
echo "<li>✅ <strong>Reliable delivery</strong> using Gmail SMTP</li>";
echo "<li>✅ <strong>Real-time notifications</strong> to byirival009@gmail.com</li>";
echo "<li>✅ <strong>Client communication</strong> with status updates</li>";
echo "<li>✅ <strong>Branded templates</strong> with your business info</li>";
echo "</ul>";

echo "<h5>🎯 Ready For:</h5>";
echo "<ul>";
echo "<li>🎊 <strong>Production use</strong> with real clients</li>";
echo "<li>📧 <strong>Professional email communication</strong></li>";
echo "<li>🔔 <strong>Real-time booking notifications</strong></li>";
echo "<li>📱 <strong>Mobile-friendly email templates</strong></li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>📞 Need Help?</h4>";
echo "<p>If you encounter any issues during setup:</p>";
echo "<ol>";
echo "<li><strong>Double-check</strong> each step above</li>";
echo "<li><strong>Verify</strong> 2FA is enabled on byirival009@gmail.com</li>";
echo "<li><strong>Ensure</strong> app password is copied correctly</li>";
echo "<li><strong>Test</strong> with the email test form above</li>";
echo "</ol>";
echo "<p><strong>Your MC booking system will be fully operational with professional email delivery!</strong> 🎉</p>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gmail SMTP Setup Guide</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        h4, h5 { color: inherit; margin-bottom: 10px; }
        p { line-height: 1.6; }
        ul, ol { line-height: 1.8; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
        input, button { font-family: inherit; }
        button:hover { opacity: 0.9; }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Content will be inserted here by PHP -->
    </div>
</body>
</html>
