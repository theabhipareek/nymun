<?php
/**
 * Valid Certificate Result Page
 * 
 * Displays certificate details for valid certificates
 * 
 * Current UTC Time: 2025-05-05 16:56:17
 * User: theabhipareek
 */

session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;");

// Check if verification result exists in session
if (!isset($_SESSION['verification_result']) || $_SESSION['verification_result']['status'] !== 'success') {
    header("Location: index.php");
    exit;
}

$result = $_SESSION['verification_result'];
$certificate = $result['certificate'];

// Clear session data after displaying
// This prevents reusing old results on page refresh
unset($_SESSION['verification_result']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Valid certificate verification result">
    <title>Certificate Verified | NYMUN Certificate Verification</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="favicon.ico">
</head>
<body>
    <div class="page-wrapper">
        <header class="header">
            <div class="container">
                <nav class="navbar">
                    <div class="logo">
                        <i class="fas fa-award"></i>
                        <span>NYMUN</span>
                    </div>
                    <div class="nav-links">
                        <a href="index.php" class="nav-link">Home</a>
                        <a href="https://nymun.org/about" class="nav-link">About</a>
                        <a href="https://nymun.org/contact" class="nav-link">Contact</a>
                    </div>
                </nav>
                <div class="header-content">
                    <h1>Certificate Verification Result</h1>
                    <p>Your certificate has been successfully verified</p>
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="container">
                <div class="card verification-card">
                    <div class="card-header">
                        <i class="fas fa-check-circle"></i>
                        <h2>Certificate Verified Successfully</h2>
                    </div>
                    <div class="card-body">
                        <div class="result-success">
                            <div class="result-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="result-content">
                                <h3>Valid Certificate</h3>
                                <p>The certificate is authentic and was issued by NYMUN.</p>
                            </div>
                        </div>
                        
                        <div class="certificate-details">
                            <h4>Certificate Details</h4>
                            <div class="certificate-detail">
                                <div class="detail-label">Name:</div>
                                <div class="detail-value"><?php echo $certificate['name']; ?></div>
                            </div>
                            <div class="certificate-detail">
                                <div class="detail-label">Certificate ID:</div>
                                <div class="detail-value"><?php echo $certificate['certificate_id']; ?></div>
                            </div>
                            <div class="certificate-detail">
                                <div class="detail-label">Issue Date:</div>
                                <div class="detail-value"><?php echo $certificate['issue_date']; ?></div>
                            </div>
                            <div class="certificate-detail">
                                <div class="detail-label">Event:</div>
                                <div class="detail-value"><?php echo $certificate['event']; ?></div>
                            </div>
                            <div class="certificate-detail">
                                <div class="detail-label">Position:</div>
                                <div class="detail-value"><?php echo $certificate['position']; ?></div>
                            </div>
                            <div class="certificate-detail">
                                <div class="detail-label">Committee:</div>
                                <div class="detail-value"><?php echo $certificate['committee']; ?></div>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 30px;">
                            <a href="download_certificate.php?id=<?php echo $certificate['certificate_id']; ?>" class="btn btn-primary" target="_blank">
                                <i class="fas fa-download"></i> Download Certificate
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-search"></i> Verify Another Certificate
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-logo">
                        <i class="fas fa-award"></i>
                        <span>NYMUN</span>
                    </div>
                    <div class="footer-links">
                        <a href="https://nymun.org/privacy">Privacy Policy</a>
                        <a href="https://nymun.org/terms">Terms of Service</a>
                        <a href="https://nymun.org/contact">Contact Us</a>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2025 NYMUN. All rights reserved.</p>
                    <p>Current UTC time: <span id="current-time">2025-05-05 16:56:17</span></p>
                </div>
            </div>
        </footer>
    </div>

    <script src="scripts.js"></script>
</body>
</html>
