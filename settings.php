<?php
require_once 'config/db.php';
checkAuth(['student', 'instadmin', 'superadmin']);
global $pdo;

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];
$msg     = '';
$error   = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name && $phone) {
        $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?")
            ->execute([$name, $phone, $user_id]);
        $_SESSION['user_name'] = $name;
        $msg = "Profile updated successfully.";
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } else {
        $error = "Name and phone cannot be empty.";
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $old_pass     = $_POST['old_password'] ?? '';
    $new_pass     = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    if (!password_verify($old_pass, $user['password_hash'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "New passwords do not match.";
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
            ->execute([$hash, $user_id]);
        $msg = "Password changed successfully.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admission System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/theme.js"></script>
    <script src="js/auth.js"></script>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <?php if ($role === 'superadmin'): ?>
                <a href="superadmin/dashboard.php" data-tooltip="Dashboard"><i class="fas fa-solar-panel"></i><span>Dashboard</span></a>
                <a href="superadmin/institutions.php" data-tooltip="Manage Institutions"><i class="fas fa-university"></i><span>Institutions</span></a>
                <a href="superadmin/admins.php" data-tooltip="Manage Admins"><i class="fas fa-user-shield"></i><span>Admins</span></a>
                <a href="superadmin/students.php" data-tooltip="Student Directory"><i class="fas fa-user-graduate"></i><span>Students</span></a>
                <a href="settings.php" class="active" data-tooltip="Settings"><i class="fas fa-cog"></i><span>Settings</span></a>
            <?php elseif ($role === 'instadmin'): ?>
                <a href="instadmin/dashboard.php" data-tooltip="Dashboard"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
                <a href="instadmin/courses.php" data-tooltip="Manage Courses"><i class="fas fa-book"></i><span>Courses</span></a>
                <a href="instadmin/verify_documents.php" data-tooltip="Verify Documents"><i class="fas fa-user-check"></i><span>Verify Docs</span></a>
                <a href="instadmin/applications.php" data-tooltip="Manage Applications"><i class="fas fa-file-invoice"></i><span>Applications</span></a>
                <a href="settings.php" class="active" data-tooltip="Settings"><i class="fas fa-user-cog"></i><span>Settings</span></a>
            <?php else: ?>
                <a href="student/dashboard.php" data-tooltip="Home"><i class="fas fa-home"></i><span>Home</span></a>
                <a href="student/upload_docs.php" data-tooltip="Upload Documents"><i class="fas fa-file-upload"></i><span>Upload Docs</span></a>
                <a href="student/apply.php" data-tooltip="Apply for Courses"><i class="fas fa-graduation-cap"></i><span>Apply Course</span></a>
                <a href="student/my_applications.php" data-tooltip="My Applications"><i class="fas fa-list-ul"></i><span>Applications</span></a>
                <a href="settings.php" class="active" data-tooltip="Settings"><i class="fas fa-user-cog"></i><span>Settings</span></a>
            <?php endif; ?>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="topbar">
            <h2 class="page-title">Account Settings</h2>
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="settings.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; cursor: pointer;">
                    <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                    </div>
                </a>
            </div>
        </div>

        <div class="content">
            <?php if(!empty($msg)) echo "<div class='success'>$msg</div>"; ?>
            <?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
                <!-- Profile Section -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-circle"></i> Profile Information</h3>
                    </div>
                    <form method="POST">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        
                        <label>Email Address</label>
                        <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
                        
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        
                        <button type="submit" name="update_profile">Save Changes</button>
                    </form>
                </div>

                <!-- Security Section -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-shield-alt"></i> Security</h3>
                    </div>
                    <form method="POST">
                        <label>Current Password</label>
                        <div class="password-field-container">
                            <input type="password" id="current_password" name="old_password" required>
                            <i class="fas fa-eye password-toggle" id="toggle_current" onclick="togglePassword('current_password', 'toggle_current')"></i>
                        </div>
                        <div style="margin-bottom: 16px;"></div> <!-- Spacer -->
                        
                        <label>New Password</label>
                        <div class="password-field-container">
                            <input type="password" id="new_password" name="new_password" required>
                            <i class="fas fa-eye password-toggle" id="toggle_new" onclick="togglePassword('new_password', 'toggle_new')"></i>
                        </div>
                        <div style="margin-bottom: 16px;"></div> <!-- Spacer -->
                        
                        <label>Confirm New Password</label>
                        <div class="password-field-container">
                            <input type="password" id="confirm_new_password" name="confirm_password" required>
                            <i class="fas fa-eye password-toggle" id="toggle_confirm_new" onclick="togglePassword('confirm_new_password', 'toggle_confirm_new')"></i>
                        </div>
                        <div style="margin-bottom: 20px;"></div> <!-- Spacer -->
                        
                        <button type="submit" name="change_password">Update Password</button>
                    </form>
                </div>

                <!-- Preferences Section -->
                <div class="card" style="grid-column: span 2;">
                    <div class="card-header">
                        <h3><i class="fas fa-sliders-h"></i> Appearance</h3>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="margin: 0; font-weight: 500;">Interface Theme</p>
                            <span style="color: var(--text-dim); font-size: 14px;">Switch between light and dark mode</span>
                        </div>
                        <button class="theme-toggle" onclick="toggleTheme()" style="width: auto; border-radius: 12px; padding: 10px 20px; gap: 10px;">
                            <i class="fas fa-circle-half-stroke"></i>
                            Switch Theme
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
