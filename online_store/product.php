<?php
session_start();
require_once 'config.php';

$cart_count = 0;
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ошибка: Некорректный ID товара.");
}

$product_id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Ошибка: Товар не найден.");
}

$stmtImages = $pdo->prepare("SELECT image FROM product_images WHERE product_id = ?");
$stmtImages->execute([$product_id]);
$images = $stmtImages->fetchAll(PDO::FETCH_ASSOC);

$stmtReviews = $pdo->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE product_id = ? 
    ORDER BY review_date DESC
");
$stmtReviews->execute([$product_id]);
$reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);

$item = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmtCart = $pdo->prepare("
        SELECT id AS cart_id, quantity 
        FROM cart 
        WHERE user_id = ? AND product_id = ?
    ");
    $stmtCart->execute([$user_id, $product_id]);
    $item = $stmtCart->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> — Детали товара</title>
    <style>
        /* Стили для основного изображения товара */
        .main-image {
            width: 100%; /* Заполняет родительский контейнер */
            max-width: 600px; /* Максимальная ширина */
            height: auto;
            display: block;
            margin-bottom: 20px;
        }

        /* Стили для галереи дополнительных изображений */
        .gallery {
            display: flex;
            gap: 10px; /* Отступы между изображениями */
            margin-top: 10px;
            flex-wrap: wrap; /* Чтобы изображения переходили на следующую строку при необходимости */
            justify-content: center; /* Центрирование картинок */
        }

        .gallery img {
            width: 80px; /* Ограничиваем ширину миниатюр */
            height: 80px; /* Ограничиваем высоту миниатюр */
            object-fit: cover; /* Обрезка изображений, чтобы они сохраняли пропорции */
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        /* Эффект увеличения миниатюр при наведении */
        .gallery img:hover {
            transform: scale(1.1); /* Увеличение изображения при наведении */
            border-color: #333; /* Меняем цвет рамки */
        }

        /* Для контейнера с отзывами и описанием */
        .description {
            margin-top: 20px;
        }

        .price {
            font-size: 1.2em;
            color: #ff5722; /* Цвет для цены */
        }

        /* Стили для кнопок */
        .buttons {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .order-button, .add-to-cart-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4285f4;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
            width: 200px;
            text-align: center;
        }

        .order-button:hover, .add-to-cart-button:hover {
            background-color: #357ae8;
        }

        /* Размещение кнопок справа от изображения */
        .product-detail {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .product-detail .image-container {
            flex: 1;
        }

        .product-detail .buttons {
            flex: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
			
		
        }
		
		.review-form {
    margin-top: 30px;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.review-form h3 {
    margin-bottom: 20px;
    font-size: 1.5em;
    color: #333;
}

.review-form label {
    font-size: 1.1em;
    color: #555;
    margin-bottom: 5px;
    display: block;
}

.review-form select, .review-form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1em;
}

.review-form textarea {
    resize: vertical;
}

.review-form input[type="submit"] {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1.1em;
}

.review-form input[type="submit"]:hover {
    background-color: #45a049;
}

    </style>
    <script>
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
        }

        function handleOrder(productId) {
            fetch("check_cart.php", {
                method: "POST",
                body: JSON.stringify({ product_id: productId }),
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    window.location.href = "order_form.php?cart_id=" + data.cart_id + "&product_id=" + productId;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error("Ошибка:", error));
        }

        function addToCart(productId) {
            fetch("add_to_cart.php", {
                method: "POST",
                body: JSON.stringify({ product_id: productId }),
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert("Товар добавлен в корзину!");
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error("Ошибка:", error));
        }
    </script>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <div class="product-detail">
        <div class="image-container">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <img id="mainImage" class="main-image" src="<?= htmlspecialchars($product['image'] ?? 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>">

            <?php if (!empty($images)): ?>
                <div class="gallery">
                    <?php foreach ($images as $img): ?>
                        <img src="<?= htmlspecialchars($img['image']) ?>" alt="Доп. изображение" onclick="changeMainImage('<?= htmlspecialchars($img['image']) ?>')">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="buttons">

            <button class="order-button" onclick="handleOrder(<?= $product_id ?>)">Оформить заказ</button>
        </div>
    </div>

    <div class="description">
        <h3>Описание</h3>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <p class="price">Цена: <?= number_format($product['price'], 2, '.', ' ') ?> руб.</p>
    </div>

    <div class="reviews">
        <h3>Отзывы</h3>
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <strong><?= htmlspecialchars($review['username']) ?></strong>
                    <span class="rating"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></span>
                    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    <small><?= htmlspecialchars($review['review_date']) ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Отзывов пока нет.</p>
        <?php endif; ?>
    </div>
</div>

<div class="review-form">
    <h3>Оставьте отзыв о товаре</h3>
    <form action="submit_review.php" method="POST">
        <label for="rating">Рейтинг:</label>
        <select name="rating" id="rating" required>
            <option value="1">1 ★</option>
            <option value="2">2 ★★</option>
            <option value="3">3 ★★★</option>
            <option value="4">4 ★★★★</option>
            <option value="5">5 ★★★★★</option>
        </select>

        <label for="comment">Комментарий:</label>
        <textarea name="comment" id="comment" rows="5" required></textarea>

        <input type="hidden" name="product_id" value="<?= $product_id ?>">
        <input type="submit" value="Отправить отзыв">
    </form>
</div>

<script>
    // Функция для обработки успешного добавления отзыва
    document.querySelector('form').addEventListener('submit', function (e) {
        e.preventDefault(); // Останавливаем стандартную отправку формы

        var formData = new FormData(this); // Собираем данные формы

        fetch('submit_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message); // Уведомление об успешном добавлении отзыва
                document.querySelector('form').reset(); // Очищаем форму
            } else {
                alert(data.message); // Уведомление об ошибке
            }
        })
        .catch(error => console.error('Ошибка:', error));
    });
</script>


<footer>
    <div class="container">
        <p>&copy; <?= date("Y") ?> Интернет-магазин. Все права защищены.</p>
    </div>
</footer>
</body>
</html>
