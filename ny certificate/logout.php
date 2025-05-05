<?php
/**
 * Admin Logout
 * 
 * Handles user logout by destroying the session
 * 
 * Current UTC Time: 2025-05-05 16:56:17
 * User: theabhipareek
 */

session_start();

// Log the logout
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && isset($_SESSION['admin_username'])) {
    $username = $_SESSION['admin_username'];
    $logFile = 'data/admin_log.txt';
    
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    $logEntry = date("Y-m-d H:i:s") . " | Logout | Username: $username | IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: admin.php");
exit;
