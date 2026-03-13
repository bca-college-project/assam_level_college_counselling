<?php
require_once 'config/db.php';
require_once 'config/mailer.php';
redirectIfLoggedIn(); // Already logged in → go to your dashboard

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name && $email && $phone && $password && $confirm_password) {
        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $pdo->beginTransaction();
            try {
                // Register user
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'student')");
                $stmt->execute([$name, $email, $phone, $hash]);
                $user_id = $pdo->lastInsertId();

                // Generate Secure OTP (6 digits)
                $otp = sprintf("%06d", mt_rand(0, 999999));
                $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                // Initialize student record with OTP
                $stmt = $pdo->prepare("INSERT INTO students (user_id, is_otp_verified, otp_code, otp_expiry) VALUES (?, 0, ?, ?)");
                $stmt->execute([$user_id, $otp, $expiry]);

                $pdo->commit();
                
                // Send OTP Email
                sendOTPEmail($email, $otp);
                
                $_SESSION['registration_email'] = $email;
                // No longer store OTP in session for better security - verify against DB
                
                header("Location: verify_otp.php");
                exit;
            } catch (\PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = "Registration failed. Email might already exist.";
            }
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Admission System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            margin: 0;
            padding: 20px;
        }
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        .auth-card {
            width: 100%;
            max-width: 480px;
            padding: 48px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }
        [data-theme="dark"] .auth-card {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
        }
    </style>
    <script src="js/theme.js"></script>
    <script src="js/auth.js"></script>
</head>
<body>
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="width: 56px; height: 56px; background: var(--accent); color: white; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; margin-bottom: 20px;">AS</div>
            <h2 style="font-size: 28px; font-weight: 800; color: var(--text-main); margin-bottom: 8px;">Create Account</h2>
            <p style="color: var(--text-dim);">Join the next generation of scholars</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="error" style="margin-bottom: 24px;">
                <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: var(--text-dim);">FULL NAME</label>
                <div style="position: relative;">
                    <i class="fas fa-user" style="position: absolute; left: 16px; top: 14px; color: var(--accent); opacity: 0.6;"></i>
                    <input type="text" name="name" required placeholder="Your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" style="width: 100%; padding: 12px 16px 12px 48px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-app); color: var(--text-main);">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: var(--text-dim);">EMAIL ADDRESS</label>
                <div style="position: relative;">
                    <i class="fas fa-envelope" style="position: absolute; left: 16px; top: 14px; color: var(--accent); opacity: 0.6;"></i>
                    <input type="email" name="email" required placeholder="john@university.edu" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" style="width: 100%; padding: 12px 16px 12px 48px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-app); color: var(--text-main);">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: var(--text-dim);">MOBILE NUMBER</label>
                <div style="position: relative;">
                    <i class="fas fa-phone" style="position: absolute; left: 16px; top: 14px; color: var(--accent); opacity: 0.6;"></i>
                    <input type="text" name="phone" required placeholder="10-digit mobile number" style="width: 100%; padding: 12px 16px 12px 48px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-app); color: var(--text-main);">
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: var(--text-dim);">SECURE PASSWORD</label>
                <div class="password-field-container">
                    <i class="fas fa-shield-halved" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--accent); opacity: 0.6; z-index: 10;"></i>
                    <input type="password" id="reg_password" name="password" required placeholder="Create a strong password" style="width: 100%; padding: 12px 16px 12px 48px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-app); color: var(--text-main);">
                    <i class="fas fa-eye password-toggle" id="toggle_reg_password" onclick="togglePassword('reg_password', 'toggle_reg_password')"></i>
                </div>
            </div>

            <div style="margin-bottom: 32px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: var(--text-dim);">CONFIRM PASSWORD</label>
                <div class="password-field-container">
                    <i class="fas fa-check-double" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--accent); opacity: 0.6; z-index: 10;"></i>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat your password" style="width: 100%; padding: 12px 16px 12px 48px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-app); color: var(--text-main);">
                    <i class="fas fa-eye password-toggle" id="toggle_confirm_password" onclick="togglePassword('confirm_password', 'toggle_confirm_password')"></i>
                </div>
            </div>
            
            <button type="submit" style="width: 100%; padding: 14px; font-weight: 700; font-size: 16px; border-radius: 12px; background: var(--accent); color: white;">Register Now</button>
        </form>

        <div style="margin-top: 32px; text-align: center; font-size: 14px; color: var(--text-dim);">
            Already have an account? <a href="login.php" style="color: var(--accent); font-weight: 600; text-decoration: none;">Sign in here</a>
        </div>
    </div>
</body>
</html>
