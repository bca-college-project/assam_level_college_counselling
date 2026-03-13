<?php
$host = 'localhost';
$user = 'root';
$pass = 'iamrohanthegreat'; // Try empty first, if that fails, we can catch it.
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     $sql = file_get_contents('database.sql');
     $pdo->exec($sql);
     echo "Database initialized successfully.\n";
} catch (\PDOException $e) {
     echo "Initialization failed: " . $e->getMessage() . "\n";
}
?>
