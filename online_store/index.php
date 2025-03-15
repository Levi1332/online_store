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
    <link rel="stylesheet" href="index.css?v=1.0">

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

	<?php include 'footer.php'; ?>

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
