<?php
session_start();
require_once 'config.php';

if (!isset($_GET['order_id'])) {
    die("Ошибка: Неверный запрос.");
}

$order_id = $_GET['order_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        die("Ошибка: Заказ не найден.");
    }
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подтверждение заказа</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <p>Спасибо за ваш заказ.</p>
    <p>Номер заказа: <?= htmlspecialchars($order['id']) ?></p>
</div>
</body>
</html>
