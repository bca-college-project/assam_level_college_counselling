<?php
require_once '../config/db.php';
checkAuth('superadmin');
global $pdo;

$total_insts   = $pdo->query("SELECT COUNT(*) FROM institutions")->fetchColumn();
$total_admins  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'instadmin'")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_apps    = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - Admission System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/theme.js"></script>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <a href="dashboard.php" class="active" data-tooltip="Dashboard"><i class="fas fa-solar-panel"></i><span>Dashboard</span></a>
            <a href="institutions.php" data-tooltip="Manage Institutions"><i class="fas fa-university"></i><span>Institutions</span></a>
            <a href="admins.php" data-tooltip="Manage Admins"><i class="fas fa-user-shield"></i><span>Admins</span></a>
            <a href="students.php" data-tooltip="Student Directory"><i class="fas fa-user-graduate"></i><span>Students</span></a>
            <a href="../settings.php" data-tooltip="Settings"><i class="fas fa-cog"></i><span>Settings</span></a>
        </div>
        <div class="sidebar-footer" style="display: flex; align-items: center; justify-content: center;">
            <a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="topbar">
            <h2 class="page-title">Super Admin Control Panel</h2>
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon"></i></button>
                <a href="../settings.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; cursor: pointer;">
                    <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Super Admin') ?></span>
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; color: var(--text-on-accent); font-weight: bold;">
                        SA
                    </div>
                </a>
            </div>
        </div>

        <div class="content">
            <div class="card" style="margin-bottom: 32px; background: var(--accent-soft); border-color: var(--accent);">
                <h3 style="margin: 0;">Global System Overview</h3>
                <p style="margin: 5px 0 0; text-align: left; color: var(--text-main);">Oversee all institutions, manage administrative access, and monitor student registrations.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 32px;">
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-university"></i>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: 700;"><?= $total_insts ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Institutions</div>
                    </div>
                </div>
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: 700;"><?= $total_admins ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Total Admins</div>
                    </div>
                </div>
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: 700;"><?= $total_students ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Registrations</div>
                    </div>
                </div>
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: 700;"><?= $total_courses ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Active Courses</div>
                    </div>
                </div>
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <!-- System Actions -->
                <div class="card">
                    <div class="card-header" style="margin-bottom: 24px;">
                        <h3>System Actions</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <a href="institutions.php" class="btn" style="background: var(--bg-app); border: 1px solid var(--border); color: var(--text-main); display: flex; flex-direction: column; align-items: center; padding: 24px; gap: 12px; text-decoration: none; border-radius: 12px;">
                            <i class="fas fa-plus-circle" style="font-size: 24px; color: var(--accent);"></i>
                            <span>Add Institution</span>
                        </a>
                        <a href="admins.php" class="btn" style="background: var(--bg-app); border: 1px solid var(--border); color: var(--text-main); display: flex; flex-direction: column; align-items: center; padding: 24px; gap: 12px; text-decoration: none; border-radius: 12px;">
                            <i class="fas fa-user-plus" style="font-size: 24px; color: #3b82f6;"></i>
                            <span>Create Admin</span>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity/Info -->
                <div class="card" style="background: var(--bg-card); border-style: dashed;">
                    <div style="text-align: center; padding: 10px;">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--accent-soft); color: var(--accent); display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 16px;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Security & Access</h4>
                        <p style="font-size: 14px; color: var(--text-dim); margin-top: 8px;">Ensure all institutional admins have correct permissions. Review system logs for sensitive operations.</p>
                        <a href="../settings.php" style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 14px; margin-top: 16px; display: block;">Manage Your Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
