<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user_id = $_POST['user_id'];
$action = $_POST['action'];

if ($action === 'ban') {
    $stmt = $conn->prepare("UPDATE users SET is_banned = 1 WHERE id = ?");
} elseif ($action === 'unban') {
    $stmt = $conn->prepare("UPDATE users SET is_banned = 0 WHERE id = ?");
} else {
    header("Location: user-list.php");
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

header("Location: user-list.php");
exit;
