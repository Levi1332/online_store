<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Ошибка: Войдите в систему!");
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header("Location: view_cart.php");
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>
