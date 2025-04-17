<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$new_username = trim($_POST['new_username']);
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

if ($new_username === "" && $new_password === "") {
    $error = "You must update at least username or password.";
    header("Location: profile.php?error=" . urlencode($error));
    exit;
}

if (!empty($new_password) && $new_password !== $confirm_password) {
    $error = "Passwords do not match.";
    header("Location: profile.php?error=" . urlencode($error));
    exit;
}

if (!empty($new_username)) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=? AND id != ?");
    $stmt->bind_param("si", $new_username, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $error = "Username already taken!";
        header("Location: profile.php?error=" . urlencode($error));
        exit;
    }
    $stmt->close();
}

$fields = [];
$params = [];
$types = "";

if (!empty($new_username)) {
    $fields[] = "username=?";
    $params[] = &$new_username;
    $types .= "s";
    $_SESSION['username'] = $new_username;
}

if (!empty($new_password)) {
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $fields[] = "password=?";
    $params[] = &$hashed;
    $types .= "s";
}

if (!empty($fields)) {
    $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id=?";
    $params[] = &$user_id;
    $types .= "i";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();
}

header("Location: profile.php?success=" . urlencode("Profile updated successfully!"));
exit;
?>
