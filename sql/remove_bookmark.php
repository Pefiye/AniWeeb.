<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'], $_POST['bookmark_id'])) {
    header("Location: login.php");
    exit;
}

$bookmark_id = (int)$_POST['bookmark_id'];
$user_id = $_SESSION['user_id'];

$del = $conn->prepare("DELETE FROM bookmarks WHERE id=? AND user_id=?");
$del->bind_param("ii", $bookmark_id, $user_id);
$del->execute();
$del->close();

header("Location: profile.php");
exit;
