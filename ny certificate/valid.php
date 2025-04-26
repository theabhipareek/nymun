<?php
// Enable strict error reporting
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Validate parameters
$allowedParams = ['id','name','issued_on','venue','award','committee','position','country','certificate_image'];
$rawData = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS);

// Validate parameters
if (empty($rawData) || array_diff($allowedParams, array_keys($rawData))) {
    header("Location: index.html");
    exit();
}

// Validate date format
try {
    $issueDate = new DateTime($rawData['issued_on']);
} catch (Exception $e) {
    header("Location: invalid.html");
    exit();
}

// Prepare display data
$displayData = [];
foreach ($allowedParams as $param) {
    $displayData[$param] = htmlspecialchars($rawData[$param] ?? '', ENT_QUOTES, 'UTF-8');
}

// Validate certificate image
$imagePath = __DIR__ . "/assets/images/certificates/{$displayData['certificate_image']}";
if (!file_exists($imagePath)) {
    header("Location: invalid.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valid Certificate | Global MUN</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'">
</head>
<body>
    <div class="container">
        <div class="verification-card">
            <div class="header">
                <img src="assets/images/logo.png" alt="MUN Logo" class="logo">
                <h1>✅ Valid Certificate Verified</h1>
                <p>Issued by Global Model United Nations 2024</p>
            </div>

            <div class="valid-certificate">
                <div class="certificate-image">
                    <img src="assets/images/certificates/<?= $displayData['certificate_image'] ?>" 
                         alt="Certificate Image">
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <i class="fas fa-user"></i>
                        <strong>Name:</strong> <?= $displayData['name'] ?>
                    </div>
                    
                    <div class="detail-item">
                        <i class="fas fa-award"></i>
                        <strong>Award:</strong> <?= $displayData['award'] ?>
                    </div>
                    
                    <div class="detail-item">
                        <i class="fas fa-calendar-day"></i>
                        <strong>Issued On:</strong> <?= date('F j, Y', strtotime($displayData['issued_on'])) ?>
                    </div>
                    
                    <div class="detail-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <strong>Venue:</strong> <?= $displayData['venue'] ?>
                    </div>
                    
                    <div class="detail-item">
                        <i class="fas fa-users"></i>
                        <strong>Committee:</strong> <?= $displayData['committee'] ?>
                    </div>
                    
                    <div class="detail-item">
                        <i class="fas fa-briefcase"></i>
                        <strong>Position:</strong> <?= $displayData['position'] ?>
                    </div>
                    
                    <div class="detail-item">
                        <i class="fas fa-flag"></i>
                        <strong>Country:</strong> <?= $displayData['country'] ?>
                    </div>
                    
                    <div class="detail-item">
                        <i class="fas fa-id-card"></i>
                        <strong>Certificate ID:</strong> <?= $displayData['id'] ?>
                    </div>
                </div>

                <div class="security-note">
                    <i class="fas fa-shield-alt"></i>
                    This certificate was successfully verified on <?= date('F j, Y') ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>