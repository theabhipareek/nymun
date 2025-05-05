<?php
/**
 * Batch Certificate Actions
 * 
 * Process bulk actions on certificates (validate, invalidate, delete)
 * 
 * Current UTC Time: 2025-05-05 16:56:17
 * User: theabhipareek
 */

session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php");
    exit;
}

// Database configuration
$config = [
    'db_host' => 'localhost',
    'db_name' => 'nymun_certificates',
    'db_user' => 'nymun_user',
    'db_pass' => 'your_secure_password_here', // Change this in production
    'table_name' => 'certificates'
];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['certificates'])) {
    $action = $_POST['action'];
    $certificates = $_POST['certificates'];
    
    // Validate input
    if (!is_array($certificates) || empty($certificates)) {
        $_SESSION['error'] = "No certificates selected";
        header("Location: admin_dashboard.php");
        exit;
    }
    
    // Convert all IDs to integers
    $certificates = array_map('intval', $certificates);
    
    try {
        // Connect to database
        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        
        // Process action
        switch ($action) {
            case 'validate':
                // Mark certificates as valid
                $stmt = $pdo->prepare("UPDATE {$config['table_name']} SET is_valid = 1, updated_at = NOW() WHERE id IN (" . implode(',', array_fill(0, count($certificates), '?')) . ")");
                $stmt->execute($certificates);
                $_SESSION['message'] = count($certificates) . " certificates marked as valid";
                break;
                
            case 'invalidate':
                // Mark certificates as invalid
                $stmt = $pdo->prepare("UPDATE {$config['table_name']} SET is_valid = 0, updated_at = NOW() WHERE id IN (" . implode(',', array_fill(0, count($certificates), '?')) . ")");
                $stmt->execute($certificates);
                $_SESSION['message'] = count($certificates) . " certificates marked as invalid";
                break;
                
            case 'delete':
                // Delete certificates
                $stmt = $pdo->prepare("DELETE FROM {$config['table_name']} WHERE id IN (" . implode(',', array_fill(0, count($certificates), '?')) . ")");
                $stmt->execute($certificates);
                $_SESSION['message'] = count($certificates) . " certificates deleted";
                break;
                
            default:
                $_SESSION['error'] = "Invalid action";
                break;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
}

// Redirect back to dashboard
header("Location: admin_dashboard.php");
exit;
