<?php
/**
 * Create Admin User Script
 * 
 * This script creates the admin user account for the MC website admin panel.
 */

require_once 'config.php';

// Get database connection
$conn = connectDB();

if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

echo "<h2>Creating Admin User Account...</h2>";

try {
    // First, create the admin_users table if it doesn't exist
    echo "<p>Creating admin_users table...</p>";
    $admin_users_sql = "
    CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        role ENUM('admin', 'manager') DEFAULT 'admin',
        is_active BOOLEAN DEFAULT TRUE,
        last_login DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_email (email)
    )";
    $conn->exec($admin_users_sql);
    echo "<p style='color: green;'>✓ Admin users table created successfully.</p>";

    // Check if admin user already exists
    $check_sql = "SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute();
    $result = $check_stmt->fetch();

    if ($result['count'] > 0) {
        echo "<p style='color: orange;'>⚠️ Admin user already exists. Updating password...</p>";
        
        // Update existing admin user
        $update_sql = "UPDATE admin_users SET password = :password, email = :email, updated_at = NOW() WHERE username = 'admin'";
        $update_stmt = $conn->prepare($update_sql);
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $update_stmt->bindParam(':password', $hashed_password);
        $update_stmt->bindParam(':email', ADMIN_EMAIL);
        $update_stmt->execute();
        
        echo "<p style='color: green;'>✓ Admin user updated successfully.</p>";
    } else {
        echo "<p>Creating new admin user...</p>";
        
        // Create new admin user
        $insert_sql = "INSERT INTO admin_users (username, password, full_name, email, role) VALUES (:username, :password, :full_name, :email, :role)";
        $insert_stmt = $conn->prepare($insert_sql);
        
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $full_name = 'Byiringiro Valentin';
        $email = ADMIN_EMAIL; // This will use izabayojeanlucseverin@gmail.com
        $role = 'admin';
        
        $insert_stmt->bindParam(':username', $username);
        $insert_stmt->bindParam(':password', $password);
        $insert_stmt->bindParam(':full_name', $full_name);
        $insert_stmt->bindParam(':email', $email);
        $insert_stmt->bindParam(':role', $role);
        
        $insert_stmt->execute();
        echo "<p style='color: green;'>✓ Admin user created successfully.</p>";
    }

    // Create a second admin user with your email as username (alternative login)
    echo "<p>Creating alternative admin user with your email...</p>";
    
    $check_email_sql = "SELECT COUNT(*) as count FROM admin_users WHERE username = 'valentin' OR email = :email";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bindParam(':email', ADMIN_EMAIL);
    $check_email_stmt->execute();
    $email_result = $check_email_stmt->fetch();

    if ($email_result['count'] == 0) {
        $insert_email_sql = "INSERT INTO admin_users (username, password, full_name, email, role) VALUES (:username, :password, :full_name, :email, :role)";
        $insert_email_stmt = $conn->prepare($insert_email_sql);
        
        $username2 = 'valentin';
        $password2 = password_hash('valentin123', PASSWORD_DEFAULT);
        $full_name2 = 'Byiringiro Valentin';
        $email2 = ADMIN_EMAIL;
        $role2 = 'admin';
        
        $insert_email_stmt->bindParam(':username', $username2);
        $insert_email_stmt->bindParam(':password', $password2);
        $insert_email_stmt->bindParam(':full_name', $full_name2);
        $insert_email_stmt->bindParam(':email', $email2);
        $insert_email_stmt->bindParam(':role', $role2);
        
        $insert_email_stmt->execute();
        echo "<p style='color: green;'>✓ Alternative admin user 'valentin' created successfully.</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Alternative admin user already exists.</p>";
    }

    echo "<h3 style='color: green;'>🎉 Admin User Setup Complete!</h3>";
    
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h4>🔑 Your Admin Login Credentials:</h4>";
    echo "<p><strong>Option 1:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> admin</li>";
    echo "<li><strong>Password:</strong> admin123</li>";
    echo "</ul>";
    
    echo "<p><strong>Option 2:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> valentin</li>";
    echo "<li><strong>Password:</strong> valentin123</li>";
    echo "</ul>";
    
    echo "<p><strong>Admin Panel URL:</strong></p>";
    echo "<ul>";
    echo "<li><a href='../admin/' target='_blank'>http://localhost/mc_website/admin/</a></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
    echo "<h4>⚠️ Important Security Notes:</h4>";
    echo "<ul>";
    echo "<li><strong>Change these passwords immediately</strong> after first login</li>";
    echo "<li>Go to <strong>Profile</strong> section in admin panel to update your password</li>";
    echo "<li>Use strong passwords for production use</li>";
    echo "<li>Your admin email is set to: <strong>" . ADMIN_EMAIL . "</strong></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
    echo "<h4>📋 Next Steps:</h4>";
    echo "<ol>";
    echo "<li><a href='../admin/' target='_blank'><strong>Login to Admin Panel</strong></a></li>";
    echo "<li><strong>Change your password</strong> in Profile section</li>";
    echo "<li><strong>Test the booking system</strong> by making a test booking</li>";
    echo "<li><strong>Test email notifications</strong> by sending yourself an email</li>";
    echo "<li><strong>Customize email templates</strong> to match your style</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p style='text-align: center; margin: 30px 0;'>";
    echo "<a href='../admin/' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;'>🚀 Go to Admin Panel</a>";
    echo "</p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure:</p>";
    echo "<ul>";
    echo "<li>XAMPP is running (Apache + MySQL)</li>";
    echo "<li>Database 'mc_website' exists</li>";
    echo "<li>Database connection settings are correct in config.php</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Close connection
$conn = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Admin User</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2 { color: #2c3e50; }
        h3 { color: #27ae60; }
        h4 { color: #2c3e50; margin-bottom: 10px; }
        p { line-height: 1.6; }
        ul, ol { line-height: 1.8; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
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
