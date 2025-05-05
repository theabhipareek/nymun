<?php
/**
 * Certificate Export Handler
 * 
 * Exports certificates to CSV format for download
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

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="nymun_certificates_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM (for Excel compatibility with non-ASCII characters)
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Add CSV header
fputcsv($output, [
    'id',
    'name',
    'certificate_id',
    'event',
    'position',
    'committee',
    'issue_date',
    'is_valid',
    'last_verified',
    'created_at',
    'updated_at'
]);

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
    
    // Get certificates
    $stmt = $pdo->query("SELECT * FROM {$config['table_name']} ORDER BY id");
    
    // Output each certificate as a CSV row
    while ($row = $stmt->fetch()) {
        fputcsv($output, $row);
    }
} catch (PDOException $e) {
    // Log error (don't expose to output)
    error_log("Database error: " . $e->getMessage());
    
    // Output error as CSV comment
    fputcsv($output, ["# Error: Unable to retrieve certificates"]);
}

// Close output stream
fclose($output);
exit;
