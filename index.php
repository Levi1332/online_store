<?php
session_start();
require_once 'config.php';

$cart_count = 0;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetchColumn() ?: 0;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Интернет-магазин</title>
    <link rel="stylesheet" href="style.css?v=1.0">

    <style>
        /* Общий стиль страницы */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        /* Контейнер для содержимого */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Стили для формы поиска */
        form input[type="text"], form select {
            padding: 12px;
            font-size: 1em;
            border-radius: 5px;
            border: 1px solid #ddd;
            width: 70%;
            margin-right: 10px;
            transition: border-color 0.3s ease;
        }

        form input[type="text"]:focus, form select:focus {
            border-color: #4285f4;
            outline: none;
        }

        /* Кнопка поиска */
        form button {
            padding: 12px 25px;
            font-size: 1em;
            color: white;
            background-color: #4285f4;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        form button:hover {
            background-color: #34a853;
            transform: scale(1.1);
        }

        form button:active {
            background-color: #fbbc05;
            transform: scale(1.05);
        }

        /* Стили для вывода товаров */
        .products {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .product-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 300px;
            margin: 15px;
            padding: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: scale(1.05);
        }

        .product-card img {
            width: 100%;
            height: auto;
            border-bottom: 1px solid #ddd;
            margin-bottom: 15px;
            transition: transform 0.3s ease-in-out;
        }

        .product-card:hover img {
            transform: scale(1.1);
        }

        .product-card h3, 
        .product-card p {
            margin: 0 0 10px;
        }

        .price {
            font-size: 1.2em;
            font-weight: bold;
            color: #4285f4;
        }

        .product-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        /* Стили для футера */
        footer {
            background-color: #ddd;
            text-align: center;
            padding: 10px 0;
            margin-top: 20px;
        }

        /* Уведомления */
        .cart-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #34a853;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out, fadeOut 0.5s ease-in-out 1.5s forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
	
    <div class="container">
        <h2>Наши товары</h2>

        <form method="GET" action="">
            <input type="text" name="search" placeholder="Поиск товара..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit">Поиск</button>
        </form>

        <form method="GET" action="">
            <select name="category" onchange="this.form.submit()">
                <option value="">Выберите категорию</option>
                <?php
                $stmt = $pdo->query("SELECT * FROM categories");
                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value=\"" . $category['id'] . "\" " . (isset($_GET['category']) && $_GET['category'] == $category['id'] ? "selected" : "") . ">" . htmlspecialchars($category['name']) . "</option>";
                }
                ?>
            </select>
        </form>

        <div class="products">
            <?php
            try {
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                $categoryId = isset($_GET['category']) ? $_GET['category'] : '';

                $sql = "SELECT p.*, c.name AS category_name FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE 1";

                if ($search) {
                    $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
                }
                if ($categoryId) {
                    $sql .= " AND p.category_id = :category_id";
                }

                $stmt = $pdo->prepare($sql);
                if ($search) {
                    $stmt->bindValue(':search', '%' . $search . '%');
                }
                if ($categoryId) {
                    $stmt->bindValue(':category_id', $categoryId);
                }

                $stmt->execute();

                while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= htmlspecialchars($product['id']) ?>" class="product-link">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <img src="placeholder.jpg" alt="Изображение отсутствует">
                            <?php endif; ?>
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                            <p class="price"><?= number_format($product['price'], 2, '.', ' ') ?> руб.</p>
                            <?php if (!empty($product['category_name'])): ?>
                                <p>Категория: <?= htmlspecialchars($product['category_name']) ?></p>
                            <?php endif; ?>
                        </a>
                        <button onclick="addToCart(<?= $product['id'] ?>)">Добавить в корзину</button>
                    </div>
                    <?php
                }
            } catch (PDOException $e) {
                echo "<p>Ошибка: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?= date("Y") ?> Интернет-магазин. Все права защищены.</p>
        </div>
    </footer>

    <script>
        function addToCart(productId) {
            let formData = new FormData();
            formData.append("product_id", productId);

            fetch("add_to_cart.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    showNotification(data.message);
                    updateCartCount(data.cartCount);
                } else {
                    alert("Ошибка: " + data.message);
                }
            })
            .catch(error => console.error("Ошибка:", error));
        }

        function showNotification(message) {
            let notification = document.createElement("div");
            notification.className = "cart-notification";
            notification.innerText = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 2000);
        }

        function updateCartCount(count) {
            document.getElementById("cart-count").innerText = count;
        }
    </script>
</body>
</html>
