<?php
/**
 * Admin Header Component
 * 
 * This file contains the header component for the admin panel.
 * It should be included in all admin pages for consistent navigation.
 */

// Check if user is logged in (security check)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page
    header('Location: index.php');
    exit;
}

// Get page title if not set
if (!isset($page_title)) {
    $page_title = 'Admin Dashboard';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | Byiringiro Valentin MC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="css/analytics-style.css">
    <script src="js/notifications.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php include_once 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="header-left">
                    <button id="sidebar-toggle" class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php echo $page_title; ?></h1>
                </div>
                
                <div class="header-right">
                    <!-- Notifications widget removed -->

                    <div class="admin-profile">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                        <div class="profile-img">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="content-body">