<?php
// config.php
$host     = 'localhost';
$dbname   = 'online_store';     
$username = 'root';         
$password = '';                

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password);
    // Включаем вывод ошибок в виде исключений:
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Если подключение не удалось, выводим сообщение об ошибке:
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>

