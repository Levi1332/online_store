<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Доступ запрещен.");
}

// Удаление товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['delete_product'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
}

// Получаем список товаров
$products = $pdo->query("SELECT id, name FROM products")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="admin.css">
    <script>
        function deleteReview(reviewId) {
            fetch("delete_review.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ id: reviewId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert("Отзыв удален");
                    document.getElementById("review-" + reviewId).remove(); // Удаляем отзыв из DOM
                } else {
                    alert("Ошибка удаления отзыва");
                }
            })
            .catch(error => {
                console.error("Ошибка:", error);
                alert("Ошибка при удалении отзыва");
            });
        }

        function loadReviews(productId, button) {
            let reviewContainer = document.getElementById('reviews-' + productId);

            if (reviewContainer.style.display === 'none' || reviewContainer.innerHTML === '') {
                fetch("get_reviews.php?product_id=" + productId)
                    .then(response => response.text())
                    .then(data => {
                        reviewContainer.innerHTML = data;
                        reviewContainer.style.display = 'block';
                        button.textContent = 'Скрыть отзывы';
                    });
            } else {
                reviewContainer.style.display = 'none';
                button.textContent = 'Просмотреть отзывы';
            }
        }
    </script>
</head>
<body>
   <?php include 'header.php'; ?>

    <h1>Админ-панель</h1>
    <h2>Список товаров</h2>
    <table border="1">
        <tr>
            <th>Название</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($products as $product): ?>
            <tr>
                <td>
                    <a href="product.php?id=<?= $product['id'] ?>" target="_blank">
                        <?= htmlspecialchars($product['name']) ?>
                    </a>
                </td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_product" value="<?= $product['id'] ?>">
                        <button type="submit">Удалить товар</button>
                    </form>
                    <button onclick="loadReviews(<?= $product['id'] ?>, this)">Просмотреть отзывы</button>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div id="reviews-<?= $product['id'] ?>" style="display: none;"></div>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
