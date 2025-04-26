<?php
session_start();

$data_file = 'data/certificates.json';

// Read existing data
$certificates = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];

// Check certificate
$found = false;
foreach ($certificates as $cert) {
    if (strtolower($cert['name']) === strtolower($_POST['name']) && $cert['id'] === strtoupper($_POST['certificate_id'])) {
        $_SESSION['certificate_data'] = $cert;
        header("Location: confirmed.php");
        exit();
    }
}

header("Location: invalid.php");
exit();
?>