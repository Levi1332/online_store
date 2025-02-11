<?php
session_start();
require_once 'config.php';

$response = array('status' => 'error', 'message' => 'Что-то пошло не так.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверяем, что все данные переданы
    if (isset($_POST['rating'], $_POST['comment'], $_POST['product_id']) && is_numeric($_POST['rating']) && !empty($_POST['comment'])) {
        
        $rating = (int) $_POST['rating'];
        $comment = htmlspecialchars(trim($_POST['comment']));
        $product_id = (int) $_POST['product_id'];
        $user_id = $_SESSION['user_id'] ?? null;

        if ($user_id) {
            // Проверка, что рейтинг в пределах допустимого диапазона
            if ($rating < 1 || $rating > 5) {
                $response = array('status' => 'error', 'message' => 'Рейтинг должен быть от 1 до 5.');
            } else {
                // Вставляем отзыв в базу данных
                $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment, review_date) 
                                       VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$product_id, $user_id, $rating, $comment]);

                // Ответ успешного добавления отзыва
                $response = array('status' => 'success', 'message' => 'Отзыв успешно добавлен!');
            }
        } else {
            $response = array('status' => 'error', 'message' => 'Пожалуйста, войдите в систему.');
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Некорректные данные.');
    }
}

// Отправка ответа в формате JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
