<?php
require_once '../config/db.php';
checkAuth('instadmin');
global $pdo;

$inst_id = $_SESSION['institution_id'];

$courses_count = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE institution_id = ?");
$courses_count->execute([$inst_id]);
$courses_count = $courses_count->fetchColumn();

$pending_docs_count = $pdo->prepare("SELECT COUNT(*) FROM student_documents sd JOIN applications a ON a.student_id = sd.student_id WHERE a.institution_id = ? AND sd.status = 'pending'");
$pending_docs_count->execute([$inst_id]);
$pending_docs_count = $pending_docs_count->fetchColumn();

$apps_count = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE institution_id = ?");
$apps_count->execute([$inst_id]);
$apps_count = $apps_count->fetchColumn();

$admitted_count = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE institution_id = ? AND status = 'admitted'");
$admitted_count->execute([$inst_id]);
$admitted_count = $admitted_count->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institution Dashboard - Admission System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/theme.js"></script>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <a href="dashboard.php" class="active" data-tooltip="Dashboard"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
            <a href="courses.php" data-tooltip="Manage Courses"><i class="fas fa-book"></i><span>Courses</span></a>
            <a href="verify_documents.php" data-tooltip="Verify Documents"><i class="fas fa-user-check"></i><span>Verify Docs</span></a>
            <a href="applications.php" data-tooltip="Manage Applications"><i class="fas fa-file-invoice"></i><span>Applications</span></a>
            <a href="../settings.php" data-tooltip="Settings"><i class="fas fa-user-cog"></i><span>Settings</span></a>
        </div>
        <div class="sidebar-footer" style="display: flex; align-items: center; justify-content: center;">
            <a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="topbar">
            <h2 class="page-title">Institution Admin Dashboard</h2>
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="../settings.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; cursor: pointer;">
                    <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?>
                    </div>
                </a>
            </div>
        </div>

        <div class="content">
            <div class="card" style="margin-bottom: 32px; background: var(--accent-soft); border-color: var(--accent);">
                <h3 style="margin: 0;">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h3>
                <p style="margin: 5px 0 0; text-align: left; color: var(--text-main);">Manage your institution's admissions and courses from here.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 32px;">
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: 700;"><?= $courses_count ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Total Courses</div>
                    </div>
                </div>
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: 700;"><?= $pending_docs_count ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Pending Verifications</div>
                    </div>
                </div>
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: 700;"><?= $apps_count ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Total Applications</div>
                    </div>
                </div>
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: 700;"><?= $admitted_count ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Confirmed Admissions</div>
                    </div>
                </div>
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header" style="margin-bottom: 24px;">
                        <h3>Quick Actions</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                        <a href="courses.php" class="btn" style="background: var(--bg-app); border: 1px solid var(--border); color: var(--text-main); display: flex; flex-direction: column; align-items: center; padding: 24px; gap: 12px; text-decoration: none; border-radius: 12px;">
                            <i class="fas fa-plus-circle" style="font-size: 24px; color: var(--accent);"></i>
                            <span>Add New Course</span>
                        </a>
                        <a href="verify_documents.php" class="btn" style="background: var(--bg-app); border: 1px solid var(--border); color: var(--text-main); display: flex; flex-direction: column; align-items: center; padding: 24px; gap: 12px; text-decoration: none; border-radius: 12px;">
                            <i class="fas fa-id-card" style="font-size: 24px; color: #3b82f6;"></i>
                            <span>Verify Student Docs</span>
                        </a>
                        <a href="applications.php" class="btn" style="background: var(--bg-app); border: 1px solid var(--border); color: var(--text-main); display: flex; flex-direction: column; align-items: center; padding: 24px; gap: 12px; text-decoration: none; border-radius: 12px;">
                            <i class="fas fa-list-check" style="font-size: 24px; color: #10b981;"></i>
                            <span>Manage Merit List</span>
                        </a>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card" style="background: var(--bg-card); border-style: dashed;">
                    <div style="text-align: center; padding: 10px;">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--accent-soft); color: var(--accent); display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 16px;">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h4>Need Help?</h4>
                        <p style="font-size: 14px; color: var(--text-dim); margin-top: 8px;">Ensure all student documents are verified before they appear in the merit list processing system.</p>
                        <a href="#" style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 14px; margin-top: 16px; display: block;">View Admin Guide</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
