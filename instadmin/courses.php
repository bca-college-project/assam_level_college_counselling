<?php
require_once '../config/db.php';
checkAuth('instadmin');
global $pdo;

$inst_id = $_SESSION['institution_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_course') {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'general';
        if ($name) {
            $pdo->prepare("INSERT INTO courses (institution_id, name, type) VALUES (?, ?, ?)")->execute([$inst_id, $name, $type]);
            $msg = "Course '$name' added successfully.";
        }
    } elseif ($action === 'add_minor') {
        $course_id = $_POST['course_id'] ?? 0;
        $minor_name = trim($_POST['minor_name'] ?? '');
        if ($course_id && $minor_name) {
            $pdo->prepare("INSERT INTO course_minors (course_id, name) VALUES (?, ?)")->execute([$course_id, $minor_name]);
            $msg = "Minor subject added.";
        }
    } elseif ($action === 'delete_course') {
        $course_id = $_POST['course_id'] ?? 0;
        if ($course_id) {
            $pdo->prepare("DELETE FROM courses WHERE id = ? AND institution_id = ?")->execute([$course_id, $inst_id]);
            $msg = "Course deleted.";
        }
    } elseif ($action === 'delete_minor') {
        $minor_id = $_POST['minor_id'] ?? 0;
        if ($minor_id) {
            $pdo->prepare("DELETE FROM course_minors WHERE id = ?")->execute([$minor_id]);
            $msg = "Minor subject removed.";
        }
    } elseif ($action === 'bulk_upload' && isset($_FILES['csv_file'])) {
        $rows = array_map('str_getcsv', file($_FILES['csv_file']['tmp_name']));
        foreach ($rows as $row) {
            if (count($row) < 2) continue;
            $cname = trim($row[0]);
            $ctype = strtolower(trim($row[1])) === 'professional' ? 'professional' : 'general';
            $pdo->prepare("INSERT INTO courses (institution_id, name, type) VALUES (?, ?, ?)")->execute([$inst_id, $cname, $ctype]);
            $cid = $pdo->lastInsertId();
            if (isset($row[2]) && trim($row[2])) {
                foreach (explode('|', $row[2]) as $m) {
                    $m = trim($m);
                    if ($m) $pdo->prepare("INSERT INTO course_minors (course_id, name) VALUES (?, ?)")->execute([$cid, $m]);
                }
            }
        }
        $msg = "Bulk import complete.";
    }
}

// Fetch courses
$stmt = $pdo->prepare("SELECT * FROM courses WHERE institution_id = ? ORDER BY type, name");
$stmt->execute([$inst_id]);
$courses = $stmt->fetchAll();

// Fetch minor subjects grouped by course_id
$minor_subjects = [];
if ($courses) {
    $course_ids = array_column($courses, 'id');
    $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
    $stmt2 = $pdo->prepare("SELECT * FROM course_minors WHERE course_id IN ($placeholders)");
    $stmt2->execute($course_ids);
    foreach ($stmt2->fetchAll() as $m) {
        $minor_subjects[$m['course_id']][] = $m;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admission System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/theme.js"></script>
    <style>
        .split-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
        .minor-item { padding: 12px; background: var(--bg-app); border-radius: 8px; border: 1px solid var(--border); margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; }
        .minor-tag { font-size: 12px; font-weight: 500; color: var(--text-dim); }
    </style>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <a href="dashboard.php" data-tooltip="Dashboard"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
            <a href="courses.php" class="active" data-tooltip="Manage Courses"><i class="fas fa-book"></i><span>Courses</span></a>
            <a href="verify_documents.php" data-tooltip="Verify Documents"><i class="fas fa-user-check"></i><span>Verify Docs</span></a>
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
            <h2 class="page-title">Course Management</h2>
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
            
            <div class="split-layout" style="margin-bottom: 32px;">
                <!-- Add Course -->
                <div class="card">
                    <div class="card-header" style="margin-bottom: 24px;">
                        <h3><i class="fas fa-plus-circle" style="margin-right: 10px; color: var(--accent);"></i> Create New Course</h3>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_course">
                        <label>Course Name</label>
                        <input type="text" name="name" required placeholder="e.g. B.Tech Computer Science">
                        
                        <label>Academic Category</label>
                        <select name="type" required>
                            <option value="general">General (Academic)</option>
                            <option value="professional">Professional (Technical)</option>
                        </select>
                        
                        <button type="submit"><i class="fas fa-check"></i> Add Course</button>
                    </form>
                </div>

                <!-- Add Minor -->
                <div class="card">
                    <div class="card-header" style="margin-bottom: 24px;">
                        <h3><i class="fas fa-book-open" style="margin-right: 10px; color: #3b82f6;"></i> Configure Minors</h3>
                    </div>
                    <?php 
                        $has_general = false;
                        foreach($courses as $c) if($c['type'] === 'general') $has_general = true;
                    ?>
                    <?php if($has_general): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_minor">
                        <label>Select Course</label>
                        <select name="course_id" required>
                            <option value="">-- Choose Course --</option>
                            <?php foreach($courses as $c): ?>
                                <?php if($c['type'] === 'general'): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        
                        <label>Minor Subject Name</label>
                        <input type="text" name="minor_name" required placeholder="e.g. Mathematics">
                        
                        <button type="submit" style="background: #3b82f6;"><i class="fas fa-plus"></i> Add Minor</button>
                    </form>
                    <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-dim); background: var(--bg-app); border-radius: 12px; border: 1px dashed var(--border);">
                        <i class="fas fa-info-circle" style="font-size: 24px; margin-bottom: 12px;"></i>
                        <p>No general courses available to add minors.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CSV Upload -->
            <div class="card" style="margin-bottom: 32px; border: 1px dashed var(--accent);">
                <div style="display: flex; align-items: flex-start; gap: 24px;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--accent-soft); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                        <i class="fas fa-file-csv"></i>
                    </div>
                    <div style="flex-grow: 1;">
                        <h4 style="margin: 0 0 8px;">Bulk Course Import</h4>
                        <p style="margin: 0 0 20px; font-size: 14px; text-align: left; color: var(--text-dim);">
                            Format: <code>Course Name, Type (General/Professional), Minor1|Minor2</code>
                        </p>
                        <form method="POST" enctype="multipart/form-data" style="display: flex; gap: 16px; align-items: center;">
                            <input type="hidden" name="action" value="bulk_upload">
                            <input type="file" name="csv_file" accept=".csv" required style="margin: 0; padding: 8px; font-size: 13px;">
                            <button type="submit" style="width: auto; padding: 0 24px; height: 42px;">Import CSV</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Course Table -->
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h3>Course Catalog</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 300px;">Course Offering</th>
                            <th style="width: 150px; text-align: center;">Category</th>
                            <th>Minors / Specializations</th>
                            <th style="width: 120px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $c): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 16px;"><?= htmlspecialchars($c['name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-dim);">ID: CRS-<?= str_pad($c['id'], 4, '0', STR_PAD_LEFT) ?></div>
                            </td>
                            <td style="text-align: center;">
                                <span class="status-badge <?= $c['type'] === 'general' ? 'pending' : 'verified' ?>" style="font-size: 11px;">
                                    <?= strtoupper($c['type']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <?php if($c['type'] === 'general' && isset($minor_subjects[$c['id']])): ?>
                                    <?php foreach($minor_subjects[$c['id']] as $m): ?>
                                        <div class="minor-item" style="margin: 0;">
                                            <span class="minor-tag"><?= htmlspecialchars($m['name']) ?></span>
                                            <form method="POST" style="margin-left: 8px;">
                                                <input type="hidden" name="action" value="delete_minor">
                                                <input type="hidden" name="minor_id" value="<?= $m['id'] ?>">
                                                <button type="submit" style="background: none; border: none; padding: 0; color: #ef4444; cursor: pointer; font-size: 10px;" onclick="return confirm('Remove minor?');">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                <?php elseif($c['type'] === 'professional'): ?>
                                    <span style="color: var(--text-dim); font-size: 13px; font-style: italic;">Stand-alone Professional Program</span>
                                <?php else: ?>
                                    <span style="color: var(--text-dim); font-size: 13px;">No minors configured</span>
                                <?php endif; ?>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <form method="POST" onsubmit="return confirm('Delete this course?');" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_course">
                                    <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="theme-toggle" style="width: 36px; height: 36px; padding: 0; color: #ef4444; background: rgba(239, 68, 68, 0.1);">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($courses)): ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--text-dim); padding: 60px;">No courses registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
