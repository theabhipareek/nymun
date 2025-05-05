<?php
/**
 * Certificate Download Handler
 * 
 * Generates and provides a downloadable certificate PDF
 * 
 * Current UTC Time: 2025-05-05 16:56:17
 * User: theabhipareek
 */

session_start();

// Set proper headers for PDF download
header("Content-Type: application/pdf");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: 0");

// Get certificate ID from request
$certificate_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($certificate_id)) {
    // No certificate ID provided
    header("Location: index.php");
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
    
    // Find certificate
    $stmt = $pdo->prepare("SELECT * FROM {$config['table_name']} WHERE certificate_id = :certificate_id AND is_valid = 1");
    $stmt->bindParam(':certificate_id', $certificate_id, PDO::PARAM_STR);
    $stmt->execute();
    $certificate = $stmt->fetch();
    
    // For demo purposes, use example certificate if database lookup fails
    if (!$certificate && $certificate_id === 'ABC123') {
        $certificate = [
            'name' => 'John Doe',
            'certificate_id' => 'ABC123',
            'issue_date' => '2023-05-15',
            'event' => 'NYMUN 2023',
            'position' => 'Delegate',
            'committee' => 'Security Council'
        ];
    }
    
    if (!$certificate) {
        // Certificate not found or not valid
        header("Location: invalid.php");
        exit;
    }
    
    // Set filename for download
    $filename = "NYMUN_Certificate_" . $certificate_id . ".pdf";
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    // In a real implementation, this would generate a PDF certificate
    // For this example, we'll include a library like TCPDF or FPDF to generate the certificate
    
    // Here's a simple example using FPDF (you would need to include the library)
    /*
    require('fpdf/fpdf.php');
    
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    
    // Set certificate styles
    $pdf->SetAutoPageBreak(false);
    $pdf->SetFont('Arial', 'B', 24);
    
    // Add certificate content
    $pdf->Image('images/certificate_bg.jpg', 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
    $pdf->SetXY(0, 50);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell($pdf->GetPageWidth(), 20, 'CERTIFICATE OF PARTICIPATION', 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell($pdf->GetPageWidth(), 15, 'This certifies that', 0, 1, 'C');
    
    $pdf->SetFont('Arial', 'B', 28);
    $pdf->Cell($pdf->GetPageWidth(), 25, $certificate['name'], 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell($pdf->GetPageWidth(), 15, 'participated as', 0, 1, 'C');
    
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell($pdf->GetPageWidth(), 15, $certificate['position'], 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell($pdf->GetPageWidth(), 15, 'in the', 0, 1, 'C');
    
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell($pdf->GetPageWidth(), 15, $certificate['committee'], 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell($pdf->GetPageWidth(), 15, 'at the', 0, 1, 'C');
    
    $pdf->SetFont('Arial', 'B', 22);
    $pdf->Cell($pdf->GetPageWidth(), 20, $certificate['event'], 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(40, $pdf->GetPageHeight() - 60);
    $pdf->Cell(70, 10, 'Date: ' . date('F j, Y', strtotime($certificate['issue_date'])), 0, 0, 'C');
    
    $pdf->SetXY($pdf->GetPageWidth() - 110, $pdf->GetPageHeight() - 60);
    $pdf->Cell(70, 10, 'Certificate ID: ' . $certificate['certificate_id'], 0, 0, 'C');
    
    $pdf->SetXY(40, $pdf->GetPageHeight() - 40);
    $pdf->Cell(70, 10, '_____________________', 0, 0, 'C');
    $pdf->SetXY(40, $pdf->GetPageHeight() - 35);
    $pdf->Cell(70, 10, 'Secretary General', 0, 0, 'C');
    
    $pdf->SetXY($pdf->GetPageWidth() - 110, $pdf->GetPageHeight() - 40);
    $pdf->Cell(70, 10, '_____________________', 0, 0, 'C');
    $pdf->SetXY($pdf->GetPageWidth() - 110, $pdf->GetPageHeight() - 35);
    $pdf->Cell(70, 10, 'Director General', 0, 0, 'C');
    
    // Output PDF
    $pdf->Output('D', $filename);
    */
    
    // For this example, we'll just output a message since we don't have FPDF included
    echo "This is a placeholder for the PDF certificate. In a real implementation, this would generate a PDF with the certificate for: " . $certificate['name'];
    
} catch (PDOException $e) {
    // Database error
    echo "Error: " . $e->getMessage();
}
