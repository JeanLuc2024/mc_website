<?php
/**
 * Test Admin Login
 * 
 * This script tests the admin login functionality
 */

echo "<h2>🧪 Testing Admin Login System...</h2>";

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mc_website;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 1: Check if admin_users table exists
echo "<h3>1. Checking admin_users table...</h3>";

try {
    $stmt = $pdo->query("DESCRIBE admin_users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p style='color: green;'>✅ admin_users table exists</p>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>📋 Table Structure:</h4>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>" . $column['Field'] . ":</strong> " . $column['Type'] . "</li>";
    }
    echo "</ul>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ admin_users table does not exist: " . $e->getMessage() . "</p>";
    echo "<p><strong>Solution:</strong> <a href='create_admin_user.php' target='_blank'>Create admin user table</a></p>";
    exit;
}

// Test 2: Check for admin users
echo "<h3>2. Checking for admin users...</h3>";

try {
    $stmt = $pdo->query("SELECT id, username, full_name, role, is_active, created_at FROM admin_users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<p style='color: orange;'>⚠️ No admin users found</p>";
        echo "<p><strong>Solution:</strong> <a href='create_admin_user.php' target='_blank'>Create admin user</a></p>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($users) . " admin user(s)</p>";
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>👥 Admin Users:</h4>";
        echo "<table style='width: 100%; border-collapse: collapse; font-size: 14px;'>";
        echo "<tr style='background: #e9ecef;'>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>ID</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Username</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Full Name</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Role</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Status</th>";
        echo "</tr>";
        
        foreach ($users as $user) {
            $status_color = $user['is_active'] ? '#28a745' : '#dc3545';
            $status_text = $user['is_active'] ? 'Active' : 'Inactive';
            
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$user['id']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'><strong>{$user['username']}</strong></td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$user['full_name']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$user['role']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd; color: {$status_color};'>{$status_text}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error checking admin users: " . $e->getMessage() . "</p>";
}

// Test 3: Test login with default credentials
echo "<h3>3. Testing login with default credentials...</h3>";

$test_username = 'admin';
$test_password = 'admin123';

try {
    $sql = "SELECT id, username, password, full_name, role FROM admin_users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$test_username]);
    
    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p style='color: green;'>✅ User 'admin' found in database</p>";
        
        // Test password verification
        $password_valid = false;
        $password_type = '';
        
        // Check if it's an MD5 hash (32 characters) or password_hash
        if (strlen($user['password']) === 32) {
            // MD5 password verification
            $password_valid = (md5($test_password) === $user['password']);
            $password_type = 'MD5';
        } else {
            // PHP password_hash verification
            $password_valid = password_verify($test_password, $user['password']);
            $password_type = 'password_hash';
        }
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>🔐 Password Test Results:</h4>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> {$user['username']}</li>";
        echo "<li><strong>Password Type:</strong> {$password_type}</li>";
        echo "<li><strong>Password Length:</strong> " . strlen($user['password']) . " characters</li>";
        echo "<li><strong>Test Password:</strong> {$test_password}</li>";
        echo "<li><strong>Password Valid:</strong> " . ($password_valid ? 'YES' : 'NO') . "</li>";
        echo "</ul>";
        echo "</div>";
        
        if ($password_valid) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
            echo "<h4>✅ LOGIN TEST SUCCESSFUL!</h4>";
            echo "<p>You should be able to login with:</p>";
            echo "<ul>";
            echo "<li><strong>Username:</strong> admin</li>";
            echo "<li><strong>Password:</strong> admin123</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;'>";
            echo "<h4>❌ LOGIN TEST FAILED!</h4>";
            echo "<p>Password verification failed. The password might be corrupted.</p>";
            echo "<p><strong>Solution:</strong> <a href='create_admin_user.php' target='_blank'>Reset admin password</a></p>";
            echo "</div>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ User 'admin' not found in database</p>";
        echo "<p><strong>Solution:</strong> <a href='create_admin_user.php' target='_blank'>Create admin user</a></p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error testing login: " . $e->getMessage() . "</p>";
}

// Test 4: Check PHP session functionality
echo "<h3>4. Testing PHP session functionality...</h3>";

session_start();

if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>✅ PHP sessions are working</p>";
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
} else {
    echo "<p style='color: red;'>❌ PHP sessions are not working</p>";
}

// Test 5: Check admin login page
echo "<h3>5. Checking admin login page...</h3>";

$admin_login_path = '../admin/index.php';
if (file_exists($admin_login_path)) {
    echo "<p style='color: green;'>✅ Admin login page exists</p>";
    
    // Check if the file contains the login form
    $content = file_get_contents($admin_login_path);
    
    if (strpos($content, 'admin_users') !== false) {
        echo "<p style='color: green;'>✅ Login page references admin_users table</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Login page might not be using admin_users table</p>";
    }
    
    if (strpos($content, 'password_verify') !== false || strpos($content, 'md5') !== false) {
        echo "<p style='color: green;'>✅ Login page has password verification</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Login page might not have proper password verification</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Admin login page not found</p>";
}

// Summary and troubleshooting
echo "<h3>📋 Troubleshooting Summary</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🔧 Common Login Issues and Solutions:</h4>";

echo "<h5>1. 'Invalid username or password' error:</h5>";
echo "<ul>";
echo "<li>Use the default credentials: <strong>admin</strong> / <strong>admin123</strong></li>";
echo "<li>If that doesn't work, <a href='create_admin_user.php' target='_blank'>reset the admin password</a></li>";
echo "<li>Make sure you're typing the credentials correctly (case-sensitive)</li>";
echo "</ul>";

echo "<h5>2. Database connection errors:</h5>";
echo "<ul>";
echo "<li>Make sure XAMPP MySQL is running</li>";
echo "<li>Check that the 'mc_website' database exists</li>";
echo "<li>Verify database credentials in config.php</li>";
echo "</ul>";

echo "<h5>3. Page not loading or errors:</h5>";
echo "<ul>";
echo "<li>Make sure XAMPP Apache is running</li>";
echo "<li>Clear browser cache and cookies</li>";
echo "<li>Try accessing: <a href='../admin/' target='_blank'>http://localhost/mc_website/admin/</a></li>";
echo "</ul>";

echo "<h5>4. Session issues:</h5>";
echo "<ul>";
echo "<li>Clear browser cookies for localhost</li>";
echo "<li>Try a different browser or incognito mode</li>";
echo "<li>Restart XAMPP Apache service</li>";
echo "</ul>";

echo "</div>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>🚀 Quick Fix Steps:</h4>";
echo "<ol>";
echo "<li><strong>Reset Admin User:</strong> <a href='create_admin_user.php' target='_blank'>Click here to reset admin user</a></li>";
echo "<li><strong>Try Login:</strong> <a href='../admin/' target='_blank'>Go to admin login page</a></li>";
echo "<li><strong>Use Credentials:</strong> admin / admin123</li>";
echo "<li><strong>Clear Cache:</strong> If still not working, clear browser cache</li>";
echo "</ol>";
echo "</div>";

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Admin Login</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1000px; 
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
        table { font-size: 14px; }
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
