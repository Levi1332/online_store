<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login    = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($login) && !empty($password)) {
        // Получаем данные пользователя по логину (или email, если нужно)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Успешная авторизация
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Неверный логин или пароль";
        }
    } else {
        $error = "Заполните все поля!";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-container">
    <h2>Вход в систему</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="login.php" method="post">
        <label for="username">Логин:</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Пароль:</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Войти</button>
    </form>
    <p>Еще не зарегистрированы? <a href="register.php">Регистрация</a></p>
</div>
</body>
</html>
