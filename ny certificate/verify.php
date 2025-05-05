<?php
/**
 * Certificate Verification Handler
 * 
 * Processes certificate verification requests, validates input data,
 * checks database records, and returns verification results
 */

// Start session for potential admin authentication later
session_start();

// Configure error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users

// Set security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self';");

// Database configuration
$config = [
    'db_host' => 'localhost',
    'db_name' => 'nymun_certificates',
    'db_user' => 'nymun_user',
    'db_pass' => 'your_secure_password_here', // Change this in production
    'table_name' => 'certificates',
    'log_file' => 'data/verification_log.txt'
];

// Determine if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Function to log verification attempts
function logVerification($config, $name, $certificate_id, $isValid, $ip) {
    $timestamp = date("Y-m-d H:i:s");
    $logEntry = "$timestamp | IP: $ip | Name: $name | Certificate ID: $certificate_id | Result: " . ($isValid ? "VALID" : "INVALID") . "\n";
    
    // Ensure log directory exists
    if (!file_exists(dirname($config['log_file']))) {
        mkdir(dirname($config['log_file']), 0755, true);
    }
    
    // Append to log file
    file_put_contents($config['log_file'], $logEntry, FILE_APPEND);
}

// Main verification processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $certificate_id = isset($_POST['certificate_id']) ? sanitizeInput($_POST['certificate_id']) : '';
    $event = isset($_POST['event']) ? sanitizeInput($_POST['event']) : '';
    $captcha = isset($_POST['captcha']) ? $_POST['captcha'] : '';
    
    // Basic validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    } elseif (!preg_match("/^[A-Za-z\s]{3,50}$/", $name)) {
        $errors[] = "Name must be 3-50 characters and contain only letters";
    }
    
    if (empty($certificate_id)) {
        $errors[] = "Certificate ID is required";
    } elseif (!preg_match("/^[A-Za-z0-9]{6}$/", $certificate_id)) {
        $errors[] = "Certificate ID must be 6 alphanumeric characters";
    }
    
    // In a real implementation, validate captcha against session value
    // For this example, we'll just assume captcha validation passed
    
    // Prepare response data
    $response = [
        'status' => 'error',
        'message' => '',
        'certificate' => null
    ];
    
    if (!empty($errors)) {
        $response['message'] = implode(", ", $errors);
    } else {
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
            
            // Prepare query to find certificate
            $query = "SELECT * FROM {$config['table_name']} WHERE certificate_id = :certificate_id";
            if (!empty($event)) {
                $query .= " AND event = :event";
            }
            
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':certificate_id', $certificate_id, PDO::PARAM_STR);
            
            if (!empty($event)) {
                $stmt->bindParam(':event', $event, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $certificate = $stmt->fetch();
            
            // For demonstration purposes, simulate a database record
            // In production, use the actual database record from above
            if (!$certificate) {
                // This is for simulation only - remove in production and use real DB data
                if ($certificate_id === 'ABC123') {
                    $certificate = [
                        'id' => 'ABC123',
                        'name' => 'John Doe',
                        'issue_date' => '2023-05-15',
                        'event' => 'NYMUN 2023',
                        'position' => 'Delegate',
                        'committee' => 'Security Council',
                        'is_valid' => 1,
                        'download_url' => '#'
                    ];
                }
            }
            
            // Log the verification attempt
            $ip = $_SERVER['REMOTE_ADDR'];
            $isValid = false;
            
            if ($certificate && strtolower($certificate['name']) === strtolower($name)) {
                // Certificate found and name matches
                $isValid = $certificate['is_valid'] == 1;
                
                if ($isValid) {
                    $response['status'] = 'success';
                    $response['message'] = 'Certificate verified successfully';
                    $response['certificate'] = $certificate;
                    
                    // Update verification time in database
                    $updateStmt = $pdo->prepare("UPDATE {$config['table_name']} SET last_verified = NOW() WHERE certificate_id = :certificate_id");
                    $updateStmt->bindParam(':certificate_id', $certificate_id, PDO::PARAM_STR);
                    $updateStmt->execute();
                } else {
                    $response['message'] = 'This certificate has been revoked';
                }
            } else {
                $response['message'] = 'Certificate not found or details do not match';
            }
            
            logVerification($config, $name, $certificate_id, $isValid, $ip);
            
        } catch (PDOException $e) {
            // Log the error (don't expose to users)
            error_log("Database error: " . $e->getMessage());
            $response['message'] = "A system error occurred. Please try again later.";
        }
    }
    
    // Return response based on request type
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // For non-AJAX requests, redirect to appropriate page
        if ($response['status'] === 'success') {
            $_SESSION['verification_result'] = $response;
            header("Location: valid.php");
        } else {
            $_SESSION['verification_result'] = $response;
            header("Location: invalid.php");
        }
        exit;
    }
}

// If not POST or no form submitted, redirect to index
header("Location: index.php");
exit;
