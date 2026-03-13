<?php
require_once '../config/db.php';
checkAuth('student');
global $pdo;

$student_id = $_SESSION['student_id'];

// Fetch stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ?");
$stmt->execute([$student_id]);
$app_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM student_documents WHERE student_id = ?");
$stmt->execute([$student_id]);
$doc_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ? AND status IN ('approved', 'admitted')");
$stmt->execute([$student_id]);
$approved_count = $stmt->fetchColumn();

// Fetch recent applications
$stmt = $pdo->prepare("
    SELECT a.*, i.name as inst_name, c.name as course_name, c.type as course_type
    FROM applications a
    JOIN institutions i ON a.institution_id = i.id
    JOIN courses c ON a.course_id = c.id
    WHERE a.student_id = ?
    ORDER BY a.created_at DESC
    LIMIT 5
");
$stmt->execute([$student_id]);
$recent_apps = $stmt->fetchAll();

// Is OTP verified?
$stmt = $pdo->prepare("SELECT is_otp_verified, is_docs_verified FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student_info = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Admission System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/theme.js"></script>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <a href="dashboard.php" class="active" data-tooltip="Home"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="apply.php" data-tooltip="Apply"><i class="fas fa-file-signature"></i><span>Apply</span></a>
            <a href="upload_docs.php" data-tooltip="Upload Docs"><i class="fas fa-upload"></i><span>Upload Docs</span></a>
            <a href="my_applications.php" data-tooltip="My Applications"><i class="fas fa-list-alt"></i><span>Applications</span></a>
            <a href="../settings.php" data-tooltip="Settings"><i class="fas fa-user-cog"></i><span>Settings</span></a>
        </div>
        <div class="sidebar-footer" style="display: flex; align-items: center; justify-content: center;">
            <a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="topbar">
            <h2 class="page-title">Student Dashboard</h2>
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="../settings.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; cursor: pointer;">
                    <span style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?></span>
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'S', 0, 1)) ?>
                    </div>
                </a>
            </div>
        </div>

        <div class="content">
            <!-- Welcome Banner -->
            <div class="card" style="margin-bottom: 32px; background: var(--accent-soft); border-color: var(--accent);">
                <h3 style="margin: 0;">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?>!</h3>
                <p style="margin: 5px 0 0; text-align: left; color: var(--text-main);">Track your admission applications and manage your documents from here.</p>
            </div>

            <?php if (!$student_info['is_docs_verified']): ?>
            <div class="card" style="margin-bottom: 24px; border: 1px solid #f59e0b; background: rgba(245, 158, 11, 0.08);">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 24px;"></i>
                    <div>
                        <strong>Documents not yet verified.</strong>
                        <p style="margin: 4px 0 0; font-size: 14px; color: var(--text-dim);">Please upload your documents so the institution can verify them.</p>
                    </div>
                    <a href="upload_docs.php" class="btn" style="margin-left: auto; padding: 10px 20px; font-size: 13px;">Upload Now</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 32px;">
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(37, 99, 235, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <div>
                        <div style="font-size: 28px; font-weight: 700;"><?= $app_count ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Total Applications</div>
                    </div>
                </div>
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <div style="font-size: 28px; font-weight: 700;"><?= $approved_count ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Approved / Admitted</div>
                    </div>
                </div>
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div>
                        <div style="font-size: 28px; font-weight: 700;"><?= $doc_count ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Documents Uploaded</div>
                    </div>
                </div>
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-id-badge"></i>
                    </div>
                    <div>
                        <div style="font-size: 28px; font-weight: 700;"><?= $student_info['is_docs_verified'] ? '✓' : '✗' ?></div>
                        <div style="font-size: 14px; color: var(--text-dim);">Docs Verified</div>
                    </div>
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="fas fa-history" style="color: var(--accent); margin-right: 10px;"></i>Recent Applications</h3>
                    <a href="my_applications.php" style="font-size: 13px; color: var(--accent); text-decoration: none; font-weight: 600;">View All →</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Institution</th>
                            <th>Course</th>
                            <th style="text-align: center;">Round</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_apps as $app): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?= htmlspecialchars($app['inst_name']) ?></div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($app['course_name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-dim);"><?= ucfirst($app['course_type']) ?></div>
                            </td>
                            <td style="text-align: center;">
                                <span style="font-size: 12px; font-weight: 600; color: var(--accent);">
                                    <?= $app['round_number'] === 'spot' ? 'Spot' : 'Merit #' . $app['round_number'] ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="status-badge <?= $app['status'] ?>"><?= strtoupper($app['status']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_apps)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-dim); padding: 60px;">
                                <i class="fas fa-inbox" style="font-size: 36px; display: block; margin-bottom: 12px; opacity: 0.3;"></i>
                                No applications yet. <a href="apply.php" style="color: var(--accent);">Apply to an institution →</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
