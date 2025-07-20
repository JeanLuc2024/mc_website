<?php
/**
 * Session Check API
 * 
 * This file checks if the admin session is still valid
 */

session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if session is valid
$response = [
    'logged_in' => isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true,
    'timestamp' => time()
];

// Return JSON response
echo json_encode($response);
?>
