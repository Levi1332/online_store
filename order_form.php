<?php
session_start();
require_once 'config.php';

$cart_count = 0;

if (!isset($_SESSION['user_id'])) {
    die("Ошибка: Войдите в систему, чтобы оформить заказ!");
}

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetchColumn() ?: 0;
}

$cart_id = isset($_GET['cart_id']) ? $_GET['cart_id'] : null;
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : null;


if (!$cart_id || !$product_id) {
    die("Ошибка: Неверные данные для оформления заказа.");
}

// Подготавливаем запрос для получения товара из корзины
$stmt = $pdo->prepare("SELECT p.*, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND p.id = ?");
$stmt->execute([$cart_id, $product_id]);
$item = $stmt->fetch();

// Если товар не найден, выводим ошибку
if (!$item) {
    die("Ошибка: Товар не найден в корзине.");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа</title>
    <link rel="stylesheet" href="style.css?v=1.0">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <!-- Блок с информацией о товаре -->
    <div class="order-summary">
        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
        <div class="details">
            <h2><?= htmlspecialchars($item['name']) ?></h2>
            <p>Цена: <?= number_format($item['price'], 2) ?> руб.</p>
            <p>Количество: <?= $item['quantity'] ?></p>
            <p>Общая стоимость: <?= number_format($item['price'] * $item['quantity'], 2) ?> руб.</p>
        </div>
    </div>

    <!-- Форма для ввода реквизитов заказа -->
    <form class="order-form" action="place_order.php" method="post">
        <!-- Передаем скрытыми полями cart_id и product_id -->
        <input type="hidden" name="cart_id" value="<?= $cart_id ?>">
        <input type="hidden" name="product_id" value="<?= $product_id ?>">
        
        <div>
            <label for="name">ФИО</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div>
            <label for="address">Адрес доставки</label>
            <textarea id="address" name="address" required></textarea>
        </div>
        <div>
            <label for="phone">Телефон</label>
            <input type="tel" id="phone" name="phone" required>
        </div>
        <div>
            <label for="card_number">Номер карты</label>
            <input type="text" id="card_number" name="card_number" required>
        </div>
        <div>
            <label for="expiry_date">Дата окончания</label>
            <input type="month" id="expiry_date" name="expiry_date" required>
        </div>
        <div>
            <label for="cvv">CVV</label>
            <input type="text" id="cvv" name="cvv" required>
        </div>
        <button type="submit">Оформить заказ</button>
    </form>
</div>
</body>
</html>
