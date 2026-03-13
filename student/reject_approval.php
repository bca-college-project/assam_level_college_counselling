<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id = $_POST['app_id'] ?? 0;
    $student_id = $_SESSION['student_id'];
    
    // Verify application belongs to this student and is approved
    $stmt = $pdo->prepare("SELECT id, status, round_number FROM applications WHERE id = ? AND student_id = ? AND status = 'approved'");
    $stmt->execute([$app_id, $student_id]);
    $app = $stmt->fetch();
    
    if ($app) {
        $next_round = '';
        if ($app['round_number'] === '1') $next_round = '2';
        elseif ($app['round_number'] === '2') $next_round = '3';
        elseif ($app['round_number'] === '3') $next_round = 'spot';
        
        if ($next_round) {
            // Return status to 'applied' but for the next round
            $pdo->prepare("UPDATE applications SET round_number = ?, status = 'applied' WHERE id = ?")->execute([$next_round, $app_id]);
            $_SESSION['flash_msg'] = "You declined the seat. Your application has automatically moved to Merit List $next_round.";
        } else {
            // Refused during spot admission => Permanently rejected
            $pdo->prepare("UPDATE applications SET status = 'rejected' WHERE id = ?")->execute([$app_id]);
            $_SESSION['flash_msg'] = "You declined the Spot Admission. Your application is now closed.";
        }
    }
}
header("Location: my_applications.php");
exit;
?>
