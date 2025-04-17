<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: profile.php");
    exit;
}

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

$conditions = "WHERE username LIKE ?";
$params = ["%$search%"];
$types = "s";

if ($filter === 'banned') {
    $conditions .= " AND is_banned = 1";
} elseif ($filter === 'not_banned') {
    $conditions .= " AND is_banned = 0";
}

$query = "SELECT id, username, role, is_banned FROM users $conditions ORDER BY username";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>User List</h2>
<link rel="stylesheet" href="user-list.css">
        <link rel="icon" type="image/x-icon" href="../icons/favicon.ico">

    <!-- ========================================================================= -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- ========================================================================= -->


<form method="get">
    <input type="text" name="search" placeholder="Search username" value="<?= htmlspecialchars($search) ?>">
    <select name="filter">
        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
        <option value="banned" <?= $filter === 'banned' ? 'selected' : '' ?>>Banned</option>
        <option value="not_banned" <?= $filter === 'not_banned' ? 'selected' : '' ?>>Not Banned</option>
    </select>
    <button type="submit">Search</button>
</form>
<a href="profile.php"
style="text-decoration: none; color: #ffffff;">Back</a>

<table border="1" cellpadding="5" style="margin-top: 1em;">
    <tr>
        <th>Username</th>
        <th>Role</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['role']) ?></td>
        <td><?= $row['is_banned'] ? 'Banned' : 'Active' ?></td>
        <td>
            <form method="post" action="toggle_ban.php" style="display:inline;">
                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                <button type="submit" name="action" value="<?= $row['is_banned'] ? 'unban' : 'ban' ?>">
                    <?= $row['is_banned'] ? 'Unban' : 'Ban' ?>
                </button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
