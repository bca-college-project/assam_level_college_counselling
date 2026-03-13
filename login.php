<?php
require_once 'config/db.php';
start_secure_session();
redirectIfLoggedIn(); // Already logged in → go to your dashboard

// Restore session from remember_me cookie if session expired
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['user_name'] = $user['name'] ?: ($user['role'] === 'superadmin' ? 'Super Admin' : ($user['role'] === 'instadmin' ? 'Institution Admin' : 'Student'));

        if ($user['role'] === 'student') {
            $stmt2 = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
            $stmt2->execute([$user['id']]);
            $student = $stmt2->fetch();
            $_SESSION['student_id'] = $student['id'] ?? null;
        } elseif ($user['role'] === 'instadmin') {
            $stmt2 = $pdo->prepare("SELECT institution_id FROM institution_admins WHERE user_id = ?");
            $stmt2->execute([$user['id']]);
            $inst = $stmt2->fetch();
            $_SESSION['institution_id'] = $inst['institution_id'] ?? null;
        }
    }
}

// Redirect if already logged in
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'student':
            header("Location: student/dashboard.php");
            break;
        case 'instadmin':
            header("Location: instadmin/dashboard.php");
            break;
        case 'superadmin':
            header("Location: superadmin/dashboard.php");
            break;
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['user_name'] = $user['name'] ?: ($user['role'] === 'superadmin' ? 'Super Admin' : ($user['role'] === 'instadmin' ? 'Institution Admin' : 'Student'));

        // Remember Me Logic
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $updateToken = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $updateToken->execute([$token, $user['id']]);
            setcookie('remember_me', $token, time() + (86400 * 30), "/", "", false, true);
        }

        if ($user['role'] === 'student') {
            $stmt2 = $pdo->prepare("SELECT id, is_otp_verified FROM students WHERE user_id = ?");
            $stmt2->execute([$user['id']]);
            $student = $stmt2->fetch();

            if ($student && !$student['is_otp_verified']) {
                $_SESSION['registration_email'] = $email;
                header("Location: verify_otp.php");
                exit;
            }
            $_SESSION['student_id'] = $student['id'];
            header("Location: student/dashboard.php");
        } elseif ($user['role'] === 'instadmin') {
            $stmt2 = $pdo->prepare("SELECT institution_id FROM institution_admins WHERE user_id = ?");
            $stmt2->execute([$user['id']]);
            $inst_admin = $stmt2->fetch();
            $_SESSION['institution_id'] = $inst_admin['institution_id'] ?? null;
            header("Location: instadmin/dashboard.php");
        } else {
            header("Location: superadmin/dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Portal Login - Admission System</title>
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
            box-sizing: border-box;
        }

        [data-theme="dark"] body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            padding: 48px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        [data-theme="dark"] .login-card {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .login-logo {
            width: 64px;
            height: 64px;
            background: #2563eb;
            color: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            margin: 0 auto 32px;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.4);
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 800;
            text-align: center;
            margin: 0 0 8px 0;
            color: var(--text-main);
        }

        .login-header p {
            text-align: center;
            color: var(--text-dim);
            margin: 0 0 40px 0;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-main);
        }

        .form-group label i {
            color: var(--accent);
        }

        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--bg-app);
            color: var(--text-main);
            font-size: 15px;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus {
            border-color: var(--accent);
        }

        /* ── Remember Me ── */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
            cursor: pointer;
            user-select: none;
        }

        .remember-row input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            min-width: 18px;
            flex-shrink: 0;
            border: 2px solid var(--border, #cbd5e1);
            border-radius: 5px;
            background: var(--bg-app, #fff);
            cursor: pointer;
            position: relative;
            transition: background 0.18s, border-color 0.18s;
            margin: 0;
        }

        .remember-row input[type="checkbox"]:checked {
            background: var(--accent, #2563eb);
            border-color: var(--accent, #2563eb);
        }

        .remember-row input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            top: 1px;
            left: 5px;
            width: 5px;
            height: 9px;
            border: 2px solid #fff;
            border-top: none;
            border-left: none;
            transform: rotate(45deg);
        }

        .remember-row span {
            font-size: 14px;
            color: var(--text-dim);
            line-height: 1.2;
        }

        /* ── Submit Button ── */
        .btn-submit {
            width: 100%;
            padding: 14px;
            font-weight: 700;
            font-size: 16px;
            border-radius: 12px;
            background: var(--accent, #2563eb);
            color: var(--text-on-accent, #fff);
            border: none;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
        }

        .btn-submit:hover  { opacity: 0.92; }
        .btn-submit:active { transform: scale(0.98); }

        /* ── Alerts ── */
        .alert-error {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            padding: 12px 16px;
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border-radius: 12px;
            font-size: 14px;
        }

        .alert-success {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 24px;
            padding: 12px 16px;
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border-radius: 12px;
            font-size: 14px;
        }

        /* ── Register Link ── */
        .register-link {
            margin-top: 32px;
            text-align: center;
            font-size: 14px;
            color: var(--text-dim);
        }

        .register-link a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }

        .register-link a:hover { text-decoration: underline; }
    </style>
    <script src="js/theme.js"></script>
    <script src="js/auth.js"></script>
</head>
<body>
    <div class="login-card">

        <div class="login-logo">AS</div>

        <div class="login-header">
            <h2>Welcome Back</h2>
            <p>Access your admission workspace</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input type="email" name="email" required placeholder="name@example.com">
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="password-field-container">
                    <input type="password" id="login_password" name="password" required placeholder="••••••••">
                    <i class="fas fa-eye password-toggle" id="toggle_login_password"
                       onclick="togglePassword('login_password', 'toggle_login_password')"></i>
                </div>
            </div>

            <label class="remember-row">
                <input type="checkbox" name="remember">
                <span>Stay signed in for 30 days</span>
            </label>

            <button type="submit" class="btn-submit">Sign In</button>

        </form>

        <div class="register-link">
            New student? <a href="register.php">Create an account</a>
        </div>

    </div>
</body>
</html>