<?php
/**
 * Certificate Import Handler
 * 
 * Process batch certificate imports from CSV/Excel files
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

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Function to validate certificate ID
function isValidCertificateId($id) {
    return preg_match("/^[A-Za-z0-9]{6}$/", $id);
}

// Check if file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "File upload error: " . $file['error'];
        header("Location: admin_dashboard.php");
        exit;
    }
    
    // Check file type
    $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
    if ($fileType !== 'csv' && $fileType !== 'xlsx') {
        $_SESSION['error'] = "Invalid file type. Please upload a CSV or Excel file.";
        header("Location: admin_dashboard.php");
        exit;
    }
    
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
        
        // Process CSV file
        if ($fileType === 'csv') {
            // Open file
            $handle = fopen($file['tmp_name'], 'r');
            
            if ($handle === false) {
                throw new Exception("Failed to open file");
            }
            
            // Skip header row
            $header = fgetcsv($handle);
            
            // Check if header has required columns
            $requiredColumns = ['name', 'certificate_id', 'event', 'issue_date'];
            $headerMap = [];
            
            foreach ($requiredColumns as $column) {
                $index = array_search($column, $header);
                if ($index === false) {
                    throw new Exception("Required column '$column' not found in CSV header");
                }
                $headerMap[$column] = $index;
            }
            
            // Optional columns
            $optionalColumns = ['position', 'committee', 'is_valid'];
            foreach ($optionalColumns as $column) {
                $index = array_search($column, $header);
                $headerMap[$column] = $index !== false ? $index : null;
            }
            
            // Process rows
            $imported = 0;
            $errors = [];
            
            while (($row = fgetcsv($handle)) !== false) {
                // Get values from row
                $name = $row[$headerMap['name']];
                $certificateId = $row[$headerMap['certificate_id']];
                $event = $row[$headerMap['event']];
                $issueDate = $row[$headerMap['issue_date']];
                
                $position = $headerMap['position'] !== null && isset($row[$headerMap['position']]) ? $row[$headerMap['position']] : '';
                $committee = $headerMap['committee'] !== null && isset($row[$headerMap['committee']]) ? $row[$headerMap['committee']] : '';
                $isValid = $headerMap['is_valid'] !== null && isset($row[$headerMap['is_valid']]) ? (int)$row[$headerMap['is_valid']] : 1;
                
                // Validate required fields
                if (empty($name) || empty($certificateId) || empty($event) || empty($issueDate)) {
                    $errors[] = "Row with certificate ID '$certificateId' has missing required fields";
                    continue;
                }
                
                // Validate certificate ID
                if (!isValidCertificateId($certificateId)) {
                    $errors[] = "Certificate ID '$certificateId' is invalid (must be 6 alphanumeric characters)";
                    continue;
                }
                
                // Check if certificate ID already exists
                $stmt = $pdo->prepare("SELECT id FROM {$config['table_name']} WHERE certificate_id = :certificate_id");
                $stmt->bindParam(':certificate_id', $certificateId, PDO::PARAM_STR);
                $stmt->execute();
                $existingId = $stmt->fetchColumn();
                
                if ($existingId) {
                    // Update existing certificate
                    $stmt = $pdo->prepare("UPDATE {$config['table_name']} SET 
                                          name = :name, 
                                          event = :event, 
                                          position = :position, 
                                          committee = :committee, 
                                          issue_date = :issue_date, 
                                          is_valid = :is_valid,
                                          updated_at = NOW()
                                          WHERE id = :id");
                    
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':event', $event, PDO::PARAM_STR);
                    $stmt->bindParam(':position', $position, PDO::PARAM_STR);
                    $stmt->bindParam(':committee', $committee, PDO::PARAM_STR);
                    $stmt->bindParam(':issue_date', $issueDate, PDO::PARAM_STR);
                    $stmt->bindParam(':is_valid', $isValid, PDO::PARAM_INT);
                    $stmt->bindParam(':id', $existingId, PDO::PARAM_INT);
                    
                    $stmt->execute();
                } else {
                    // Insert new certificate
                    $stmt = $pdo->prepare("INSERT INTO {$config['table_name']} 
                                          (name, certificate_id, event, position, committee, issue_date, is_valid, created_at) 
                                          VALUES (:name, :certificate_id, :event, :position, :committee, :issue_date, :is_valid, NOW())");
                    
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':certificate_id', $certificateId, PDO::PARAM_STR);
                    $stmt->bindParam(':event', $event, PDO::PARAM_STR);
                    $stmt->bindParam(':position', $position, PDO::PARAM_STR);
                    $stmt->bindParam(':committee', $committee, PDO::PARAM_STR);
                    $stmt->bindParam(':issue_date', $issueDate, PDO::PARAM_STR);
                    $stmt->bindParam(':is_valid', $isValid, PDO::PARAM_INT);
                    
                    $stmt->execute();
                }
                
                $imported++;
            }
            
            fclose($handle);
            
            // Set session message
            if ($imported > 0) {
                $_SESSION['message'] = "Successfully imported $imported certificates";
                if (!empty($errors)) {
                    $_SESSION['message'] .= ". There were " . count($errors) . " errors.";
                    $_SESSION['import_errors'] = $errors;
                }
            } else {
                $_SESSION['error'] = "No certificates were imported. " . implode(" ", $errors);
            }
        } else {
            // Process Excel file (XLSX)
            // In a real implementation, this would use a library like PhpSpreadsheet
            $_SESSION['error'] = "Excel file import is not implemented in this example";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Redirect back to dashboard
header("Location: admin_dashboard.php");
exit;
