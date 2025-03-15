<?php
session_start();
require_once 'config.php';

$cart_count = 0;



if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();
}
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetchColumn() ?: 0;
}
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заказы</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Дополнительные стили для страницы заказов */
        .order {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 4px;
            background-color: #fff;
        }
        .order h2 {
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        table th {
            background-color: #f4f4f4;
        }
        .order-info {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
    <div class="container">
        <?php if (empty($orders)): ?>
            <p>Вы ещё не сделали ни одного заказа.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order">
                    <h2>Заказ №<?= htmlspecialchars($order['id']) ?></h2>
                    <div class="order-info">
                        <p><strong>Дата заказа:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
                        <p><strong>Статус:</strong> <?= htmlspecialchars($order['status']) ?></p>
                        <p><strong>Имя:</strong> <?= htmlspecialchars($order['name']) ?></p>
                        <p><strong>Адрес:</strong> <?= htmlspecialchars($order['address']) ?></p>
                        
                    </div>
                    <?php
                    $stmtItems = $pdo->prepare("
                        SELECT oi.*, p.name, p.image 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?
                    ");
                    $stmtItems->execute([$order['id']]);
                    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (!empty($items)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Товар</th>
                                    <th>Изображение</th>
                                    <th>Количество</th>
                                    <th>Цена за ед.</th>
                                    <th>Сумма</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $order_total = 0;
                                foreach ($items as $item):
                                    $subtotal = $item['price'] * $item['quantity'];
                                    $order_total += $subtotal;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td>
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="50">
                                        <?php else: ?>
                                            Нет изображения
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                    <td><?= number_format($item['price'], 2, '.', ' ') ?> руб.</td>
                                    <td><?= number_format($subtotal, 2, '.', ' ') ?> руб.</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p><strong>Общая сумма заказа:</strong> <?= number_format($order_total, 2, '.', ' ') ?> руб.</p>
                    <?php else: ?>
                        <p>Позиции заказа отсутствуют.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
