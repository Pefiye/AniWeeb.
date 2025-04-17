<?php
session_start();
require 'db.php';

if (!$conn) {
    die("Connection to DB failed.");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, password, role FROM users WHERE id=? AND is_banned=0");
if (!$stmt) {
    die("User SELECT prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($current_username, $hashed_password, $role);
$fetch_success = $stmt->fetch();
$stmt->close();

if (!$fetch_success) {
    die("User not found or is banned.");
}

$_SESSION['role'] = $role;

$bookmarkStmt = $conn->prepare("SELECT id, anime_id, anime_title, anime_cover, bookmarked_at, finished FROM bookmarks WHERE user_id=?");
if (!$bookmarkStmt) {
    die("Bookmark SELECT prepare failed: " . $conn->error);
}
$bookmarkStmt->bind_param("i", $user_id);
$bookmarkStmt->execute();
$bookmarkResult = $bookmarkStmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>AniWeeb | Your Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="profile.css">
            <link rel="icon" type="image/x-icon" href="../icons/favicon.ico">

        <!-- ========================================================================= -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- ========================================================================= -->

</head>
<body>

<div class="navbar">
  <a href="../Home.html"><input type="submit" value="Home"></a>
    <form action="logout.php" method="post">
        <input type="submit" value="Logout">
    </form>
</div>

<div class="container">
    <div class="left-panel">
        <h2>Welcome, <?= htmlspecialchars($current_username) ?></h2>
        <h3>Your Bookmarked Anime</h3>
        <div class="bookmarked-list">
            <?php if ($bookmarkResult && $bookmarkResult->num_rows > 0): ?>
                <?php while ($row = $bookmarkResult->fetch_assoc()): ?>
                    <div class="bookmark-card">
                        <img src="<?= htmlspecialchars($row['anime_cover']) ?>" alt="Poster">
                        <div>
                            <p><strong>Title:</strong> <?= htmlspecialchars($row['anime_title']) ?></p>
                            <p><strong>Bookmarked At:</strong> <?= htmlspecialchars($row['bookmarked_at']) ?></p>
                            <form method="post" action="remove_bookmark.php">
                                <input type="hidden" name="bookmark_id" value="<?= $row['id'] ?>">
                                <button type="submit">❌ Remove</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No bookmarks yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="right-panel">
        <h3>Update Profile</h3>
        <?php if (isset($_GET['error'])): ?>
            <p style="color:red;"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <p style="color:green;"><?= htmlspecialchars($_GET['success']) ?></p>
        <?php endif; ?>

        <form id="profileForm" method="post" action="update_profile.php">
            <label>Change Username:</label>
            <input type="text" name="new_username" placeholder="New username">

            <label>Change Password:</label>
            <input type="password" name="new_password" id="new_password" placeholder="New password">

            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password">

            <input type="submit" value="Update">
        </form>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <form action="user-list.php" method="get">
                <button type="submit">Go to User List</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('profileForm').onsubmit = function(e) {
    const username = document.querySelector('[name="new_username"]').value.trim();
    const password = document.getElementById('new_password').value;
    const confirm = document.getElementById('confirm_password').value;

    if (username === "" && password === "") {
        alert("Please enter a new username or new password.");
        e.preventDefault();
        return;
    }

    if (password !== "" && password !== confirm) {
        alert("Passwords do not match.");
        e.preventDefault();
    }
};
</script>

</body>
</html>
