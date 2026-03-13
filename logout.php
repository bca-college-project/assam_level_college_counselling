<?php
require_once 'config/db.php';
start_secure_session();

// Clear Remember Me token in DB if it exists
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Clear cookie
setcookie('remember_me', '', time() - 3600, '/');

// Clear session
session_unset();
session_destroy();

header("Location: login.php?msg=Logged out successfully.");
exit;
?>
