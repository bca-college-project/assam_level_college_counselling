<?php
// config/db.php — loads secrets from .env at project root

// ─── Load .env ───────────────────────────────────────────────────────────────
function loadEnv(string $path): void
{
    if (!file_exists($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$key, $val] = array_map('trim', explode('=', $line, 2));
        if (!getenv($key)) putenv("$key=$val");
        $_ENV[$key] = $val;
    }
}

loadEnv(__DIR__ . '/../.env');

// ─── Database connection ─────────────────────────────────────────────────────
$host    = getenv('DB_HOST')    ?: 'localhost';
$db      = getenv('DB_NAME')    ?: 'college_counselling';
$user    = getenv('DB_USER')    ?: 'root';
$pass    = getenv('DB_PASS')    ?: '';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// ─── Secure session helper ───────────────────────────────────────────────────
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Lax');

        $lifetime     = 86400 * 30; // 30 days
        $cookie_secure = filter_var(getenv('COOKIE_SECURE') ?: 'false', FILTER_VALIDATE_BOOLEAN);

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $cookie_secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }
}

// ─── Auth middleware ─────────────────────────────────────────────────────────
function checkAuth($requiredRole = null): void
{
    start_secure_session();
    global $pdo;

    // Helper: build the correct root-relative redirect path
    $depth = (
        strpos($_SERVER['PHP_SELF'], '/student/')   !== false ||
        strpos($_SERVER['PHP_SELF'], '/instadmin/') !== false ||
        strpos($_SERVER['PHP_SELF'], '/superadmin/') !== false
    ) ? '../' : '';

    // 1. Not in session — try Remember Me cookie
    if (!isset($_SESSION['user_id'])) {
        if (isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];
            $stmt  = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['user_name'] = $user['name'] ?: (
                    $user['role'] === 'superadmin' ? 'Super Admin' :
                    ($user['role'] === 'instadmin'  ? 'Institution Admin' : 'Student')
                );

                if ($user['role'] === 'student') {
                    $s = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
                    $s->execute([$user['id']]);
                    if ($row = $s->fetch()) $_SESSION['student_id'] = $row['id'];
                }
                if ($user['role'] === 'instadmin') {
                    $s = $pdo->prepare("SELECT institution_id FROM institution_admins WHERE user_id = ?");
                    $s->execute([$user['id']]);
                    if ($row = $s->fetch()) $_SESSION['institution_id'] = $row['institution_id'];
                }
            } else {
                setcookie('remember_me', '', time() - 3600, '/');
                header("Location: {$depth}login.php");
                exit;
            }
        } else {
            header("Location: {$depth}login.php");
            exit;
        }
    }

    // 2. Role check
    if ($requiredRole !== null) {
        $allowed = is_array($requiredRole)
            ? in_array($_SESSION['role'], $requiredRole)
            : ($_SESSION['role'] === $requiredRole);

        if (!$allowed) {
            $map = [
                'student'    => 'student/dashboard.php',
                'instadmin'  => 'instadmin/dashboard.php',
                'superadmin' => 'superadmin/dashboard.php',
            ];
            $target = $depth . ($map[$_SESSION['role']] ?? 'login.php');
            header("Location: $target?error=unauthorized");
            exit;
        }
    }
}

// ─── Redirect already-logged-in users away from public pages ────────────────
function redirectIfLoggedIn(): void
{
    start_secure_session();
    if (!isset($_SESSION['user_id'])) return; // Not logged in – nothing to do

    $map = [
        'student'    => 'student/dashboard.php',
        'instadmin'  => 'instadmin/dashboard.php',
        'superadmin' => 'superadmin/dashboard.php',
    ];
    $target = $map[$_SESSION['role']] ?? 'login.php';
    header("Location: $target");
    exit;
}
?>
