<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit();
}

$data_file = 'data/certificates.json';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $certificates = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];

    if (isset($_POST['delete'])) {
        // Delete certificates
        $ids_to_delete = $_POST['delete_ids'] ?? [];
        $certificates = array_filter($certificates, function($cert) use ($ids_to_delete) {
            return !in_array($cert['id'], $ids_to_delete);
        });
        $success = count($ids_to_delete) . " certificate(s) deleted successfully!";
    } else {
        // Add new certificate
        $new_cert = [
            'id' => generateCleanId(),
            'name' => $_POST['name'],
            'category' => $_POST['category'],
            'position' => $_POST['position'],
            'date' => $_POST['date']
        ];

        $certificates[] = $new_cert;
        $success = "Certificate added successfully!";
    }

    file_put_contents($data_file, json_encode($certificates, JSON_PRETTY_PRINT));
}

// Generate clean ID function
function generateCleanId() {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $id = '';
    for ($i = 0; $i < 6; $i++) {
        $id .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $id;
}

// Read existing data
$certificates = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-panel container">
        <div class="header">
            <h1>Admin Dashboard <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></h1>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="admin-section">
            <h2><i class="fas fa-plus-circle"></i> Add New Certificate</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="input-group">
                        <label>Participant Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="input-group">
                        <label>Category</label>
                        <input type="text" name="category" required>
                    </div>
                    <div class="input-group">
                        <label>Position/Committee</label>
                        <input type="text" name="position" required>
                    </div>
                    <div class="input-group">
                        <label>Presentation Date</label>
                        <input type="date" name="date" required>
                    </div>
                </div>
                <button type="submit"><i class="fas fa-save"></i> Save Certificate</button>
            </form>
        </div>

        <div class="admin-section">
            <h2><i class="fas fa-list"></i> Manage Certificates</h2>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search certificates...">
                <button onclick="bulkDelete()" class="delete-btn"><i class="fas fa-trash"></i> Delete Selected</button>
            </div>

            <form id="deleteForm" method="POST">
                <input type="hidden" name="delete" value="1">
                <table class="table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($certificates) as $cert): ?>
                        <tr>
                            <td><input type="checkbox" name="delete_ids[]" value="<?= $cert['id'] ?>"></td>
                            <td><?= $cert['id'] ?></td>
                            <td><?= htmlspecialchars($cert['name']) ?></td>
                            <td><?= htmlspecialchars($cert['category']) ?></td>
                            <td><?= date('M j, Y', strtotime($cert['date'])) ?></td>
                            <td class="action-btns">
                                <button type="button" class="delete-btn" onclick="deleteCertificate('<?= $cert['id'] ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Bulk delete
        function bulkDelete() {
            if (confirm('Are you sure you want to delete selected certificates?')) {
                document.getElementById('deleteForm').submit();
            }
        }

        // Single delete
        function deleteCertificate(id) {
            if (confirm('Delete this certificate?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="delete" value="1">
                    <input type="hidden" name="delete_ids[]" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Select all checkboxes
        document.getElementById('selectAll').addEventListener('click', function(e) {
            const checkboxes = document.querySelectorAll('input[name="delete_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = e.target.checked);
        });
    </script>
</body>
</html>