<?php
session_start();
require_once 'config.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Вы должны войти в систему!"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;

if (!$product_id) {
    echo json_encode(["status" => "error", "message" => "Некорректный товар."]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT id AS cart_id FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cart_item) {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $product_id]);
        $cart_id = $pdo->lastInsertId();
    } else {
        $cart_id = $cart_item['cart_id'];
    }

    echo json_encode(["status" => "success", "cart_id" => $cart_id]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
