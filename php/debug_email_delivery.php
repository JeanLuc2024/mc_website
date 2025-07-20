<?php
/**
 * Debug Email Delivery Issue
 * 
 * Comprehensive analysis of why emails show as "sent" but don't reach Gmail
 */

echo "<h2>🔍 Email Delivery Debugging</h2>";

// Include required files
require_once 'config.php';

echo "<h3>1. Current Email Configuration Analysis</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📧 SMTP Configuration:</h4>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr><td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>Setting</td><td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>Value</td><td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>Status</td></tr>";

$config_checks = [
    'Admin Email' => [ADMIN_EMAIL, ADMIN_EMAIL === 'byirival009@gmail.com' ? '✅' : '❌'],
    'SMTP Host' => [SMTP_HOST, SMTP_HOST === 'smtp.gmail.com' ? '✅' : '❌'],
    'SMTP Port' => [SMTP_PORT, SMTP_PORT == 587 ? '✅' : '❌'],
    'SMTP Username' => [SMTP_USERNAME, SMTP_USERNAME === 'byirival009@gmail.com' ? '✅' : '❌'],
    'SMTP Password' => [SMTP_PASSWORD !== 'your-app-password' ? 'fvaa vjqd hwfv jewt' : 'NOT SET', SMTP_PASSWORD !== 'your-app-password' ? '✅' : '❌'],
    'From Email' => [SMTP_FROM_EMAIL, SMTP_FROM_EMAIL === 'byirival009@gmail.com' ? '✅' : '❌'],
    'From Name' => [SMTP_FROM_NAME, !empty(SMTP_FROM_NAME) ? '✅' : '❌']
];

foreach ($config_checks as $setting => $data) {
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$setting}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($data[0]) . "</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$data[1]}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<h3>2. PHP Mail Function Analysis</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>🔧 PHP Mail Configuration:</h4>";

// Check PHP mail configuration
$mail_settings = [
    'sendmail_path' => ini_get('sendmail_path'),
    'SMTP' => ini_get('SMTP'),
    'smtp_port' => ini_get('smtp_port'),
    'sendmail_from' => ini_get('sendmail_from')
];

echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr><td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>PHP Setting</td><td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>Value</td></tr>";

foreach ($mail_settings as $setting => $value) {
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$setting}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . (empty($value) ? '<em>Not set</em>' : htmlspecialchars($value)) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h5>⚠️ ISSUE IDENTIFIED:</h5>";
echo "<p><strong>The problem is that PHP's mail() function is being used instead of proper SMTP authentication!</strong></p>";
echo "<p>The enhanced_smtp.php file is using PHP's basic mail() function, which doesn't actually connect to Gmail's SMTP servers with authentication.</p>";
echo "</div>";
echo "</div>";

echo "<h3>3. Test Real SMTP Connection</h3>";

if (isset($_POST['test_real_smtp'])) {
    echo "<h4>🔌 Testing Real SMTP Connection...</h4>";
    
    // Test actual SMTP connection using socket
    $smtp_test_result = testRealSMTPConnection();
    
    if ($smtp_test_result['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>✅ SMTP Connection Test Successful!</h5>";
        echo "<p>" . htmlspecialchars($smtp_test_result['message']) . "</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ SMTP Connection Test Failed</h5>";
        echo "<p>" . htmlspecialchars($smtp_test_result['message']) . "</p>";
        echo "</div>";
    }
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🔌 Test Real SMTP Connection</h4>";
echo "<form method='POST'>";
echo "<p>This will test if we can actually connect to Gmail's SMTP servers with authentication.</p>";
echo "<button type='submit' name='test_real_smtp' style='background: #17a2b8; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>🔌 Test SMTP Connection</button>";
echo "</form>";
echo "</div>";

echo "<h3>4. Create Proper SMTP Email Function</h3>";

if (isset($_POST['test_proper_smtp'])) {
    echo "<h4>📧 Testing Proper SMTP Email...</h4>";
    
    $result = sendProperSMTPEmail(
        'byirival009@gmail.com',
        '🔧 PROPER SMTP TEST - ' . date('Y-m-d H:i:s'),
        createProperTestEmail()
    );
    
    if ($result['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>✅ Proper SMTP Email Sent!</h5>";
        echo "<p>" . htmlspecialchars($result['message']) . "</p>";
        echo "<p><strong>Check your Gmail inbox now!</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ Proper SMTP Email Failed</h5>";
        echo "<p>" . htmlspecialchars($result['message']) . "</p>";
        echo "</div>";
    }
}

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>📧 Test Proper SMTP Email</h4>";
echo "<form method='POST'>";
echo "<p>This will send an email using proper SMTP authentication (not PHP's mail() function).</p>";
echo "<button type='submit' name='test_proper_smtp' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>📧 Send Proper SMTP Email</button>";
echo "</form>";
echo "</div>";

echo "<h3>5. The Real Problem and Solution</h3>";

echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🚨 ROOT CAUSE IDENTIFIED:</h4>";
echo "<p><strong>The issue is in the enhanced_smtp.php file!</strong></p>";
echo "<p>The current implementation uses PHP's basic <code>mail()</code> function, which:</p>";
echo "<ul>";
echo "<li>❌ Does NOT connect to Gmail's SMTP servers</li>";
echo "<li>❌ Does NOT use your Gmail app password</li>";
echo "<li>❌ Does NOT authenticate with Gmail</li>";
echo "<li>❌ Relies on local server mail configuration (which doesn't exist in XAMPP)</li>";
echo "</ul>";

echo "<p><strong>What happens:</strong></p>";
echo "<ol>";
echo "<li>PHP's mail() function returns 'true' (success)</li>";
echo "<li>But the email is never actually sent to Gmail</li>";
echo "<li>Your system thinks the email was sent</li>";
echo "<li>But Gmail never receives it</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>✅ SOLUTION:</h4>";
echo "<p><strong>We need to implement REAL SMTP authentication!</strong></p>";
echo "<p>I will create a proper SMTP implementation that:</p>";
echo "<ul>";
echo "<li>✅ Actually connects to smtp.gmail.com:587</li>";
echo "<li>✅ Uses TLS encryption</li>";
echo "<li>✅ Authenticates with your Gmail app password</li>";
echo "<li>✅ Sends emails through Gmail's servers</li>";
echo "</ul>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Test the proper SMTP function above</li>";
echo "<li>If it works, I'll update your email system</li>";
echo "<li>Then test booking notifications again</li>";
echo "</ol>";
echo "</div>";

/**
 * Test real SMTP connection to Gmail
 */
function testRealSMTPConnection() {
    $host = 'smtp.gmail.com';
    $port = 587;
    $timeout = 10;
    
    $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
    
    if (!$socket) {
        return [
            'success' => false,
            'message' => "Cannot connect to {$host}:{$port} - {$errstr} ({$errno})"
        ];
    }
    
    $response = fgets($socket);
    fclose($socket);
    
    if (strpos($response, '220') === 0) {
        return [
            'success' => true,
            'message' => "Successfully connected to Gmail SMTP server: " . trim($response)
        ];
    } else {
        return [
            'success' => false,
            'message' => "Unexpected response from Gmail SMTP: " . trim($response)
        ];
    }
}

/**
 * Send email using proper SMTP authentication
 */
function sendProperSMTPEmail($to, $subject, $message) {
    $host = 'smtp.gmail.com';
    $port = 587;
    $username = SMTP_USERNAME;
    $password = SMTP_PASSWORD;
    $from = SMTP_FROM_EMAIL;
    $from_name = SMTP_FROM_NAME;
    
    try {
        // Create socket connection
        $socket = fsockopen($host, $port, $errno, $errstr, 30);
        if (!$socket) {
            return ['success' => false, 'message' => "Connection failed: {$errstr}"];
        }
        
        // Read initial response
        $response = fgets($socket);
        if (strpos($response, '220') !== 0) {
            fclose($socket);
            return ['success' => false, 'message' => "Invalid initial response: {$response}"];
        }
        
        // Send EHLO
        fputs($socket, "EHLO localhost\r\n");
        $response = fgets($socket);
        
        // Start TLS
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket);
        if (strpos($response, '220') !== 0) {
            fclose($socket);
            return ['success' => false, 'message' => "STARTTLS failed: {$response}"];
        }
        
        // Enable crypto
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return ['success' => false, 'message' => "TLS encryption failed"];
        }
        
        // Send EHLO again after TLS
        fputs($socket, "EHLO localhost\r\n");
        $response = fgets($socket);
        
        // Authenticate
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket);
        
        fputs($socket, base64_encode($username) . "\r\n");
        $response = fgets($socket);
        
        fputs($socket, base64_encode($password) . "\r\n");
        $response = fgets($socket);
        if (strpos($response, '235') !== 0) {
            fclose($socket);
            return ['success' => false, 'message' => "Authentication failed: {$response}"];
        }
        
        // Send email
        fputs($socket, "MAIL FROM: <{$from}>\r\n");
        $response = fgets($socket);
        
        fputs($socket, "RCPT TO: <{$to}>\r\n");
        $response = fgets($socket);
        
        fputs($socket, "DATA\r\n");
        $response = fgets($socket);
        
        // Email headers and body
        $email_data = "From: {$from_name} <{$from}>\r\n";
        $email_data .= "To: {$to}\r\n";
        $email_data .= "Subject: {$subject}\r\n";
        $email_data .= "MIME-Version: 1.0\r\n";
        $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_data .= "\r\n";
        $email_data .= $message;
        $email_data .= "\r\n.\r\n";
        
        fputs($socket, $email_data);
        $response = fgets($socket);
        
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        if (strpos($response, '250') === 0) {
            return ['success' => true, 'message' => "Email sent successfully via proper SMTP!"];
        } else {
            return ['success' => false, 'message' => "Send failed: {$response}"];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "SMTP Error: " . $e->getMessage()];
    }
}

/**
 * Create proper test email
 */
function createProperTestEmail() {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #28a745; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .success-box { background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 PROPER SMTP SUCCESS!</h1>
            </div>
            <div class='content'>
                <div class='success-box'>
                    <h3>✅ Real SMTP Email Working!</h3>
                    <p>This email was sent using proper SMTP authentication with Gmail servers!</p>
                </div>
                <p><strong>Test Details:</strong></p>
                <ul>
                    <li>Sent via: smtp.gmail.com:587</li>
                    <li>Authentication: Gmail app password</li>
                    <li>Encryption: TLS</li>
                    <li>Time: " . date('Y-m-d H:i:s') . "</li>
                </ul>
                <p>Your booking notification emails should now work properly!</p>
            </div>
        </div>
    </body>
    </html>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Email Delivery</title>
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
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
        button { font-family: inherit; }
        button:hover { opacity: 0.9; transform: translateY(-1px); transition: all 0.3s ease; }
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
