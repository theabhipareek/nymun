<?php
session_start();
if (!isset($_SESSION['certificate_data'])) {
    header("Location: index.php");
    exit();
}

$cert = $_SESSION['certificate_data'];
unset($_SESSION['certificate_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate Verified</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Certificate Verification Successful</h1>
        </div>
    </div>

    <div class="container">
        <div class="result-card">
            <div class="watermark">Powered By edXtra</div>
            
            <i class="fas fa-check-circle verified-badge"></i>
            <h2>Certificate Authenticated</h2>
            
            <div class="certificate-details">
                <div class="detail-item">
                    <span>Name:</span>
                    <strong><?= htmlspecialchars($cert['name']) ?></strong>
                </div>
                <div class="detail-item">
                    <span>Certificate ID:</span>
                    <strong><?= htmlspecialchars($cert['id']) ?></strong>
                </div>
                <div class="detail-item">
                    <span>Category:</span>
                    <?= htmlspecialchars($cert['category']) ?>
                </div>
                <div class="detail-item">
                    <span>Position:</span>
                    <?= htmlspecialchars($cert['position']) ?>
                </div>
                <div class="detail-item">
                    <span>Presented On:</span>
                    <?= date('F j, Y', strtotime($cert['date'])) ?>
                </div>
            </div>

            <div class="security-footer">
                <i class="fas fa-shield-alt"></i>
                <p>Secured by edXtra Verification Systems</p>
                <small>This certificate is digitally verified and tamper-proof</small>
            </div>
        </div>
    </div>
</body>
</html>