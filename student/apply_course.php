<?php
require_once '../config/db.php';
checkAuth('student');
global $pdo;

$student_id = $_SESSION['student_id'];
$inst_id = $_GET['inst_id'] ?? null;

if (!$inst_id) {
    header("Location: apply.php");
    exit;
}

// Handle application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'] ?? 0;
    $minor_id = $_POST['minor_id'] ?? null;
    $minor_id = ($minor_id && $minor_id > 0) ? $minor_id : null;

    if ($course_id) {
        // Check for duplicate application
        $check = $pdo->prepare("SELECT id FROM applications WHERE student_id = ? AND institution_id = ? AND course_id = ?");
        $check->execute([$student_id, $inst_id, $course_id]);
        if ($check->fetch()) {
            $error = "You have already applied for this course at this institution.";
        } else {
            $pdo->prepare("INSERT INTO applications (student_id, institution_id, course_id, minor_id, status, payment_status, round_number) VALUES (?, ?, ?, ?, 'applied', 'pending', 1)")->execute([$student_id, $inst_id, $course_id, $minor_id]);
            header("Location: my_applications.php?msg=Application submitted successfully.");
            exit;
        }
    }
}

// Fetch institution info
$inst = $pdo->prepare("SELECT * FROM institutions WHERE id = ?");
$inst->execute([$inst_id]);
$institution = $inst->fetch();
if (!$institution) {
    header("Location: apply.php");
    exit;
}

// Fetch courses for this institution
$stmt = $pdo->prepare("SELECT * FROM courses WHERE institution_id = ? ORDER BY type, name");
$stmt->execute([$inst_id]);
$courses = $stmt->fetchAll();

// Fetch minors
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
    <title>Select Course - Admission System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/theme.js"></script>
    <script>
        // Pass PHP array to JS
        const minorSubjects = <?= json_encode($minor_subjects) ?>;
        const courses = <?= json_encode($courses) ?>;

        function updateForm() {
            const courseSelect = document.getElementById('course_select');
            const minorDiv = document.getElementById('minor_div');
            const minorSelect = document.getElementById('minor_select');
            const selectedCourseId = courseSelect.value;
            
            if(!selectedCourseId) {
                minorDiv.style.display = 'none';
                minorSelect.required = false;
                return;
            }

            // Find course type
            let courseType = '';
            for(let i=0; i<courses.length; i++) {
                if(courses[i].id == selectedCourseId) {
                    courseType = courses[i].type;
                    break;
                }
            }

            if(courseType === 'general') {
                minorDiv.style.display = 'block';
                minorSelect.required = true;
                
                // Populate options
                minorSelect.innerHTML = '<option value="">-- Select Minor Subject --</option>';
                const minors = minorSubjects[selectedCourseId] || [];
                minors.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = m.name;
                    minorSelect.appendChild(opt);
                });
                
                if(minors.length === 0) {
                    minorSelect.innerHTML = '<option value="">-- No Minor Subjects Available --</option>';
                    minorSelect.required = false; 
                }
            } else {
                minorDiv.style.display = 'none';
                minorSelect.required = false;
                minorSelect.innerHTML = '<option value="">Not required</option>';
            }
        }
    </script>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">A<span>S</span></div>
        <div class="sidebar-nav">
            <a href="dashboard.php" data-tooltip="Home"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="upload_docs.php" data-tooltip="Upload Documents"><i class="fas fa-file-upload"></i><span>Upload Docs</span></a>
            <a href="apply.php" class="active" data-tooltip="Apply for Courses"><i class="fas fa-graduation-cap"></i><span>Apply Course</span></a>
            <a href="my_applications.php" data-tooltip="My Applications"><i class="fas fa-list-ul"></i><span>Applications</span></a>
            <a href="../settings.php" data-tooltip="Settings"><i class="fas fa-user-cog"></i><span>Settings</span></a>
        </div>
        <div class="sidebar-footer">
            <a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="topbar">
            <h2 class="page-title">Course Selection</h2>
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
            <a href="apply.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-dim); text-decoration: none; margin-bottom: 24px; font-weight: 500;">
                <i class="fas fa-chevron-left"></i> Change College
            </a>

            <div class="grid" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 32px;">
                <div class="card">
                    <div class="card-header" style="margin-bottom: 24px;">
                        <h3><i class="fas fa-university" style="margin-right: 10px; color: var(--accent);"></i> <?= htmlspecialchars($institution['name']) ?></h3>
                        <p style="margin: 5px 0 0; text-align: left; color: var(--text-dim);">Select your preferred course below.</p>
                    </div>

                    <form method="POST">
                        <label>Available Courses</label>
                        <select name="course_id" id="course_select" required onchange="updateForm()">
                            <option value="">-- Choose a Course --</option>
                            <?php 
                            $current_type = '';
                            foreach($courses as $c): 
                                if($current_type !== $c['type']) {
                                    if($current_type !== '') echo "</optgroup>";
                                    echo "<optgroup label='" . ucfirst($c['type']) . " Specializations'>";
                                    $current_type = $c['type'];
                                }
                            ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; if($current_type !== '') echo "</optgroup>"; ?>
                        </select>

                        <div id="minor_div" style="display:none; margin-top: 24px; padding: 20px; background: var(--bg-app); border-radius: 12px; border: 1px solid var(--border);">
                            <label><i class="fas fa-book-open" style="margin-right: 8px; color: var(--accent);"></i> Select Minor Subject</label>
                            <select name="minor_id" id="minor_select" style="margin-bottom: 0;">
                                <option value="">-- Choose Minor --</option>
                            </select>
                            <p style="margin: 10px 0 0; font-size: 12px; text-align: left; color: var(--text-dim);">General courses require a minor subject selection for eligibility.</p>
                        </div>

                        <div style="margin-top: 32px;">
                            <button type="submit" style="height: 52px; font-size: 16px;">
                                Submit Application <i class="fas fa-paper-plane" style="margin-left: 10px;"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div>
                    <div class="card" style="background: var(--accent-soft); border-color: var(--accent); color: var(--text-on-accent);">
                        <h4><i class="fas fa-info-circle"></i> Application Tip</h4>
                        <p style="text-align: left; font-size: 14px; line-height: 1.6; margin-top: 12px; opacity: 0.9;">
                            Professional courses are highly specialized. General courses offer flexibility with a minor subject. Choose wisely as your selection determines your merit list placement.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
