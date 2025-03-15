<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username         = trim($_POST['username']);
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Все поля обязательны для заполнения!";
    } elseif ($password !== $confirm_password) {
        $error = "Пароли не совпадают!";
    } else {
        // Проверяем, существует ли уже пользователь с таким логином или email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error = "Пользователь с таким логином или email уже существует";
        } else {
            // Хэшируем пароль
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $password_hash])) {
                // Автоматическая авторизация после регистрации
                $_SESSION['user_id']   = $pdo->lastInsertId();
                $_SESSION['username']  = $username;
                header("Location: index.php");
                exit();
            } else {
                $error = "Ошибка регистрации, попробуйте позже.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-container">
    <h2>Регистрация</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="register.php" method="post">
        <label for="username">Логин:</label>
        <input type="text" name="username" id="username" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Пароль:</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm_password">Подтвердите пароль:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже зарегистрированы? <a href="login.php">Войти</a></p>
</div>
</body>
</html>
