<?php
require_once '../config/db.php';
checkAuth('student');
global $pdo;

$student_id = $_SESSION['student_id'];

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $doc_type = $_POST['doc_type'] ?? '';
    if ($doc_type && $_FILES['document']['error'] === 0) {
        $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
        $filename = $student_id . '_' . $doc_type . '_' . time() . '.' . $ext;
        $upload_path = '../uploads/' . $filename;
        if (move_uploaded_file($_FILES['document']['tmp_name'], $upload_path)) {
            // Delete old doc of same type
            $pdo->prepare("DELETE FROM student_documents WHERE student_id = ? AND doc_type = ?")->execute([$student_id, $doc_type]);
            // Insert new
            $pdo->prepare("INSERT INTO student_documents (student_id, doc_type, filename, status) VALUES (?, ?, ?, 'pending')")->execute([$student_id, $doc_type, $filename]);
            $msg = "Document uploaded successfully.";
        } else {
            $error = "Failed to upload document.";
        }
    }
}

// Fetch existing documents
$stmt = $pdo->prepare("SELECT * FROM student_documents WHERE student_id = ?");
$stmt->execute([$student_id]);
$documents = [];
foreach ($stmt->fetchAll() as $doc) {
    $documents[$doc['doc_type']] = $doc;
}

// Overall document status
$doc_status = 'none';
if (!empty($documents)) {
    $statuses = array_column(array_values($documents), 'status');
    if (in_array('rejected', $statuses)) $doc_status = 'rejected';
    elseif (in_array('pending', $statuses)) $doc_status = 'pending';
    else $doc_status = 'verified';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents - Admission System</title>
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
            <a href="upload_docs.php" class="active" data-tooltip="Upload Documents"><i class="fas fa-file-upload"></i><span>Upload Docs</span></a>
            <a href="apply.php" data-tooltip="Apply for Courses"><i class="fas fa-graduation-cap"></i><span>Apply Course</span></a>
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
            <h2 class="page-title">Upload Documents</h2>
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
            <?php if(isset($msg)) echo "<div class='success'>$msg</div>"; ?>
            <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
            
            <div class="card" style="background: var(--accent-soft); border-color: var(--accent);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0;">Overall Verification Status</h4>
                        <p style="margin: 5px 0 0; font-size: 14px; text-align: left; color: var(--text-main);">Current status of your document review</p>
                    </div>
                    <span class="status-badge <?= $doc_status ?>" style="font-size: 16px; padding: 8px 16px;"><?= strtoupper($doc_status) ?></span>
                </div>
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 32px; align-items: flex-start;">
                <!-- Upload Form -->
                <?php if($doc_status !== 'verified'): ?>
                <div class="card">
                    <div class="card-header" style="margin-bottom: 24px;">
                        <h3><i class="fas fa-upload" style="margin-right: 10px; color: var(--accent);"></i> New Upload</h3>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <label>Document Type</label>
                        <select name="document_type" required>
                            <option value="10th Marksheet">10th Marksheet</option>
                            <option value="12th Marksheet">12th Marksheet</option>
                            <option value="Transfer Certificate">Transfer Certificate</option>
                            <option value="Aadhar/ID Proof">Aadhar / ID Proof</option>
                            <option value="Other">Other</option>
                        </select>
                        
                        <label>File (PDF, JPG, PNG)</label>
                        <div style="position: relative; margin-bottom: 24px;">
                            <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required style="padding: 10px; background: var(--bg-app); border-style: dashed;">
                        </div>
                        
                        <button type="submit"><i class="fas fa-cloud-upload-alt"></i> Upload Document</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="card">
                    <div style="text-align: center; padding: 20px;">
                        <i class="fas fa-check-circle" style="font-size: 48px; color: #22c55e; margin-bottom: 20px;"></i>
                        <h4>Verified</h4>
                        <p style="font-size: 14px;">Your documents are verified. Uploading is disabled.</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Document List -->
                <div class="card">
                    <div class="card-header" style="margin-bottom: 24px;">
                        <h3><i class="fas fa-list" style="margin-right: 10px; color: var(--accent);"></i> Uploaded Files</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 500;"><?= htmlspecialchars($doc['document_type']) ?></div>
                                    <div style="font-size: 12px; color: var(--text-dim);"><?= date('M d, Y', strtotime($doc['created_at'] ?? 'now')) ?></div>
                                </td>
                                <td><span class="status-badge <?= $doc['status'] ?>"><?= ucfirst($doc['status']) ?></span></td>
                                <td style="text-align: right;">
                                    <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="theme-toggle" style="display: inline-flex; width: 32px; height: 32px; padding: 0; margin-right: 8px; text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if($doc['status'] !== 'verified' && $doc_status !== 'verified'): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete document?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                                        <button type="submit" class="theme-toggle" style="width: 32px; height: 32px; padding: 0; color: #ef4444; background: rgba(239, 68, 68, 0.1);">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($documents)): ?>
                            <tr><td colspan="3" style="text-align: center; color: var(--text-dim); padding: 40px;">No documents uploaded yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
