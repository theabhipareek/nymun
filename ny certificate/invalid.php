<?php
/**
 * Invalid Certificate Result Page
 * 
 * Displays error message for invalid certificates
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
if (!isset($_SESSION['verification_result'])) {
    header("Location: index.php");
    exit;
}

$result = $_SESSION['verification_result'];
$message = $result['message'];

// Clear session data after displaying
unset($_SESSION['verification_result']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Invalid certificate verification result">
    <title>Verification Failed | NYMUN Certificate Verification</title>
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
                    <p>The certificate verification was unsuccessful</p>
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="container">
                <div class="card verification-card">
                    <div class="card-header">
                        <i class="fas fa-times-circle"></i>
                        <h2>Certificate Verification Failed</h2>
                    </div>
                    <div class="card-body">
                        <div class="result-error">
                            <div class="result-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="result-content">
                                <h3>Invalid Certificate</h3>
                                <p><?php echo $message; ?></p>
                            </div>
                        </div>
                        
                        <div style="margin-top: 30px;">
                            <h4>Possible Reasons:</h4>
                            <ul style="margin-left: 20px; margin-top: 10px;">
                                <li>The certificate ID does not exist in our database</li>
                                <li>The name does not match the certificate ID</li>
                                <li>The certificate may have been revoked</li>
                                <li>You may have entered incorrect information</li>
                            </ul>
                        </div>
                        
                        <div style="text-align: center; margin-top: 30px;">
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-redo"></i> Try Again
                            </a>
                            <a href="https://nymun.org/contact" class="btn btn-secondary">
                                <i class="fas fa-envelope"></i> Contact Support
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
