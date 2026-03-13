<?php
require_once '../config/db.php';
checkAuth('superadmin');
global $pdo;

$students = $pdo->query("
    SELECT u.id, u.email, u.phone, s.id as student_id,
           s.is_otp_verified, s.is_docs_verified,
           (SELECT COUNT(*) FROM applications a WHERE a.student_id = s.id) as app_count
    FROM users u
    JOIN students s ON u.id = s.user_id
    WHERE u.role = 'student'
    ORDER BY u.email ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admission System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/theme.js"></script>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <a href="dashboard.php" data-tooltip="Dashboard"><i class="fas fa-solar-panel"></i><span>Dashboard</span></a>
            <a href="institutions.php" data-tooltip="Manage Institutions"><i class="fas fa-university"></i><span>Institutions</span></a>
            <a href="admins.php" data-tooltip="Manage Admins"><i class="fas fa-user-shield"></i><span>Admins</span></a>
            <a href="students.php" class="active" data-tooltip="Student Directory"><i class="fas fa-user-graduate"></i><span>Students</span></a>
            <a href="../settings.php" data-tooltip="Settings"><i class="fas fa-cog"></i><span>Settings</span></a>
        </div>
        <div class="sidebar-footer">
            <a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="topbar">
            <h2 class="page-title">Student Directory</h2>
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
            
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h3><i class="fas fa-users" style="color: var(--accent); margin-right: 12px;"></i> Universal Student Records</h3>
                    <p style="margin: 5px 0 0; text-align: left; color: var(--text-dim);">A comprehensive list of all students registered across the entire admission network.</p>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Student Information</th>
                            <th style="text-align: center;">Identity Status</th>
                            <th style="text-align: center;">Academic Audit</th>
                            <th style="text-align: right;">Operations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--text-main); font-size: 16px;"><?= htmlspecialchars($student['email']) ?></div>
                                <div style="font-size: 13px; color: var(--text-dim);"><i class="fas fa-phone-alt" style="margin-right: 6px; opacity: 0.5;"></i> <?= htmlspecialchars($student['phone'] ?? '') ?></div>
                            </td>
                            <td style="text-align: center;">
                                <?php if($student['is_otp_verified']): ?>
                                    <span style="color: #10b981; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; background: rgba(16, 185, 129, 0.1); padding: 4px 12px; border-radius: 20px;">
                                        <i class="fas fa-check-double"></i> VERIFIED
                                    </span>
                                <?php else: ?>
                                    <span style="color: #ef4444; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; background: rgba(239, 68, 68, 0.1); padding: 4px 12px; border-radius: 20px;">
                                        <i class="fas fa-exclamation-triangle"></i> PENDING
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <span class="status-badge <?= $student['is_docs_verified'] ? 'verified' : 'pending' ?>">
                                    <?= $student['is_docs_verified'] ? 'VERIFIED' : 'PENDING' ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <form method="POST" onsubmit="return confirm('CRITICAL: Delete this student record permanently?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= $student['id'] ?>">
                                    <button type="submit" class="theme-toggle" style="width: 36px; height: 36px; color: #ef4444; background: rgba(239, 68, 68, 0.1);">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($students)): ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--text-dim); padding: 100px;">No registered students found in the system.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
