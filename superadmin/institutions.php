<?php
require_once '../config/db.php';
checkAuth('superadmin');
global $pdo;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        if ($name) {
            $pdo->prepare("INSERT INTO institutions (name, address) VALUES (?, ?)")->execute([$name, $address]);
            $msg = "Institution '$name' registered successfully.";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['inst_id'] ?? 0;
        if ($id) {
            $pdo->prepare("DELETE FROM institutions WHERE id = ?")->execute([$id]);
            $msg = "Institution removed.";
        }
    } elseif ($action === 'bulk_upload' && isset($_FILES['csv_file'])) {
        $rows = array_map('str_getcsv', file($_FILES['csv_file']['tmp_name']));
        foreach ($rows as $row) {
            if (count($row) < 1 || empty(trim($row[0]))) continue;
            $iname = trim($row[0]);
            $iaddr = isset($row[1]) ? trim($row[1]) : '';
            $pdo->prepare("INSERT INTO institutions (name, address) VALUES (?, ?)")->execute([$iname, $iaddr]);
        }
        $msg = "Bulk import complete.";
    }
}

// Fetch institutions
$institutions = $pdo->query("SELECT * FROM institutions ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Institutions - Admission System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/theme.js"></script>
    <style>
        .inst-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 32px; }
        .inst-card-title { font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 4px; }
        .inst-card-addr { font-size: 13px; color: var(--text-dim); }
    </style>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <a href="dashboard.php" data-tooltip="Dashboard"><i class="fas fa-solar-panel"></i><span>Dashboard</span></a>
            <a href="institutions.php" class="active" data-tooltip="Manage Institutions"><i class="fas fa-university"></i><span>Institutions</span></a>
            <a href="admins.php" data-tooltip="Manage Admins"><i class="fas fa-user-shield"></i><span>Admins</span></a>
            <a href="students.php" data-tooltip="Student Directory"><i class="fas fa-user-graduate"></i><span>Students</span></a>
            <a href="../settings.php" data-tooltip="Settings"><i class="fas fa-cog"></i><span>Settings</span></a>
        </div>
        <div class="sidebar-footer">
            <a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="topbar">
            <h2 class="page-title">Institution Management</h2>
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon"></i></button>
                <a href="../settings.php" style="display: flex; align-items: center; gap: 20px; text-decoration: none; color: inherit; cursor: pointer;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-weight: 600; font-size: 14px;">Super Admin</span>
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; color: var(--text-on-accent); font-weight: bold;">
                            SA
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="content">
            <?php if(isset($msg)) echo "<div class='success'>$msg</div>"; ?>
            <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

            <div class="inst-grid">
                <!-- Left: Forms -->
                <div>
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header" style="margin-bottom: 24px;">
                            <h3><i class="fas fa-plus-circle" style="color: var(--accent); margin-right: 10px;"></i> Register Institution</h3>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <label>Institution Official Name</label>
                            <input type="text" name="name" required placeholder="e.g. Imperial Institute of Technology">
                            
                            <label>Campus Address</label>
                            <textarea name="address" rows="3" placeholder="Full postal address..."></textarea>
                            
                            <button type="submit" style="width: 100%;"><i class="fas fa-check"></i> Register Now</button>
                        </form>
                    </div>

                    <div class="card" style="border: 1px dashed var(--accent);">
                        <div class="card-header" style="margin-bottom: 12px;">
                            <h3>Bulk Provisioning</h3>
                        </div>
                        <p style="font-size: 13px; color: var(--text-dim); margin-bottom: 20px;">Upload CSV with <code>Name, Address</code> format.</p>
                        <form method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px;">
                            <input type="hidden" name="action" value="bulk_upload">
                            <input type="file" name="csv_file" accept=".csv" required style="margin: 0; padding: 8px;">
                            <button type="submit" style="white-space: nowrap; padding: 0 16px;">Upload</button>
                        </form>
                    </div>
                </div>

                <!-- Right: List -->
                <div class="card">
                    <div class="card-header" style="margin-bottom: 24px;">
                        <h3>Active Portals</h3>
                    </div>
                    <?php if(empty($institutions)): ?>
                        <div style="text-align: center; padding: 60px; color: var(--text-dim);">
                            <i class="fas fa-ghost" style="font-size: 40px; margin-bottom: 16px; opacity: 0.3;"></i>
                            <p>No institutions registered yet.</p>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            <?php foreach ($institutions as $inst): ?>
                                <div style="display: flex; align-items: flex-start; gap: 20px; padding: 20px; background: var(--bg-app); border-radius: 12px; border: 1px solid var(--border);">
                                    <div style="width: 44px; height: 44px; border-radius: 10px; background: var(--accent-soft); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div style="flex-grow: 1;">
                                        <div class="inst-card-title"><?= htmlspecialchars($inst['name']) ?></div>
                                        <div class="inst-card-addr"><i class="fas fa-map-marker-alt" style="margin-right: 6px; opacity: 0.5;"></i> <?= htmlspecialchars($inst['address'] ?? '') ?></div>
                                        <div style="font-size: 11px; color: var(--accent); font-weight: 700; margin-top: 8px;">INST-ID: #<?= $inst['id'] ?></div>
                                    </div>
                                    <form method="POST" onsubmit="return confirm('WARNING: This will delete all associated data. Continue?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $inst['id'] ?>">
                                        <button type="submit" class="theme-toggle" style="width: 36px; height: 36px; color: #ef4444; background: rgba(239, 68, 68, 0.1);">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
