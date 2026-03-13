<?php
require_once 'config/db.php';
echo $pdo->query('SELECT VERSION()')->fetchColumn();
?>
