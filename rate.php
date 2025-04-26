<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.html');
    exit;
}

$userId = $_SESSION['user']['id'];
$storeId = $_POST['store_id'];
$rating = $_POST['rating'];

if ($rating < 1 || $rating > 5) {
    echo "Invalid rating.";
    exit;
}

// Check if rating exists
$check = $conn->prepare("SELECT * FROM ratings WHERE user_id = ? AND store_id = ?");
$check->execute([$userId, $storeId]);

if ($check->rowCount() > 0) {
    // Update
    $stmt = $conn->prepare("UPDATE ratings SET rating = ? WHERE user_id = ? AND store_id = ?");
    $stmt->execute([$rating, $userId, $storeId]);
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO ratings (user_id, store_id, rating) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $storeId, $rating]);
}

header('Location: stores.php');
exit;
?>
