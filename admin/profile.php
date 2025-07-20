<?php
/**
 * Admin Profile Page
 * 
 * This page allows the admin to view and update their profile information.
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page
    header('Location: index.php');
    exit;
}

// Include database configuration
require_once '../php/config.php';

// Initialize variables
$success_message = '';
$error_message = '';
$admin_data = [];

// Get database connection
$conn = connectDB();

// Check if database connection was successful
if ($conn) {
    try {
        // Get admin data
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['admin_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() === 1) {
            $admin_data = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error_message = "Admin user not found.";
        }
        
    } catch(PDOException $e) {
        error_log("Profile page error: " . $e->getMessage());
        $error_message = "An error occurred while retrieving your profile data.";
    }
}

// Process profile update if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = isset($_POST['full_name']) ? sanitizeInput($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    
    // Validate input
    if (empty($full_name) || empty($email)) {
        $error_message = "Please fill in all required fields.";
    } elseif (!validateEmail($email)) {
        $error_message = "Please enter a valid email address.";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE admin_users SET full_name = :full_name, email = :email, updated_at = NOW() WHERE id = :id");
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $_SESSION['admin_id']);
            $stmt->execute();
            
            // Update session data
            $_SESSION['admin_name'] = $full_name;
            
            // Update admin_data for display
            $admin_data['full_name'] = $full_name;
            $admin_data['email'] = $email;
            
            $success_message = "Profile updated successfully!";
            
        } catch(PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            $error_message = "An error occurred while updating your profile.";
        }
    }
}

// Process password change if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Please fill in all password fields.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New password and confirmation do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "New password must be at least 8 characters long.";
    } else {
        try {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = :id");
            $stmt->bindParam(':id', $_SESSION['admin_id']);
            $stmt->execute();
            
            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify current password - handle both MD5 and password_hash formats
                $password_valid = false;

                // Check if it's an MD5 hash (32 characters)
                if (strlen($user['password']) === 32) {
                    $password_valid = (md5($current_password) === $user['password']);
                } else {
                    $password_valid = password_verify($current_password, $user['password']);
                }

                if ($password_valid) {
                    // Hash new password using MD5 for consistency
                    $hashed_password = md5($new_password);

                    // Update password
                    $stmt = $conn->prepare("UPDATE admin_users SET password = :password, updated_at = NOW() WHERE id = :id");
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':id', $_SESSION['admin_id']);
                    $stmt->execute();

                    $success_message = "Password changed successfully!";
                } else {
                    $error_message = "Current password is incorrect.";
                }
            } else {
                $error_message = "Admin user not found.";
            }
            
        } catch(PDOException $e) {
            error_log("Password change error: " . $e->getMessage());
            $error_message = "An error occurred while changing your password.";
        }
    }
}

// Close connection
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | Byiringiro Valentin MC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>ValentinMC</h2>
                <p>Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="bookings.php">
                            <i class="fas fa-calendar-check"></i>
                            <span>Bookings</span>
                        </a>
                    </li>
                    <li>
                        <a href="contacts.php">
                            <i class="fas fa-envelope"></i>
                            <span>Contacts</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="profile.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <p>&copy; 2025 Byiringiro Valentin</p>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="header-left">
                    <button id="sidebar-toggle" class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>My Profile</h1>
                </div>
                
                <div class="header-right">
                    <div class="admin-profile">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                        <div class="profile-img">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <!-- Notification for updates -->
                <?php if (!empty($success_message)): ?>
                    <div class="notification success">
                        <i class="fas fa-check-circle"></i>
                        <p><?php echo $success_message; ?></p>
                        <button class="close-notification"><i class="fas fa-times"></i></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="notification error">
                        <i class="fas fa-exclamation-circle"></i>
                        <p><?php echo $error_message; ?></p>
                        <button class="close-notification"><i class="fas fa-times"></i></button>
                    </div>
                <?php endif; ?>
                
                <div class="profile-container">
                    <!-- Profile Information -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h2><i class="fas fa-user"></i> Profile Information</h2>
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" value="<?php echo htmlspecialchars($admin_data['username'] ?? ''); ?>" disabled>
                                    <small>Username cannot be changed</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="full_name">Full Name</label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($admin_data['full_name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin_data['email'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <input type="text" id="role" value="<?php echo ucfirst($admin_data['role'] ?? ''); ?>" disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label for="last_login">Last Login</label>
                                    <input type="text" id="last_login" value="<?php echo isset($admin_data['last_login']) ? date('F j, Y, g:i a', strtotime($admin_data['last_login'])) : 'Never'; ?>" disabled>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn-primary">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h2><i class="fas fa-lock"></i> Change Password</h2>
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="POST">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" required>
                                    <small>Password must be at least 8 characters long</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn-primary">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Close notification
        const closeNotifications = document.querySelectorAll('.close-notification');
        closeNotifications.forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
        
        // Auto-hide notifications after 5 seconds
        setTimeout(function() {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                notification.style.display = 'none';
            });
        }, 5000);
        
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const adminContainer = document.querySelector('.admin-container');
        
        sidebarToggle.addEventListener('click', function() {
            adminContainer.classList.toggle('sidebar-collapsed');
        });
        
        // Password confirmation validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== newPasswordInput.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        newPasswordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value !== '' && confirmPasswordInput.value !== this.value) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
