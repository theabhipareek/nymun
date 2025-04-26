<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUN Certificate Verification</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Model United Nations Certificate Verification</h1>
            <p>Verify your MUN certificates securely</p>
        </div>
    </div>

    <div class="container">
        <div class="verification-box">
            <h2><i class="fas fa-certificate"></i> Verify Certificate</h2>
            <form action="verify.php" method="POST">
                <div class="form-group">
                    <label>Name on Certificate</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Certificate ID</label>
                    <input type="text" name="certificate_id" placeholder="6-digit code" required>
                </div>
                <button type="submit">Verify Now <i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
</body>
</html>