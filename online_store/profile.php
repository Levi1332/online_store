<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['save_profile'])) { // Обработка данных профиля
        $full_name = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $birth_date = $_POST['birth_date'] ?? '';
        $card_number = $_POST['card_number'] ?? '';
        $avatar = $user['avatar'];

        // Обновление аватара
        if (!empty($_FILES['avatar']['name'])) {
            $avatar_path = "user_avatar/" . basename($_FILES["avatar"]["name"]);
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $avatar_path)) {
                $avatar = $avatar_path;
            }
        }

        // Обновление остальных данных
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, birth_date = ?, card_number = ?, avatar = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$full_name, $phone, $address, $birth_date, $card_number, $avatar, $user_id]);

        $success_message = "Данные успешно обновлены!";
    }

    if (isset($_POST['update_account'])) { // Обновление логина и пароля
        if (!empty($_POST['username'])) {
            $username = $_POST['username'];

            // Проверяем, существует ли такой логин у другого пользователя
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $user_id]);
            $existingUsernameCount = $stmt->fetchColumn();

            if ($existingUsernameCount > 0) {
                $error_message = "Это имя пользователя уже занято.";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                $stmt->execute([$username, $user_id]);
                $_SESSION['username'] = $username;
                $success_message = "Логин успешно обновлен!";
            }
        }

        // Обновление пароля
        if (!empty($_POST['password']) && !empty($_POST['confirm_password'])) {
            if ($_POST['password'] === $_POST['confirm_password']) {
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                $success_message = "Пароль успешно изменен!";
            } else {
                $error_message = "Пароли не совпадают!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="profile.css">
    <script>
        function toggleAccountFields() {
            var fields = document.getElementById("accountFields");
            fields.style.display = fields.style.display === "none" ? "block" : "none";
        }
    </script>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="profile-container">
        <h2>Личный кабинет</h2>

        <!-- Вывод сообщений -->
        <?php if (!empty($success_message)): ?>
            <p style="color: green;"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Полное имя:</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>">

            <label>Телефон:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">

            <label>Адрес:</label>
            <textarea name="address"><?= htmlspecialchars($user['address']) ?></textarea>

            <label>Дата рождения:</label>
            <input type="date" name="birth_date" value="<?= htmlspecialchars($user['birth_date']) ?>">

            <label>Привязанная карта:</label>
            <input type="text" name="card_number" value="<?= htmlspecialchars($user['card_number']) ?>">

            <label>Аватар:</label>
            <input type="file" name="avatar">
            <?php if ($user['avatar']): ?>
                <img src="<?= $user['avatar'] ?>" alt="Аватар" width="100">
            <?php endif; ?>

            <button type="submit" name="save_profile">Сохранить изменения</button>
        </form>

        <hr>

        <form method="POST">
            <button type="button" onclick="toggleAccountFields()">Изменить данные об аккаунте</button>

            <div id="accountFields" style="display: none;">
                <label>Логин:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

                <label>Новый пароль:</label>
                <input type="password" name="password">

                <label>Подтвердите пароль:</label>
                <input type="password" name="confirm_password">

                <button type="submit" name="update_account">Сохранить логин и пароль</button>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
