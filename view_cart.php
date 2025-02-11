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
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT c.id AS cart_id, p.id AS product_id, p.name, p.price, c.quantity, p.image
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <?php if (empty($cart_items)): ?>
        <p class="empty-cart">Корзина пуста. Пожалуйста, добавьте товары в корзину.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Товар</th>
                <th>Цена</th>
                <th>Количество</th>
                <th>Общая стоимость</th>
                <th>Действия</th>
				<th></th>
            </tr>
            <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td>
                        <a href="product.php?id=<?= htmlspecialchars($item['product_id']) ?>" class="product-link">
                            <img src="<?= htmlspecialchars($item['image']) ?>" width="60" alt="<?= htmlspecialchars($item['name']) ?>"> 
                            <?= htmlspecialchars($item['name']) ?>
                        </a>
                    </td>
                    <td><?= number_format($item['price'], 2) ?> руб.</td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['price'] * $item['quantity'], 2) ?> руб.</td>
                    <td class="actions">
                        <a href="remove_from_cart.php?id=<?= $item['cart_id'] ?>">Удалить</a> 
                    </td>
					<td class= "actoins1">
						<a href="order_form.php?cart_id=<?= $item['cart_id'] ?>&product_id=<?= $item['product_id'] ?>" class="order-button">Оформить заказ</a>
					</td>
						
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
