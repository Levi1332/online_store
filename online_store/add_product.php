<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['seller', 'admin'])) {
    die("Доступ запрещен!");
}


$success_message = "";
$error_message = "";

// Получаем категории из базы
$stmt = $pdo->query("SELECT id, name FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Если форма отправлена
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category']);
    $image_path = "";

    // Проверяем, загружено ли изображение
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "images/";
        $image_path = $target_dir . basename($_FILES["image"]["name"]);

        // Перемещаем загруженный файл
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
            $error_message = "Ошибка загрузки изображения.";
        }
    }

    // Проверка, что все обязательные поля заполнены
    if ($name && $description && $price > 0 && $stock > 0 && $category_id && $image_path) {
        // Записываем данные в базу
        $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$category_id, $name, $description, $price, $stock, $image_path])) {
            $success_message = "Товар успешно добавлен!";
        } else {
            $error_message = "Ошибка добавления товара.";
        }
    } else {
        $error_message = "Заполните все поля!";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление товара</title>
    <link rel="stylesheet" href="add_product.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h2>Добавление товара</h2>

        <!-- Вывод сообщений -->
        <?php if ($success_message): ?>
            <p style="color: green;"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Название товара:</label>
            <input type="text" name="name" required>

            <label>Описание:</label>
            <textarea name="description" required></textarea>

            <label>Цена (₽):</label>
            <input type="number" name="price" step="0.01" required>

            <label>Количество на складе:</label>
            <input type="number" name="stock" required>

            <label>Категория:</label>
            <select name="category" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Изображение товара:</label>
            <input type="file" name="image" accept="image/*" required>

            <button type="submit">Добавить товар</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
