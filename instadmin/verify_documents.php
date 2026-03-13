<?php
require_once '../config/db.php';
checkAuth('instadmin');
global $pdo;

$inst_id = $_SESSION['institution_id'];

// Handle verify/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $student_id = $_POST['student_id'] ?? 0;

    if ($action === 'verify_docs' && $student_id) {
        $pdo->prepare("UPDATE student_documents SET status = 'verified' WHERE student_id = ?")->execute([$student_id]);
        $pdo->prepare("UPDATE students SET is_docs_verified = 1 WHERE id = ?")->execute([$student_id]);
        $msg = "Documents verified successfully.";
    } elseif ($action === 'reject_docs' && $student_id) {
        $pdo->prepare("UPDATE student_documents SET status = 'rejected' WHERE student_id = ?")->execute([$student_id]);
        $msg = "Documents rejected.";
    }
}

// Fetch students who have applied to this institution
$stmt = $pdo->prepare("
    SELECT DISTINCT s.id, u.email, u.phone, s.is_docs_verified,
        (SELECT COUNT(*) FROM student_documents sd WHERE sd.student_id = s.id) as doc_count,
        (SELECT sd2.status FROM student_documents sd2 WHERE sd2.student_id = s.id LIMIT 1) as doc_status
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN applications a ON a.student_id = s.id
    WHERE a.institution_id = ?
    ORDER BY s.is_docs_verified ASC, u.email ASC
");
$stmt->execute([$inst_id]);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Documents - Admission System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/theme.js"></script>
    <style>
        .doc-link { display: flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; padding: 10px; background: var(--bg-app); border-radius: 8px; border: 1px solid var(--border); transition: all 0.2s; margin-bottom: 8px; }
        .doc-link:hover { border-color: var(--accent); background: var(--accent-soft); }
    </style>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <a href="dashboard.php" data-tooltip="Dashboard"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
            <a href="courses.php" data-tooltip="Manage Courses"><i class="fas fa-book"></i><span>Courses</span></a>
            <a href="verify_documents.php" class="active" data-tooltip="Verify Documents"><i class="fas fa-user-check"></i><span>Verify Docs</span></a>
            <a href="applications.php" data-tooltip="Manage Applications"><i class="fas fa-file-invoice"></i><span>Applications</span></a>
            <a href="../settings.php" data-tooltip="Settings"><i class="fas fa-user-cog"></i><span>Settings</span></a>
        </div>
        <div class="sidebar-footer">
            <a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="topbar">
            <h2 class="page-title">Document Verification</h2>
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
                    <h3><i class="fas fa-id-card-clip" style="color: var(--accent); margin-right: 12px;"></i> Pending verifications</h3>
                    <p style="margin: 5px 0 0; text-align: left; color: var(--text-dim);">Review uploaded documents to mark students as eligible for merit rounds.</p>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Student Information</th>
                            <th style="text-align: center;">Status</th>
                            <th>Attached Documents</th>
                            <th style="text-align: right;">Decision</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): 
                            $docs = $pdo->prepare("SELECT * FROM student_documents WHERE student_id = ?");
                            $docs->execute([$student['id']]);
                            $docs = $docs->fetchAll();
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 16px;"><?= htmlspecialchars($student['email']) ?></div>
                                <div style="font-size: 13px; color: var(--text-dim);">Phone: <?= htmlspecialchars($student['phone']) ?></div>
                            </td>
                            <td style="text-align: center;">
                                <span class="status-badge <?= $student['is_docs_verified'] ? 'verified' : $student['doc_status'] ?>">
                                    <?= $student['is_docs_verified'] ? 'VERIFIED' : strtoupper($student['doc_status'] ?? 'PENDING') ?>
                                </span>
                            </td>
                            <td>
                                <div style="max-width: 300px;">
                                    <?php foreach($docs as $doc): ?>
                                        <a href="../uploads/<?= htmlspecialchars($doc['filename']) ?>" target="_blank" class="doc-link">
                                            <i class="fas fa-file-pdf" style="color: #ef4444;"></i>
                                            <span style="font-size: 13px;"><?= htmlspecialchars($doc['doc_type']) ?></span>
                                            <i class="fas fa-external-link-alt" style="margin-left: auto; font-size: 10px; opacity: 0.5;"></i>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <?php if(!empty($docs)): ?>
                                <div style="display: flex; flex-direction: column; gap: 8px; align-items: flex-end;">
                                    <form method="POST" onsubmit="return confirm('Approve all documents?');">
                                        <input type="hidden" name="action" value="verify_docs">
                                        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                        <button type="submit" style="background: #10b981; padding: 10px 16px; font-size: 13px;">
                                            <i class="fas fa-check"></i> Approve All
                                        </button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Reject docs? Student will need to re-upload.');">
                                        <input type="hidden" name="action" value="reject_docs">
                                        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                        <button type="submit" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; padding: 10px 16px; font-size: 13px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </div>
                                <?php else: ?>
                                    <span style="color: var(--text-dim); font-size: 12px;">Awaiting Upload</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($students)): ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--text-dim); padding: 80px;">No students requiring verification at this time.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
