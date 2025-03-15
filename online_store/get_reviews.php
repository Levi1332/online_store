<?php
require_once 'config.php';

if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    die("Ошибка: Некорректный ID товара.");
}

$product_id = (int) $_GET['product_id'];

$stmt = $pdo->prepare("
    SELECT r.id, r.comment, r.rating, r.review_date, u.username 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.review_date DESC
");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$reviews) {
    echo "<p>Отзывов пока нет.</p>";
    exit;
}

echo "<ul>";
foreach ($reviews as $review) {
    echo "<li id='review-" . $review['id'] . "'>";
    echo "<strong>" . htmlspecialchars($review['username']) . ":</strong> ";
    echo str_repeat("★", $review['rating']) . str_repeat("☆", 5 - $review['rating']);
    echo "<p>" . nl2br(htmlspecialchars($review['comment'])) . "</p>";
    echo "<small>" . htmlspecialchars($review['review_date']) . "</small>";
    echo " <button onclick='deleteReview(" . $review['id'] . ")'>Удалить</button>";
    echo "</li>";
}
echo "</ul>";
?>
