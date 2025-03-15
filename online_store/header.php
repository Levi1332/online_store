<?php
require_once 'config.php';

$cart_count = 0;
$avatar = 'default-avatar.png'; // Аватар по умолчанию

if (isset($_SESSION['user_id'])) {
    // Получаем количество товаров в корзине
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetchColumn() ?: 0;

    // Получаем аватар пользователя
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $avatar = $stmt->fetchColumn() ?: 'default-avatar.png'; // Если нет аватарки, ставим дефолтную
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Интернет-магазин</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .header-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 5px;
            vertical-align: middle;
        }
    </style>
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
                
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'seller'])): ?>
                    <!-- Показываем ссылку только для администраторов и продавцов -->
                    <a href="add_product.php">Добавить товар</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">Админка</a>
                <?php endif; ?>

                <a href="profile.php">
                    Личный кабинет
                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Аватар" class="header-avatar">
                </a>
                
                <a href="logout.php">Выход</a>
            <?php else: ?>
                <a href="login.php">Вход</a>
                <a href="register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
