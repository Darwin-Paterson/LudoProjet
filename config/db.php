<?php
// config/db.php
$host = 'localhost';
$db   = 'evisamdg_gammaindb';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    session_start();
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// Helper: Check Login
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>