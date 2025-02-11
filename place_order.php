<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Ошибка: Войдите в систему, чтобы оформить заказ!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id     = $_SESSION['user_id'];
    $cart_id     = $_POST['cart_id'];
    $product_id  = $_POST['product_id'];
    $name        = $_POST['name'];
    $address     = $_POST['address'];
    $phone       = $_POST['phone'];
    $card_number = $_POST['card_number'];
    $expiry_date = $_POST['expiry_date'];
    $cvv         = $_POST['cvv'];

    try {
      
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, name, address, phone, card_number, expiry_date, cvv, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'new')");
        $stmt->execute([$user_id, $name, $address, $phone, $card_number, $expiry_date, $cvv]);

        $order_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            SELECT p.id AS product_id, p.price, c.quantity
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.id = ?
        ");
        $stmt->execute([$cart_id]);
        $item = $stmt->fetch();

        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);

        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->execute([$cart_id]);

        header("Location: order_confirmation.php?order_id=$order_id");
        exit();

    } catch (PDOException $e) {
        die("Ошибка: " . $e->getMessage());
    }
}
?>
