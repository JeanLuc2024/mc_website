<?php
/**
 * Debug Admin Users
 * 
 * This script shows what admin users exist in the database and helps debug login issues.
 */

require_once 'config.php';

// Get database connection
$conn = connectDB();

if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

echo "<h2>Admin Users Debug Information</h2>";

try {
    // Check if admin_users table exists
    $table_check = "SHOW TABLES LIKE 'admin_users'";
    $table_result = $conn->query($table_check);
    
    if ($table_result->rowCount() == 0) {
        echo "<p style='color: red;'>❌ admin_users table does not exist!</p>";
        echo "<p>Please run the setup script first: <a href='create_admin_user.php'>create_admin_user.php</a></p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ admin_users table exists</p>";
    
    // Get all admin users
    $sql = "SELECT id, username, password, full_name, email, role, is_active, created_at, last_login FROM admin_users";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<p style='color: orange;'>⚠️ No admin users found in database!</p>";
        echo "<p>Please create an admin user: <a href='create_admin_user.php'>create_admin_user.php</a></p>";
    } else {
        echo "<h3>Found " . count($users) . " admin user(s):</h3>";
        
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th>ID</th><th>Username</th><th>Password Hash</th><th>Full Name</th><th>Email</th><th>Role</th><th>Active</th><th>Created</th><th>Last Login</th>";
        echo "</tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($user['username']) . "</strong></td>";
            echo "<td style='font-family: monospace; font-size: 12px;'>" . substr($user['password'], 0, 20) . "...</td>";
            echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . ($user['is_active'] ? '✓' : '✗') . "</td>";
            echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
            echo "<td>" . ($user['last_login'] ? htmlspecialchars($user['last_login']) : 'Never') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test password verification for each user
        echo "<h3>Password Verification Test:</h3>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        
        foreach ($users as $user) {
            echo "<h4>Testing user: " . htmlspecialchars($user['username']) . "</h4>";
            
            // Test common passwords
            $test_passwords = ['admin123', 'valentin123', 'password', 'secret'];
            
            foreach ($test_passwords as $test_password) {
                $password_valid = false;
                $hash_type = '';
                
                // Check if it's an MD5 hash (32 characters)
                if (strlen($user['password']) === 32) {
                    $password_valid = (md5($test_password) === $user['password']);
                    $hash_type = 'MD5';
                } else {
                    $password_valid = password_verify($test_password, $user['password']);
                    $hash_type = 'password_hash';
                }
                
                if ($password_valid) {
                    echo "<p style='color: green;'>✓ Password '<strong>" . $test_password . "</strong>' works! (Hash type: " . $hash_type . ")</p>";
                } else {
                    echo "<p style='color: #ccc;'>✗ Password '" . $test_password . "' doesn't work</p>";
                }
            }
            echo "<hr style='margin: 15px 0;'>";
        }
        echo "</div>";
    }
    
    // Show login instructions
    echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
    echo "<h3>🔑 How to Login:</h3>";
    echo "<ol>";
    echo "<li><strong>Go to admin panel:</strong> <a href='../admin/' target='_blank'>http://localhost/mc_website/admin/</a></li>";
    echo "<li><strong>Use the credentials shown above</strong> (where password verification shows ✓)</li>";
    echo "<li><strong>If no passwords work,</strong> run: <a href='create_admin_user.php'>create_admin_user.php</a></li>";
    echo "</ol>";
    echo "</div>";
    
    // Show quick fix options
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
    echo "<h3>🔧 Quick Fix Options:</h3>";
    
    if (!empty($users)) {
        $first_user = $users[0];
        echo "<p><strong>Option 1: Reset password for existing user</strong></p>";
        echo "<form method='POST' style='margin: 10px 0;'>";
        echo "<input type='hidden' name='action' value='reset_password'>";
        echo "<input type='hidden' name='user_id' value='" . $first_user['id'] . "'>";
        echo "<label>New password for '" . htmlspecialchars($first_user['username']) . "':</label><br>";
        echo "<input type='text' name='new_password' value='admin123' style='padding: 5px; margin: 5px 0;'>";
        echo "<button type='submit' style='padding: 5px 15px; background: #007bff; color: white; border: none; border-radius: 4px; margin-left: 10px;'>Reset Password</button>";
        echo "</form>";
    }
    
    echo "<p><strong>Option 2: Create new admin user</strong></p>";
    echo "<a href='create_admin_user.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Create Admin User</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    $user_id = (int)$_POST['user_id'];
    $new_password = $_POST['new_password'];
    
    try {
        $hashed_password = md5($new_password); // Using MD5 for simplicity
        $update_sql = "UPDATE admin_users SET password = :password WHERE id = :id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':password', $hashed_password);
        $update_stmt->bindParam(':id', $user_id);
        
        if ($update_stmt->execute()) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
            echo "<h3>✅ Password Reset Successful!</h3>";
            echo "<p>Password has been reset to: <strong>" . htmlspecialchars($new_password) . "</strong></p>";
            echo "<p><a href='../admin/' target='_blank'>Try logging in now</a></p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Failed to reset password</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error resetting password: " . $e->getMessage() . "</p>";
    }
}

// Close connection
$conn = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Admin Users</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1000px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        table { background: white; }
        th { background: #f8f9fa !important; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
</body>
</html>
