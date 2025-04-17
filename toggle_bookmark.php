<?php
session_start();
require 'sql/db.php';

if (!isset($_SESSION['user_id'], $_POST['anime_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$anime_id = (int)$_POST['anime_id'];
$anime_title = $_POST['anime_title'] ?? '';
$anime_cover = $_POST['anime_cover'] ?? '';

$check = $conn->prepare("SELECT id FROM bookmarks WHERE user_id=? AND anime_id=?");
$check->bind_param("ii", $user_id, $anime_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $delete = $conn->prepare("DELETE FROM bookmarks WHERE user_id=? AND anime_id=?");
    $delete->bind_param("ii", $user_id, $anime_id);
    $delete->execute();
    $delete->close();
} else {
    $insert = $conn->prepare("INSERT INTO bookmarks (user_id, anime_id, anime_title, anime_cover) VALUES (?, ?, ?, ?)");
    $insert->bind_param("iiss", $user_id, $anime_id, $anime_title, $anime_cover);
    $insert->execute();
    $insert->close();
}

$check->close();
header("Location: anime.php?id=$anime_id");
exit;
