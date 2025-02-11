<?php

require_once 'config.php';

$cart_count = 0;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetchColumn() ?: 0;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Интернет-магазин</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
        <div class="header-container">
            <div class="logo">
                <h1><a href="index.php">Интернет-магазин</a></h1>
            </div>
            <nav class="header-menu">
                <a href="index.php">Главная</a>
                <a href="view_cart.php">Корзина (<span id="cart-count"><?= $cart_count ?></span>)</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="my_orders.php">Мои заказы</a>
                    <a href="logout.php">Выход</a>
                <?php else: ?>
                    <a href="login.php">Вход</a>
                    <a href="register.php">Регистрация</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

