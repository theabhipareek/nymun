<?php
/**
 * Admin Dashboard for Certificate Management
 * 
 * Provides an interface for administrators to:
 * - View all certificates
 * - Add new certificates
 * - Edit existing certificates
 * - View verification logs
 * - Generate statistics
 * 
 * Current UTC Time: 2025-05-05 17:06:39
 * User: theabhipareek
 */

session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php");
    exit;
}

// Check session timeout (30 minutes)
if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > 1800)) {
    // Session expired
    session_unset();
    session_destroy();
    header("Location: admin.php?timeout=1");
    exit;
}

// Update last activity time
$_SESSION['admin_last_activity'] = time();

// Database configuration
$config = [
    'db_host' => 'localhost',
    'db_name' => 'nymun_certificates',
    'db_user' => 'nymun_user',
    'db_pass' => 'your_secure_password_here', // Change this in production
    'table_name' => 'certificates',
    'log_file' => 'data/verification_log.txt'
];

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Connect to database
try {
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
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Process form submissions
$message = '';
$error = '';

// Add new certificate
if (isset($_POST['add_certificate'])) {
    $name = sanitizeInput($_POST['name']);
    $certificate_id = sanitizeInput($_POST['certificate_id']);
    $event = sanitizeInput($_POST['event']);
    $position = sanitizeInput($_POST['position']);
    $committee = sanitizeInput($_POST['committee']);
    $issue_date = sanitizeInput($_POST['issue_date']);
    $is_valid = isset($_POST['is_valid']) ? 1 : 0;
    
    // Validate input
    if (empty($name) || empty($certificate_id) || empty($event) || empty($issue_date)) {
        $error = "All required fields must be filled";
    } elseif (!preg_match("/^[A-Za-z0-9]{6}$/", $certificate_id)) {
        $error = "Certificate ID must be 6 alphanumeric characters";
    } else {
        // Check if certificate ID already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$config['table_name']} WHERE certificate_id = :certificate_id");
        $stmt->bindParam(':certificate_id', $certificate_id, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $error = "Certificate ID already exists";
        } else {
            // Insert new certificate
            $stmt = $pdo->prepare("INSERT INTO {$config['table_name']} 
                                  (name, certificate_id, event, position, committee, issue_date, is_valid, created_at) 
                                  VALUES (:name, :certificate_id, :event, :position, :committee, :issue_date, :is_valid, NOW())");
            
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':certificate_id', $certificate_id, PDO::PARAM_STR);
            $stmt->bindParam(':event', $event, PDO::PARAM_STR);
            $stmt->bindParam(':position', $position, PDO::PARAM_STR);
            $stmt->bindParam(':committee', $committee, PDO::PARAM_STR);
            $stmt->bindParam(':issue_date', $issue_date, PDO::PARAM_STR);
            $stmt->bindParam(':is_valid', $is_valid, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $message = "Certificate added successfully";
            } else {
                $error = "Failed to add certificate";
            }
        }
    }
}

// Edit certificate
if (isset($_POST['edit_certificate'])) {
    $id = sanitizeInput($_POST['id']);
    $name = sanitizeInput($_POST['name']);
    $certificate_id = sanitizeInput($_POST['certificate_id']);
    $event = sanitizeInput($_POST['event']);
    $position = sanitizeInput($_POST['position']);
    $committee = sanitizeInput($_POST['committee']);
    $issue_date = sanitizeInput($_POST['issue_date']);
    $is_valid = isset($_POST['is_valid']) ? 1 : 0;
    
    // Validate input
    if (empty($name) || empty($certificate_id) || empty($event) || empty($issue_date)) {
        $error = "All required fields must be filled";
    } elseif (!preg_match("/^[A-Za-z0-9]{6}$/", $certificate_id)) {
        $error = "Certificate ID must be 6 alphanumeric characters";
    } else {
        // Check if certificate ID already exists for a different record
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$config['table_name']} WHERE certificate_id = :certificate_id AND id != :id");
        $stmt->bindParam(':certificate_id', $certificate_id, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $error = "Certificate ID already exists for another record";
        } else {
            // Update certificate
            $stmt = $pdo->prepare("UPDATE {$config['table_name']} SET 
                                  name = :name, 
                                  certificate_id = :certificate_id, 
                                  event = :event, 
                                  position = :position, 
                                  committee = :committee, 
                                  issue_date = :issue_date, 
                                  is_valid = :is_valid,
                                  updated_at = NOW()
                                  WHERE id = :id");
            
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':certificate_id', $certificate_id, PDO::PARAM_STR);
            $stmt->bindParam(':event', $event, PDO::PARAM_STR);
            $stmt->bindParam(':position', $position, PDO::PARAM_STR);
            $stmt->bindParam(':committee', $committee, PDO::PARAM_STR);
            $stmt->bindParam(':issue_date', $issue_date, PDO::PARAM_STR);
            $stmt->bindParam(':is_valid', $is_valid, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $message = "Certificate updated successfully";
            } else {
                $error = "Failed to update certificate";
            }
        }
    }
}

// Delete certificate
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    
    $stmt = $pdo->prepare("DELETE FROM {$config['table_name']} WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $message = "Certificate deleted successfully";
    } else {
        $error = "Failed to delete certificate";
    }
}

// Get list of certificates
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'id';
$order = isset($_GET['order']) ? sanitizeInput($_GET['order']) : 'DESC';
$validSortFields = ['id', 'name', 'certificate_id', 'event', 'issue_date', 'last_verified', 'is_valid', 'created_at'];
$validOrders = ['ASC', 'DESC'];

if (!in_array($sort, $validSortFields)) {
    $sort = 'id';
}

if (!in_array($order, $validOrders)) {
    $order = 'DESC';
}

$query = "SELECT * FROM {$config['table_name']} ORDER BY $sort $order";
$stmt = $pdo->prepare($query);
$stmt->execute();
$certificates = $stmt->fetchAll();

// Get verification statistics
$totalCertificates = count($certificates);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM {$config['table_name']} WHERE is_valid = 1");
$stmt->execute();
$validCertificates = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM {$config['table_name']} WHERE last_verified IS NOT NULL");
$stmt->execute();
$verifiedCertificates = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT event, COUNT(*) as count FROM {$config['table_name']} GROUP BY event ORDER BY count DESC");
$stmt->execute();
$eventStats = $stmt->fetchAll();

// Format event stats for chart
$eventChartData = [
    'labels' => [],
    'data' => []
];

foreach ($eventStats as $event) {
    $eventChartData['labels'][] = $event['event'];
    $eventChartData['data'][] = $event['count'];
}

// Get recent verifications (if log file exists)
$recentVerifications = [];
if (file_exists($config['log_file'])) {
    $logLines = file($config['log_file'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $recentVerifications = array_slice(array_reverse($logLines), 0, 10);
}

// Get verification trend data (last 7 days)
$verificationTrend = [];
$currentDate = date('Y-m-d');
$sevenDaysAgo = date('Y-m-d', strtotime('-6 days'));

$stmt = $pdo->prepare("SELECT DATE(last_verified) as date, COUNT(*) as count 
                      FROM {$config['table_name']} 
                      WHERE last_verified IS NOT NULL 
                      AND DATE(last_verified) BETWEEN :start_date AND :end_date 
                      GROUP BY DATE(last_verified) 
                      ORDER BY date ASC");

$stmt->bindParam(':start_date', $sevenDaysAgo, PDO::PARAM_STR);
$stmt->bindParam(':end_date', $currentDate, PDO::PARAM_STR);
$stmt->execute();
$verificationData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fill in missing dates with zero counts
$verificationChartData = [
    'labels' => [],
    'data' => []
];

for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $formattedDate = date('M d', strtotime($date));
    
    $verificationChartData['labels'][] = $formattedDate;
    $verificationChartData['data'][] = isset($verificationData[$date]) ? $verificationData[$date] : 0;
}

// Reverse arrays to show chronological order
$verificationChartData['labels'] = array_reverse($verificationChartData['labels']);
$verificationChartData['data'] = array_reverse($verificationChartData['data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | NYMUN Certificate Verification</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-header">
        <div class="admin-title">
            <i class="fas fa-award" style="font-size: 24px; color: var(--accent-color);"></i>
            <h1 style="margin: 0; font-size: 1.5rem;">NYMUN Certificate Admin</h1>
        </div>
        
        <div class="admin-actions">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-external-link-alt"></i> View Public Site
            </a>
            <a href="logout.php" class="btn btn-secondary">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="admin-container">
        <?php if (!empty($message)): ?>
            <div class="toast-container">
                <div class="toast toast-success">
                    <div class="toast-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="toast-content">
                        <div class="toast-title">Success</div>
                        <div class="toast-message"><?php echo $message; ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="toast-container">
                <div class="toast toast-error">
                    <div class="toast-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="toast-content">
                        <div class="toast-title">Error</div>
                        <div class="toast-message"><?php echo $error; ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="status-card">
            <h2><i class="fas fa-user-shield"></i> Admin Dashboard</h2>
            <p>Welcome, <strong><?php echo $_SESSION['admin_username']; ?></strong>. Current UTC time: <strong>2025-05-05 17:06:39</strong></p>
        </div>
        
        <div class="admin-tabs">
            <div class="admin-tab active" data-tab="certificates-tab">
                <i class="fas fa-certificate"></i> Certificates
            </div>
            <div class="admin-tab" data-tab="add-certificate-tab">
                <i class="fas fa-plus-circle"></i> Add Certificate
            </div>
            <div class="admin-tab" data-tab="statistics-tab">
                <i class="fas fa-chart-bar"></i> Statistics
            </div>
            <div class="admin-tab" data-tab="verification-log-tab">
                <i class="fas fa-history"></i> Verification Log
            </div>
            <div class="admin-tab" data-tab="settings-tab">
                <i class="fas fa-cog"></i> Settings
            </div>
        </div>
        
        <!-- Certificates Tab -->
        <div id="certificates-tab" class="tab-content active">
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalCertificates; ?></div>
                    <div class="stat-label">Total Certificates</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo $validCertificates; ?></div>
                    <div class="stat-label">Valid Certificates</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo $verifiedCertificates; ?></div>
                    <div class="stat-label">Verified Certificates</div>
                </div>
            </div>
            
            <div class="export-import-controls">
                <button id="export-certificates" class="btn btn-secondary">
                    <i class="fas fa-file-export"></i> Export Certificates
                </button>
                
                <button id="import-toggle" class="btn btn-secondary" onclick="document.getElementById('import-form').style.display = 'block'; this.style.display = 'none';">
                    <i class="fas fa-file-import"></i> Import Certificates
                </button>
                
                <form id="import-form" action="import_certificates.php" method="POST" enctype="multipart/form-data" style="display: none;">
                    <div class="input-wrapper" style="width: 100%; display: flex; gap: 10px; align-items: center;">
                        <input type="file" name="import_file" id="import_file" accept=".csv,.xlsx" style="padding-left: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="search-box">
                <div class="input-wrapper" style="flex: 1;">
                    <i class="fas fa-search"></i>
                    <input type="text" id="certificate-search" placeholder="Search by name, ID or event...">
                </div>
                
                <div class="filter-options">
                    <div class="filter-option active" data-filter="all">All</div>
                    <div class="filter-option" data-filter="valid">Valid</div>
                    <div class="filter-option" data-filter="invalid">Revoked</div>
                    <div class="filter-option" data-filter="verified">Verified</div>
                </div>
            </div>
            
            <div style="margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <label for="batch-action">Batch Action:</label>
                        <select id="batch-action" style="width: auto; padding-left: 10px;">
                            <option value="">Select Action</option>
                            <option value="validate">Mark as Valid</option>
                            <option value="invalidate">Mark as Invalid</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button id="apply-batch" class="btn btn-secondary" style="margin-left: 10px;">
                            Apply
                        </button>
                    </div>
                    
                    <div>
                        Total: <strong><?php echo count($certificates); ?></strong> certificates
                    </div>
                </div>
            </div>
            
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th data-sort="id" data-order="<?php echo $sort === 'id' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                                ID <?php echo $sort === 'id' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
                            </th>
                            <th data-sort="name" data-order="<?php echo $sort === 'name' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                                Name <?php echo $sort === 'name' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
                            </th>
                            <th data-sort="certificate_id" data-order="<?php echo $sort === 'certificate_id' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                                Certificate ID <?php echo $sort === 'certificate_id' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
                            </th>
                            <th data-sort="event" data-order="<?php echo $sort === 'event' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                                Event <?php echo $sort === 'event' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
                            </th>
                            <th data-sort="issue_date" data-order="<?php echo $sort === 'issue_date' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                                Issue Date <?php echo $sort === 'issue_date' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
                            </th>
                            <th data-sort="last_verified" data-order="<?php echo $sort === 'last_verified' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                                Last Verified <?php echo $sort === 'last_verified' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
                            </th>
                            <th data-sort="is_valid" data-order="<?php echo $sort === 'is_valid' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                                Status <?php echo $sort === 'is_valid' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($certificates)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center;">No certificates found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($certificates as $certificate): ?>
                                <tr class="certificate-row">
                                    <td>
                                        <input type="checkbox" class="certificate-checkbox" value="<?php echo $certificate['id']; ?>">
                                    </td>
                                    <td><?php echo $certificate['id']; ?></td>
                                    <td data-field="name"><?php echo $certificate['name']; ?></td>
                                    <td data-field="certificate_id"><?php echo $certificate['certificate_id']; ?></td>
                                    <td data-field="event"><?php echo $certificate['event']; ?></td>
                                    <td><?php echo $certificate['issue_date']; ?></td>
                                    <td data-field="last_verified"><?php echo $certificate['last_verified'] ? date('Y-m-d H:i', strtotime($certificate['last_verified'])) : '-'; ?></td>
                                    <td data-field="is_valid">
                                        <?php if ($certificate['is_valid']): ?>
                                            <span class="valid-badge">Valid</span>
                                        <?php else: ?>
                                            <span class="invalid-badge">Revoked</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-links">
                                        <a href="#" class="edit-certificate" 
                                           data-id="<?php echo $certificate['id']; ?>"
                                           data-name="<?php echo $certificate['name']; ?>"
                                           data-certificate-id="<?php echo $certificate['certificate_id']; ?>"
                                           data-event="<?php echo $certificate['event']; ?>"
                                           data-position="<?php echo $certificate['position']; ?>"
                                           data-committee="<?php echo $certificate['committee']; ?>"
                                           data-issue-date="<?php echo $certificate['issue_date']; ?>"
                                           data-is-valid="<?php echo $certificate['is_valid']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="admin_dashboard.php?delete=<?php echo $certificate['id']; ?>" class="delete-certificate"
                                           onclick="return confirm('Are you sure you want to delete this certificate? This action cannot be undone.');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                        <a href="download_certificate.php?id=<?php echo $certificate['certificate_id']; ?>" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Edit Certificate Modal -->
            <div id="edit-modal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h2>Edit Certificate</h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" id="edit_id" name="id">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_name">Full Name</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="edit_name" name="name" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_certificate_id">Certificate ID</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-id-card"></i>
                                    <input type="text" id="edit_certificate_id" name="certificate_id" pattern="[A-Za-z0-9]{6}" maxlength="6" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_event">Event</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-calendar-alt"></i>
                                    <select id="edit_event" name="event" required>
                                        <option value="">Select Event</option>
                                        <option value="NYMUN 2025">NYMUN 2025</option>
                                        <option value="NYMUN 2024">NYMUN 2024</option>
                                        <option value="NYMUN 2023">NYMUN 2023</option>
                                        <option value="NYMUN 2022">NYMUN 2022</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_position">Position</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user-tag"></i>
                                    <select id="edit_position" name="position">
                                        <option value="">Select Position</option>
                                        <option value="Delegate">Delegate</option>
                                        <option value="Chair">Chair</option>
                                        <option value="Vice Chair">Vice Chair</option>
                                        <option value="Rapporteur">Rapporteur</option>
                                        <option value="Secretary General">Secretary General</option>
                                        <option value="Director General">Director General</option>
                                        <option value="Organizing Committee">Organizing Committee</option>
                                        <option value="Volunteer">Volunteer</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_committee">Committee</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-users"></i>
                                    <select id="edit_committee" name="committee">
                                        <option value="">Select Committee</option>
                                        <option value="Security Council">Security Council</option>
                                        <option value="General Assembly">General Assembly</option>
                                        <option value="Economic and Social Council">Economic and Social Council</option>
                                        <option value="Human Rights Council">Human Rights Council</option>
                                        <option value="World Health Organization">World Health Organization</option>
                                        <option value="International Court of Justice">International Court of Justice</option>
                                        <option value="United Nations Environment Programme">United Nations Environment Programme</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_issue_date">Issue Date</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-calendar"></i>
                                    <input type="date" id="edit_issue_date" name="issue_date" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="edit_is_valid" name="is_valid">
                            <label for="edit_is_valid">Certificate is valid</label>
                        </div>
                        
                        <div style="margin-top: 20px; display: flex; gap: 10px;">
                            <button type="submit" name="edit_certificate" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-secondary close-modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Add Certificate Tab -->
        <div id="add-certificate-tab" class="tab-content">
            <div class="admin-form">
                <h2><i class="fas fa-plus-circle"></i> Add New Certificate</h2>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" id="name" name="name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="certificate_id_field">Certificate ID</label>
                            <div class="input-wrapper" style="display: flex; gap: 10px;">
                                <i class="fas fa-id-card"></i>
                                <input type="text" id="certificate_id_field" name="certificate_id" pattern="[A-Za-z0-9]{6}" maxlength="6" style="flex: 1;" required>
                                <button type="button" id="generate-id" class="btn btn-secondary" style="width: auto; padding: 0 15px;">
                                    <i class="fas fa-random"></i> Generate
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event">Event</label>
                            <div class="input-wrapper">
                                <i class="fas fa-calendar-alt"></i>
                                <select id="event" name="event" required>
                                    <option value="">Select Event</option>
                                    <option value="NYMUN 2025">NYMUN 2025</option>
                                    <option value="NYMUN 2024">NYMUN 2024</option>
                                    <option value="NYMUN 2023">NYMUN 2023</option>
                                    <option value="NYMUN 2022">NYMUN 2022</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="position">Position</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user-tag"></i>
                                <select id="position" name="position">
                                    <option value="">Select Position</option>
                                    <option value="Delegate">Delegate</option>
                                    <option value="Chair">Chair</option>
                                    <option value="Vice Chair">Vice Chair</option>
                                    <option value="Rapporteur">Rapporteur</option>
                                    <option value="Secretary General">Secretary General</option>
                                    <option value="Director General">Director General</option>
                                    <option value="Organizing Committee">Organizing Committee</option>
                                    <option value="Volunteer">Volunteer</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="committee">Committee</label>
                            <div class="input-wrapper">
                                <i class="fas fa-users"></i>
                                <select id="committee" name="committee">
                                    <option value="">Select Committee</option>
                                    <option value="Security Council">Security Council</option>
                                    <option value="General Assembly">General Assembly</option>
                                    <option value="Economic and Social Council">Economic and Social Council</option>
                                    <option value="Human Rights Council">Human Rights Council</option>
                                    <option value="World Health Organization">World Health Organization</option>
                                    <option value="International Court of Justice">International Court of Justice</option>
                                    <option value="United Nations Environment Programme">United Nations Environment Programme</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="issue_date">Issue Date</label>
                            <div class="input-wrapper">
                                <i class="fas fa-calendar"></i>
                                <input type="date" id="issue_date" name="issue_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_valid" name="is_valid" checked>
                        <label for="is_valid">Certificate is valid</label>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" name="add_certificate" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Certificate
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset Form
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="admin-form" style="margin-top: 30px;">
                <h2><i class="fas fa-file-import"></i> Bulk Import</h2>
                <p>You can import multiple certificates at once by uploading a CSV or Excel file with the required fields.</p>
                
                <form method="POST" action="import_certificates.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file">Select File (CSV or Excel)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-file-upload"></i>
                            <input type="file" id="file" name="file" accept=".csv,.xlsx" required>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload and Import
                        </button>
                        <a href="template/certificate_import_template.csv" download class="btn btn-secondary">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Statistics Tab -->
        <div id="statistics-tab" class="tab-content">
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalCertificates; ?></div>
                    <div class="stat-label">Total Certificates</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo $validCertificates; ?></div>
                    <div class="stat-label">Valid Certificates</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo $verifiedCertificates; ?></div>
                    <div class="stat-label">Verified Certificates</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value">
                        <?php 
                            echo $verifiedCertificates > 0 ? 
                                 round(($verifiedCertificates / $totalCertificates) * 100) . '%' : 
                                 '0%'; 
                        ?>
                    </div>
                    <div class="stat-label">Verification Rate</div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px; margin-top: 30px;">
                <!-- Certificates by Event Chart -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie"></i>
                        <h3>Certificates by Event</h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="certificates-by-event-chart" data-chart='<?php echo json_encode($eventChartData); ?>'></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Verifications Over Time Chart -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i>
                        <h3>Verifications (Last 7 Days)</h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="verifications-chart" data-chart='<?php echo json_encode($verificationChartData); ?>'></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <i class="fas fa-table"></i>
                    <h3>Event Statistics</h3>
                </div>
                <div class="card-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Total Certificates</th>
                                <th>Valid Certificates</th>
                                <th>Verified Certificates</th>
                                <th>Verification Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventStats as $event): ?>
                                <?php 
                                    // Get additional stats for this event
                                    $stmt = $pdo->prepare("SELECT 
                                                           COUNT(*) as total,
                                                           SUM(CASE WHEN is_valid = 1 THEN 1 ELSE 0 END) as valid,
                                                           SUM(CASE WHEN last_verified IS NOT NULL THEN 1 ELSE 0 END) as verified
                                                           FROM {$config['table_name']} 
                                                           WHERE event = :event");
                                    $stmt->bindParam(':event', $event['event'], PDO::PARAM_STR);
                                    $stmt->execute();
                                    $eventDetail = $stmt->fetch();
                                ?>
                                <tr>
                                    <td><?php echo $event['event']; ?></td>
                                    <td><?php echo $eventDetail['total']; ?></td>
                                    <td><?php echo $eventDetail['valid']; ?></td>
                                    <td><?php echo $eventDetail['verified']; ?></td>
                                    <td>
                                        <?php 
                                            echo $eventDetail['total'] > 0 ? 
                                                 round(($eventDetail['verified'] / $eventDetail['total']) * 100) . '%' : 
                                                 '0%'; 
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Verification Log Tab -->
        <div id="verification-log-tab" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history"></i>
                    <h3>Recent Verification Attempts</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($recentVerifications)): ?>
                        <p>No verification logs found.</p>
                    <?php else: ?>
                        <div style="max-height: 500px; overflow-y: auto;">
                            <?php foreach ($recentVerifications as $log): ?>
                                <div class="log-entry">
                                    <?php 
                                        // Parse log entry
                                        $parts = explode(' | ', $log);
                                        $timestamp = isset($parts[0]) ? $parts[0] : '';
                                        $ip = isset($parts[1]) ? str_replace('IP: ', '', $parts[1]) : '';
                                        $name = isset($parts[2]) ? str_replace('Name: ', '', $parts[2]) : '';
                                        $certificateId = isset($parts[3]) ? str_replace('Certificate ID: ', '', $parts[3]) : '';
                                        $result = isset($parts[4]) ? str_replace('Result: ', '', $parts[4]) : '';
                                    ?>
                                    <span class="log-time"><?php echo $timestamp; ?></span>
                                    <?php if ($result === 'VALID'): ?>
                                        <span class="valid-badge">Valid</span>
                                    <?php else: ?>
                                        <span class="invalid-badge">Invalid</span>
                                    <?php endif; ?>
                                    <strong><?php echo $name; ?></strong> (ID: <?php echo $certificateId; ?>) | IP: <?php echo $ip; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="margin-top: 20px; text-align: center;">
                            <a href="export_log.php" class="btn btn-secondary">
                                <i class="fas fa-download"></i> Export Full Log
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Settings Tab -->
        <div id="settings-tab" class="tab-content">
            <div class="admin-form">
                <h2><i class="fas fa-user-shield"></i> Account Settings</h2>
                
                <form method="POST" action="update_account.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" id="username" name="username" value="theabhipareek" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-key"></i>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-key"></i>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="admin-form" style="margin-top: 30px;">
                <h2><i class="fas fa-cogs"></i> System Settings</h2>
                
                <form method="POST" action="update_settings.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="site_title">Site Title</label>
                            <div class="input-wrapper">
                                <i class="fas fa-heading"></i>
                                <input type="text" id="site_title" name="site_title" value="NYMUN Certificate Verification">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="contact_email" name="contact_email" value="contact@nymun.org">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="footer_text">Footer Text</label>
                        <div class="input-wrapper">
                            <i class="fas fa-paragraph"></i>
                            <textarea id="footer_text" name="footer_text" rows="3">© 2025 NYMUN. All rights reserved.</textarea>
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode">
                        <label for="maintenance_mode">Enable Maintenance Mode</label>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="admin-form" style="margin-top: 30px;">
                <h2><i class="fas fa-database"></i> Database Management</h2>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <a href="backup_database.php" class="btn btn-primary">
                        <i class="fas fa-download"></i> Backup Database
                    </a>
                    
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('restore-form').style.display = 'block'; this.style.display = 'none';">
                        <i class="fas fa-upload"></i> Restore Database
                    </button>
                </div>
                
                <form id="restore-form" action="restore_database.php" method="POST" enctype="multipart/form-data" style="display: none; margin-top: 20px;">
                    <div class="form-group">
                        <label for="backup_file">Select Backup File</label>
                        <div class="input-wrapper">
                            <i class="fas fa-file-upload"></i>
                            <input type="file" id="backup_file" name="backup_file" accept=".sql" required>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('WARNING: This will overwrite your current database. Make sure you have a backup before proceeding. Continue?');">
                            <i class="fas fa-exclamation-triangle"></i> Restore Database
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <footer class="footer" style="margin-top: 40px;">
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
                <p>Current UTC time: <span id="current-time">2025-05-05 17:06:39</span></p>
            </div>
        </div>
    </footer>
    
    <script src="scripts.js"></script>
</body>
</html>
