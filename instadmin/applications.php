<?php
require_once '../config/db.php';
checkAuth('instadmin');
global $pdo;

$inst_id = $_SESSION['institution_id'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $app_id = $_POST['app_id'] ?? 0;

    if ($action === 'approve' && $app_id) {
        $pdo->prepare("UPDATE applications SET status = 'approved' WHERE id = ? AND institution_id = ?")->execute([$app_id, $inst_id]);
        $msg = "Seat offered to applicant.";
    } elseif ($action === 'reject' && $app_id) {
        $stmt = $pdo->prepare("SELECT round_number FROM applications WHERE id = ?");
        $stmt->execute([$app_id]);
        $curr = $stmt->fetchColumn();
        $next = ($curr === 'spot') ? 'spot' : ($curr + 1);
        $pdo->prepare("UPDATE applications SET status = 'rejected', round_number = ? WHERE id = ? AND institution_id = ?")->execute([$next, $app_id, $inst_id]);
        $msg = "Application waitlisted to next merit round.";
    } elseif ($action === 'mark_paid' && $app_id) {
        $pdo->prepare("UPDATE applications SET payment_status = 'received', status = 'admitted' WHERE id = ? AND institution_id = ?")->execute([$app_id, $inst_id]);
        $msg = "Fee confirmed. Student admitted.";
    }
}

// Fetch applications
$stmt = $pdo->prepare("
    SELECT a.*, u.email as student_email, u.phone,
           c.name as course_name, c.type as course_type,
           cm.name as minor_name
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN users u ON s.user_id = u.id
    JOIN courses c ON a.course_id = c.id
    LEFT JOIN course_minors cm ON a.minor_id = cm.id
    WHERE a.institution_id = ?
    ORDER BY a.round_number, a.created_at DESC
");
$stmt->execute([$inst_id]);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications - Admission System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/theme.js"></script>
    <style>
        .merit-round { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent); background: var(--accent-soft); padding: 4px 10px; border-radius: 20px; display: inline-block; margin-bottom: 8px; }
        .payment-status { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; margin-top: 8px; }
        .payment-pending { color: #ef4444; }
        .payment-received { color: #10b981; }
    </style>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <a href="dashboard.php" data-tooltip="Dashboard"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
            <a href="courses.php" data-tooltip="Manage Courses"><i class="fas fa-book"></i><span>Courses</span></a>
            <a href="verify_documents.php" data-tooltip="Verify Documents"><i class="fas fa-user-check"></i><span>Verify Docs</span></a>
            <a href="applications.php" class="active" data-tooltip="Manage Applications"><i class="fas fa-file-invoice"></i><span>Applications</span></a>
            <a href="../settings.php" data-tooltip="Settings"><i class="fas fa-user-cog"></i><span>Settings</span></a>
        </div>
        <div class="sidebar-footer">
            <a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="topbar">
            <h2 class="page-title">Merit List Management</h2>
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
            <?php if(isset($msg)) echo "<div class='success'>$msg</div>"; ?>
            <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h3><i class="fas fa-list-ol" style="color: var(--accent); margin-right: 12px;"></i> Application Queue</h3>
                    <p style="margin: 5px 0 0; text-align: left; color: var(--text-dim);">Evaluate student applications and manage seat allocations across merit rounds.</p>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Applicant Details</th>
                            <th>Applied Program</th>
                            <th>Merit Status</th>
                            <th style="text-align: center;">Decisioning</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                        <tr style="<?= $app['status'] === 'admitted' ? 'background: var(--accent-soft);' : '' ?>">
                            <td>
                                <div style="font-weight: 600; font-size: 16px;"><?= htmlspecialchars($app['student_email']) ?></div>
                                <div style="font-size: 13px; color: var(--text-dim);">Mob: <?= htmlspecialchars($app['phone']) ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?= htmlspecialchars($app['course_name']) ?></div>
                                <div style="font-size: 13px; color: var(--text-dim);">
                                    <?= ucfirst($app['course_type']) ?> Program
                                    <?php if($app['minor_name']): ?>
                                        <span style="color: var(--accent);">•</span> Minor: <?= htmlspecialchars($app['minor_name']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="merit-round">
                                    <?php 
                                        if($app['round_number'] === 'spot') echo "Spot Admission";
                                        else echo "Merit Phase " . $app['round_number']; 
                                    ?>
                                </div>
                                <br>
                                <span class="status-badge <?= $app['status'] ?>">
                                    <?= strtoupper($app['status']) ?>
                                </span>
                                <?php if($app['status'] === 'approved' && $app['payment_status'] === 'pending'): ?>
                                    <div class="payment-status payment-pending">
                                        <i class="fas fa-clock"></i> Awaiting Payment
                                    </div>
                                <?php elseif($app['payment_status'] === 'received'): ?>
                                    <div class="payment-status payment-received">
                                        <i class="fas fa-check-circle"></i> Fee Received
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <?php if($app['status'] === 'applied'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                            <button type="submit" class="btn" style="padding: 8px 16px; font-size: 13px; background: #10b981;">
                                                <i class="fas fa-check"></i> Offer Seat
                                            </button>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('Move to next merit list?');">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                            <button type="submit" class="btn" style="padding: 8px 16px; font-size: 13px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444;">
                                                <i class="fas fa-arrow-right"></i> Waitlist
                                            </button>
                                        </form>
                                    <?php elseif($app['status'] === 'approved' && $app['payment_status'] === 'pending'): ?>
                                        <form method="POST" onsubmit="return confirm('Confirm offline fee receipt?');">
                                            <input type="hidden" name="action" value="mark_paid">
                                            <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                            <button type="submit" class="btn" style="padding: 8px 16px; font-size: 13px; background: var(--accent); color: var(--text-on-accent);">
                                                <i class="fas fa-money-bill-wave"></i> Confirm Payment
                                            </button>
                                        </form>
                                    <?php elseif($app['status'] === 'admitted'): ?>
                                        <div style="padding: 8px 16px; border-radius: 8px; background: #10b981; color: white; font-size: 12px; font-weight: 600;">
                                            <i class="fas fa-user-graduate"></i> ADMITTED
                                        </div>
                                    <?php elseif($app['status'] === 'rejected'): ?>
                                        <div style="padding: 8px 16px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 12px; font-weight: 600;">
                                            <i class="fas fa-user-slash"></i> REJECTED
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($applications)): ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--text-dim); padding: 80px;">Queue is currently empty.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
