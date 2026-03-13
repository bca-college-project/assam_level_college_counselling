<?php
require_once '../config/db.php';
checkAuth('student');
global $pdo;

$student_id = $_SESSION['student_id'];

// Fetch all institutions with their course counts
$institutions = $pdo->query("
    SELECT i.*, COUNT(c.id) as course_count
    FROM institutions i
    LEFT JOIN courses c ON c.institution_id = i.id
    GROUP BY i.id
    ORDER BY i.name
")->fetchAll();

// Fetch institutions the student has already applied to
$applied_insts_stmt = $pdo->prepare("
    SELECT DISTINCT institution_id FROM applications WHERE student_id = ?
");
$applied_insts_stmt->execute([$student_id]);
$applied_inst_ids = array_column($applied_insts_stmt->fetchAll(), 'institution_id');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Admission - Admission System</title>
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
            <a href="apply.php" class="active" data-tooltip="Apply"><i class="fas fa-file-signature"></i><span>Apply</span></a>
            <a href="upload_docs.php" data-tooltip="Upload Docs"><i class="fas fa-upload"></i><span>Upload Docs</span></a>
            <a href="my_applications.php" data-tooltip="My Applications"><i class="fas fa-list-alt"></i><span>Applications</span></a>
            <a href="../settings.php" data-tooltip="Settings"><i class="fas fa-user-cog"></i><span>Settings</span></a>
        </div>
        <div class="sidebar-footer">
            <a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="topbar">
            <h2 class="page-title">Apply for Admission</h2>
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
            <div class="card" style="margin-bottom: 24px; background: var(--accent-soft); border-color: var(--accent);">
                <h3 style="margin: 0;"><i class="fas fa-university" style="margin-right: 10px;"></i>Choose an Institution</h3>
                <p style="margin: 8px 0 0; font-size: 14px; color: var(--text-main);">Select an institution below to browse their courses and submit your application.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
                <?php foreach ($institutions as $inst): ?>
                <div class="card" style="transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
                    <div style="display: flex; align-items: flex-start; gap: 16px;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--accent-soft); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                            <i class="fas fa-university"></i>
                        </div>
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 4px; font-size: 16px;"><?= htmlspecialchars($inst['name']) ?></h3>
                            <p style="margin: 0 0 12px; font-size: 13px; color: var(--text-dim);">
                                <?= htmlspecialchars($inst['address'] ?: 'Address not specified') ?>
                            </p>
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 12px; color: var(--text-dim);">
                                    <i class="fas fa-book" style="margin-right: 4px;"></i><?= $inst['course_count'] ?> Courses
                                </span>
                                <?php if (in_array($inst['id'], $applied_inst_ids)): ?>
                                    <span style="font-size: 12px; font-weight: 600; color: #10b981;">
                                        <i class="fas fa-check-circle"></i> Applied
                                    </span>
                                <?php else: ?>
                                    <a href="apply_course.php?inst_id=<?= $inst['id'] ?>" class="btn" style="padding: 8px 16px; font-size: 13px;">
                                        Apply Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($institutions)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 80px; color: var(--text-dim);">
                    <i class="fas fa-building" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.3;"></i>
                    <p>No institutions have been registered yet. Check back later.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
