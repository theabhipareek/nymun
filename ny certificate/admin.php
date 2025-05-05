<?php
/**
 * Admin Login Page
 * 
 * Secure login page for certificate verification system administrators
 * 
 * Current UTC Time: 2025-05-05 16:51:35
 * User: theabhipareek
 */
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;");

// Process login
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get credentials
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Hard-coded credentials for example
    // In production, use a database and proper password hashing
    $validUsername = 'theabhipareek';
    $validPassword = 'admin123'; // In production, use password_hash() and password_verify()
    
    if (empty($username) || empty($password)) {
        $error = "Username and password are required";
    } elseif ($username === $validUsername && $password === $validPassword) {
        // Successful login
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_last_activity'] = time();
        
        // Log the login
        $logFile = 'data/admin_log.txt';
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        $logEntry = date("Y-m-d H:i:s") . " | Login successful | Username: $username | IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        header("Location: admin_dashboard.php");
        exit;
    } else {
        // Failed login
        $error = "Invalid username or password";
        
        // Log the failed attempt
        $logFile = 'data/admin_log.txt';
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        $logEntry = date("Y-m-d H:i:s") . " | Login failed | Username: $username | IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        // Add delay to prevent brute force attacks (not noticeable to legitimate users)
        sleep(1);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | NYMUN Certificate Verification</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="favicon.ico">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo" style="justify-content: center;">
                    <i class="fas fa-award"></i>
                    <span>NYMUN Admin</span>
                </div>
                <p style="margin-top: 10px; color: rgba(255,255,255,0.8);">Certificate Verification System</p>
            </div>
            
            <div class="login-body">
                <?php if (!empty($error)): ?>
                    <div class="result-error" style="margin-bottom: 20px;">
                        <div class="result-icon" style="font-size: 24px;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="result-content">
                            <p style="margin: 0;"><?php echo $error; ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" name="username" placeholder="Enter your username" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-sign-in-alt"></i> Log In
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="login-footer">
                <p>Current UTC Time: <span id="current-time">2025-05-05 16:51:35</span></p>
                <p style="margin-top: 5px;"><a href="index.php">Return to Certificate Verification</a></p>
            </div>
        </div>
    </div>
</body>
</html>
