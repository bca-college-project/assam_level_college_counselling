<?php
require_once '../config/db.php';
checkAuth('superadmin');
global $pdo;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $inst_id = $_POST['institution_id'] ?? 0;
        $name = trim($_POST['name'] ?? $email);
        if ($email && $password && $inst_id) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->beginTransaction();
            try {
                $pdo->prepare("INSERT INTO users (email, password_hash, role, name) VALUES (?, ?, 'instadmin', ?)")->execute([$email, $hash, $name]);
                $user_id = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO institution_admins (user_id, institution_id) VALUES (?, ?)")->execute([$user_id, $inst_id]);
                $pdo->commit();
                $msg = "Admin provisioned for institution #$inst_id.";
            } catch (Exception $e) {
                $pdo->rollback();
                $error = "Error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $user_id = $_POST['user_id'] ?? 0;
        if ($user_id) {
            $pdo->prepare("DELETE FROM institution_admins WHERE user_id = ?")->execute([$user_id]);
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
            $msg = "Admin removed.";
        }
    }
}

// Fetch institutions and admins
$institutions = $pdo->query("SELECT * FROM institutions ORDER BY name")->fetchAll();
$admins = $pdo->query("
    SELECT u.id, u.email, u.name as admin_name, i.name as inst_name
    FROM users u
    JOIN institution_admins ia ON u.id = ia.user_id
    JOIN institutions i ON ia.institution_id = i.id
    WHERE u.role = 'instadmin'
    ORDER BY i.name, u.email
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Admission System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/theme.js"></script>
    <style>
        .split-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
        .admin-row { padding: 16px; background: var(--bg-app); border-radius: 12px; border: 1px solid var(--border); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; }
        .admin-info { display: flex; align-items: center; gap: 16px; }
        .admin-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--accent-soft); color: var(--accent); display: flex; align-items: center; justify-content: center; font-weight: 700; }
    </style>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <a href="dashboard.php" data-tooltip="Dashboard"><i class="fas fa-solar-panel"></i><span>Dashboard</span></a>
            <a href="institutions.php" data-tooltip="Manage Institutions"><i class="fas fa-university"></i><span>Institutions</span></a>
            <a href="admins.php" class="active" data-tooltip="Manage Admins"><i class="fas fa-user-shield"></i><span>Admins</span></a>
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
            <h2 class="page-title">Administrative Access</h2>
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
            
            <div class="split-layout" style="margin-bottom: 32px;">
                <!-- Add Admin Section -->
                <div class="card">
                    <div class="card-header" style="margin-bottom: 24px;">
                        <h3><i class="fas fa-user-plus" style="color: var(--accent); margin-right: 12px;"></i> Provision New Admin</h3>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <label>Target Institution</label>
                        <select name="institution_id" required>
                            <option value="">-- Assign to Institution --</option>
                            <?php foreach ($institutions as $inst): ?>
                                <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['name']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Admin Email</label>
                        <input type="email" name="email" required placeholder="admin@college.edu">
                        
                        <label>Temp Password</label>
                        <input type="text" name="password" required placeholder="Generate a secure password">
                        
                        <button type="submit" style="width: 100%;"><i class="fas fa-key"></i> Create Secure Account</button>
                    </form>
                </div>

                <!-- Bulk Upload -->
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div class="card-header" style="margin-bottom: 12px;">
                            <h3><i class="fas fa-users-cog" style="color: #3b82f6; margin-right: 12px;"></i> Bulk Import Admins</h3>
                        </div>
                        <p style="font-size: 13px; color: var(--text-dim); margin-bottom: 24px;">
                            Rapidly setup multiple institutional portals using a CSV file.<br><br>
                            <strong>CSV Layout:</strong> <code>Institution Name, Email, Password</code>
                        </p>
                    </div>
                    <div style="padding: 24px; background: var(--bg-app); border-radius: 12px; border: 1px dashed var(--border);">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="bulk_upload">
                            <label style="font-size: 11px; text-transform: uppercase;">Select Data File</label>
                            <input type="file" name="csv_file" accept=".csv" required style="margin-bottom: 16px;">
                            <button type="submit" style="background: #3b82f6; width: 100%;"><i class="fas fa-upload"></i> Process Batch</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Admin List Table -->
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h3>Institutional Administrators</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Portal Administrator</th>
                            <th>Parent Institution</th>
                            <th style="text-align: right;">Authorization</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="admin-avatar">
                                        <?= strtoupper(substr($admin['email'], 0, 1)) ?>
                                    </div>
                                    <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($admin['email']) ?></div>
                                </div>
                            </td>
                            <td>
                                <div style="color: var(--text-dim); font-size: 14px;">
                                    <i class="fas fa-university" style="margin-right: 8px; opacity: 0.5;"></i>
                                    <?= htmlspecialchars($admin['inst_name'] ?? '') ?>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <form method="POST" onsubmit="return confirm('Revoke this admin\'s access?');" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= $admin['id'] ?>">
                                    <button type="submit" class="theme-toggle" style="width: 36px; height: 36px; color: #ef4444; background: rgba(239, 68, 68, 0.1);">
                                        <i class="fas fa-user-minus"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($admins)): ?>
                        <tr><td colspan="3" style="text-align: center; color: var(--text-dim); padding: 80px;">No institutional administrators provisioned yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
