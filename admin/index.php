<?php
/**
 * Admin Login Page
 * 
 * This file handles the admin login functionality.
 */

// Start session
session_start();

// Include database configuration
require_once '../php/config.php';

// Check if user is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Redirect to dashboard
    header('Location: dashboard.php');
    exit;
}

// Initialize error message
$error_message = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get database connection
    $conn = connectDB();
    
    // Check if database connection was successful
    if (!$conn) {
        $error_message = "Database connection error. Please try again later.";
    } else {
        // Get form data
        $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            $error_message = "Please enter both username and password.";
        } else {
            try {
                // Prepare SQL statement
                $sql = "SELECT id, username, password, full_name, role FROM admin_users WHERE username = :username";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                
                // Check if user exists
                if ($stmt->rowCount() === 1) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Verify password - handle both MD5 and password_hash formats
                    $password_valid = false;

                    // Check if it's an MD5 hash (32 characters)
                    if (strlen($user['password']) === 32) {
                        // MD5 password verification
                        $password_valid = (md5($password) === $user['password']);
                    } else {
                        // PHP password_hash verification
                        $password_valid = password_verify($password, $user['password']);
                    }

                    if ($password_valid) {
                        // Password is correct, set session variables
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_username'] = $user['username'];
                        $_SESSION['admin_name'] = $user['full_name'];
                        $_SESSION['admin_role'] = $user['role'];

                        // Update last login time
                        $update_sql = "UPDATE admin_users SET last_login = NOW() WHERE id = :id";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bindParam(':id', $user['id']);
                        $update_stmt->execute();

                        // Redirect to dashboard
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        $error_message = "Invalid username or password.";
                    }
                } else {
                    $error_message = "Invalid username or password.";
                }
            } catch(PDOException $e) {
                $error_message = "An error occurred. Please try again later.";
                error_log("Login error: " . $e->getMessage());
            }
        }
        
        // Close connection
        $conn = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Byiringiro Valentin MC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #8e44ad;
            --primary-dark: #7d3c98;
            --secondary-color: #2c3e50;
            --light-color: #f4f4f4;
            --dark-color: #333;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --white: #fff;
            --max-width: 1200px;
            --border-radius-sm: 4px;
            --border-radius-md: 8px;
            --border-radius-lg: 12px;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 8px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f9f9f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            width: 100%;
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-card {
            background-color: var(--white);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-md);
            width: 100%;
            max-width: 400px;
            padding: 30px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            font-size: 2.4rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: var(--secondary-color);
            font-size: 1.4rem;
        }
        
        .login-form .form-group {
            margin-bottom: 20px;
        }
        
        .login-form label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--secondary-color);
        }
        
        .login-form input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius-sm);
            font-size: 1.6rem;
            transition: border-color 0.3s;
        }
        
        .login-form input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .login-form button {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius-sm);
            font-size: 1.6rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-form button:hover {
            background-color: var(--primary-dark);
        }
        
        .error-message {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            padding: 10px;
            border-radius: var(--border-radius-sm);
            margin-bottom: 20px;
            font-size: 1.4rem;
            border-left: 4px solid var(--danger-color);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 1.4rem;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .login-card {
                max-width: 100%;
                margin: 0 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Admin Login</h1>
                <p>Byiringiro Valentin MC Services</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form class="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit">Login</button>
            </form>
            
            <div class="back-link">
                <a href="../index.html"><i class="fas fa-arrow-left"></i> Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>

