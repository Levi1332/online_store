<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Проверяем, что запрос содержит ID отзыва и что пользователь является администратором
    if (isset($data['id']) && is_numeric($data['id']) && $_SESSION['role'] === 'admin') {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$data['id']]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Отзыв не найден"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Ошибка удаления"]);
    }
}
?>
