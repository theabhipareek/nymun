<?php
session_start();
// Set proper security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdnjs.cloudflare.com; style-src 'self' https://fonts.googleapis.com https://cdnjs.cloudflare.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:;");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure verification system for Model United Nations certificates">
    <title>MUN Certificate Verification Portal</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <!-- Favicon -->
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
                        <a href="https://nymun.org" class="nav-link">Home</a>
                        <a href="https://nymun.org/about" class="nav-link">About</a>
                        <a href="https://nymun.org/contact" class="nav-link">Contact</a>
                    </div>
                </nav>
                <div class="header-content">
                    <h1>Certificate Verification Portal</h1>
                    <p>Verify the authenticity of your Model United Nations certificates securely and instantly</p>
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="container">
                <div class="card verification-card">
                    <div class="card-header">
                        <i class="fas fa-certificate"></i>
                        <h2>Verify Your Certificate</h2>
                    </div>
                    <div class="card-body">
                        <form id="verification-form" action="verify.php" method="POST">
                            <div class="form-group">
                                <label for="name">Full Name (as on certificate)</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                                </div>
                                <div class="form-feedback" id="name-feedback"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="certificate_id">Certificate ID</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-id-card"></i>
                                    <input type="text" id="certificate_id" name="certificate_id" 
                                           pattern="[A-Za-z0-9]{6}" maxlength="6"
                                           placeholder="Enter 6-digit alphanumeric code" required>
                                </div>
                                <div class="form-feedback" id="certificate-feedback"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="event">Event (Optional)</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-calendar-alt"></i>
                                    <select id="event" name="event">
                                        <option value="">Select Event (Optional)</option>
                                        <option value="NYMUN 2025">NYMUN 2025</option>
                                        <option value="NYMUN 2024">NYMUN 2024</option>
                                        <option value="NYMUN 2023">NYMUN 2023</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="captcha-container">
                                <div id="captcha-box">
                                    <span id="captcha-text"></span>
                                </div>
                                <button type="button" id="refresh-captcha" title="Refresh Captcha">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            
                            <div class="form-group">
                                <label for="captcha-input">Enter Captcha</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-shield-alt"></i>
                                    <input type="text" id="captcha-input" name="captcha" placeholder="Enter captcha code" required>
                                </div>
                                <div class="form-feedback" id="captcha-feedback"></div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" id="verify-btn" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Verify Certificate
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="info-section">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Secure Verification</h3>
                        <p>Our system uses advanced encryption to keep your certificate data safe and secure.</p>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3>Instant Results</h3>
                        <p>Get immediate confirmation of your certificate's authenticity.</p>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h3>Historical Records</h3>
                        <p>Verify certificates from all past NYMUN events with our comprehensive database.</p>
                    </div>
                </div>
            </div>
        </main>

        <div id="verification-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div id="verification-result"></div>
            </div>
        </div>

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
                    <p>&copy; <?php echo date("Y"); ?> NYMUN. All rights reserved.</p>
                    <p>Current UTC time: <span id="current-time"><?php echo date("Y-m-d H:i:s"); ?></span></p>
                </div>
            </div>
        </footer>
    </div>

    <div class="loader-overlay" id="loader">
        <div class="loader"></div>
    </div>

    <script src="scripts.js"></script>
</body>
</html>
