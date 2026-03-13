<?php
require_once 'config/db.php';
start_secure_session();

if (!isset($_SESSION['registration_email'])) {
    header("Location: register.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_entered = $_POST['otp'] ?? '';
    
    // Fetch OTP from database for this email
    $stmt = $pdo->prepare("SELECT u.id, u.name, s.id as student_id, s.otp_code, s.otp_expiry 
                           FROM users u 
                           JOIN students s ON u.id = s.user_id 
                           WHERE u.email = ?");
    $stmt->execute([$_SESSION['registration_email']]);
    $user = $stmt->fetch();
    
    if ($user) {
        if ($user['otp_code'] === $otp_entered) {
            $now = date('Y-m-d H:i:s');
            if ($now <= $user['otp_expiry']) {
                // Verified! Update student record
                $update = $pdo->prepare("UPDATE students SET is_otp_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE id = ?");
                $update->execute([$user['student_id']]);
                
                // Log them in
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'] ?: explode('@', $_SESSION['registration_email'])[0];
                $_SESSION['role']      = 'student';
                $_SESSION['student_id'] = $user['student_id'];
                unset($_SESSION['registration_email']);
                
                header("Location: student/dashboard.php");
                exit;
            } else {
                $error = "OTP has expired. Please register again.";
            }
        } else {
            $error = "Invalid OTP code. Please try again.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Admission System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            margin: 0;
        }
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
    </style>
    <script src="js/theme.js"></script>
</head>
<body>
    <div class="card" style="width: 100%; max-width: 400px; padding: 40px; text-align: center;">
        <h2 style="margin-bottom: 10px;">Verify Your Email</h2>
        <p style="color: var(--text-dim); margin-bottom: 30px;">We've sent a 6-digit code to <strong><?= htmlspecialchars($_SESSION['registration_email']) ?></strong></p>

        <?php if(isset($error)): ?>
            <div class="error" style="margin-bottom: 20px;"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 24px;">
                <input type="text" name="otp" required maxlength="6" placeholder="000000" 
                       style="width: 100%; padding: 14px; font-size: 24px; text-align: center; letter-spacing: 8px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-app); color: var(--text-main);">
            </div>
            <button type="submit" class="btn" style="width: 100%; padding: 14px; font-weight: 700; font-size: 16px; border-radius: 12px; background: var(--accent); color: var(--text-on-accent); border: none; cursor: pointer;">Verify OTP</button>
        </form>

        <div style="margin-top: 30px; font-size: 14px; color: var(--text-dim);">
            Didn't receive the code? <a href="register.php" style="color: var(--accent); text-decoration: none; font-weight: 600;">Restart Registration</a>
        </div>
    </div>
</body>
</html>
