<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = 'Admin123'; // Change this
    if ($_POST['password'] === $password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $error = "Invalid password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-login-container">
        <form method="POST">
            <h2>Admin Login</h2>
            <?php if(isset($error)): ?>
                <p class="error-message"><?= $error ?></p>
            <?php endif; ?>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>