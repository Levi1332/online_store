<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Ошибка: Войдите в систему!");
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    header("Location: view_cart.php");
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>
