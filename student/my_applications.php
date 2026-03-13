<?php
require_once '../config/db.php';
checkAuth('student');
global $pdo;

$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare("
    SELECT a.*, i.name as inst_name,
           c.name as course_name, c.type as course_type,
           cm.name as minor_name
    FROM applications a
    JOIN institutions i ON a.institution_id = i.id
    JOIN courses c ON a.course_id = c.id
    LEFT JOIN course_minors cm ON a.minor_id = cm.id
    WHERE a.student_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$student_id]);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Admission System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/theme.js"></script>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <a href="dashboard.php" data-tooltip="Home"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="upload_docs.php" data-tooltip="Upload Documents"><i class="fas fa-file-upload"></i><span>Upload Docs</span></a>
            <a href="apply.php" data-tooltip="Apply for Courses"><i class="fas fa-graduation-cap"></i><span>Apply Course</span></a>
            <a href="my_applications.php" class="active" data-tooltip="My Applications"><i class="fas fa-list-ul"></i><span>Applications</span></a>
            <a href="../settings.php" data-tooltip="Settings"><i class="fas fa-user-cog"></i><span>Settings</span></a>
        </div>
        <div class="sidebar-footer">
            <a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="topbar">
            <h2 class="page-title">My Applications</h2>
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
            <?php if(isset($_SESSION['flash_msg'])): ?>
                <div class="success"><?= htmlspecialchars($_SESSION['flash_msg']); unset($_SESSION['flash_msg']); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h3><i class="fas fa-history" style="margin-right: 10px; color: var(--accent);"></i> Application History</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Institution & Course</th>
                            <th style="text-align: center;">Details</th>
                            <th style="text-align: center;">Round</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 16px;"><?= htmlspecialchars($app['inst_name']) ?></div>
                                <div style="color: var(--text-dim); font-size: 14px;"><?= htmlspecialchars($app['course_name']) ?></div>
                            </td>
                            <td style="text-align: center; font-size: 13px;">
                                <span style="display: block; font-weight: 500;"><?= ucfirst($app['course_type']) ?></span>
                                <?php if($app['minor_name']): ?>
                                    <span style="color: var(--text-dim);">Minor: <?= htmlspecialchars($app['minor_name']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: var(--accent-soft); color: var(--accent); font-weight: bold;">
                                    <?= $app['round_number'] === 'spot' ? 'S' : $app['round_number'] ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="status-badge <?= $app['status'] ?>"><?= ucfirst($app['status']) ?></span>
                                <?php if($app['status'] === 'approved' && $app['payment_status'] === 'pending'): ?>
                                    <div style="color: #ef4444; font-size: 11px; margin-top: 5px; font-weight: 600;">Awaiting Payment</div>
                                <?php elseif($app['payment_status'] === 'received'): ?>
                                    <div style="color: #22c55e; font-size: 11px; margin-top: 5px; font-weight: 600;">Fee Received</div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if($app['status'] === 'approved' && $app['payment_status'] === 'pending'): ?>
                                    <button class="btn-small" style="background: var(--accent); color: var(--text-on-accent);" onclick="alert('Proceed to college for offline payment.')">Pay Fee</button>
                                <?php elseif($app['status'] === 'admitted'): ?>
                                    <span style="color: #22c55e; font-size: 13px; font-weight: 600;"><i class="fas fa-check-circle"></i> Admitted</span>
                                <?php else: ?>
                                    <span style="color: var(--text-dim); font-size: 13px;">Processing...</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($applications)): ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-dim); padding: 60px;">No applications found yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
