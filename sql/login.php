<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}

$login_error = "";
$register_success = "";
$register_error = "";
$form_mode = "login";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "login") {
    $username = $_POST['login_username'];
    $password = $_POST['login_password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username=? AND is_banned=0");
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            header("Location: ../Home.html");
            exit;
        } else {
            $login_error = "Invalid password!";
        }
    } else {
        $login_error = "User not found or banned!";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "register") {
    $form_mode = "register";
    $username = trim($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $confirm = $_POST['reg_confirm'];

    if ($password !== $confirm) {
        $register_error = "Passwords do not match!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $register_error = "Username already taken!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $insert->bind_param("ss", $username, $hashed);
            if ($insert->execute()) {
                $register_success = "Registered successfully! Please log in.";
                $form_mode = "login";
            } else {
                $register_error = "Registration failed.";
            }
            $insert->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AniWeeb</title>
    <link rel="stylesheet" href="login.css">
        <link rel="icon" type="image/x-icon" href="../icons/favicon.ico">

        <!-- ========================================================================= -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- ========================================================================= -->

</head>
<body>
<div class="container">
    <a href="../index.html"><h1>AniWeeb</h1></a>




    <form id="loginForm" method="post" style="display: <?= $form_mode === "login" ? "block" : "none" ?>;">
        <input type="hidden" name="action" value="login">
        <input type="text" name="login_username" placeholder="Username" required>
        <input type="password" name="login_password" placeholder="Password" required>
        <input type="submit" value="Login">
    </form>
    <?php if (!empty($login_error)) echo "<div id='loginError' style='color:red;'>$login_error</div>"; ?>

    <form id="registerForm" method="post" style="display: <?= $form_mode === "register" ? "block" : "none" ?>;">
        <input type="hidden" name="action" value="register">
        <input type="text" name="reg_username" placeholder="New username" required>
        <input type="password" name="reg_password" placeholder="New password" required>
        <input type="password" name="reg_confirm" placeholder="Confirm password" required>
        <input type="submit" value="Register">
    </form>
    <?php 
        if (!empty($register_error)) echo "<div id='registerError' style='color:red;'>$register_error</div>";
        if (!empty($register_success)) echo "<div id='registerSuccess' style='color:green;'>$register_success</div>";
    ?>

<input type="checkbox" id="toggleForm" <?= $form_mode === "register" ? "checked" : "" ?> hidden>

<label class="toggle-label" for="toggleForm" id="showRegister">Register?</label>
<label class="toggle-label" for="toggleForm" id="showLogin">Login?</label>

</div>

<script>
    const toggle = document.getElementById("toggleForm");
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const loginError = document.getElementById("loginError");
    const registerError = document.getElementById("registerError");
    const registerSuccess = document.getElementById("registerSuccess");

    toggle.addEventListener("change", function () {
        if (this.checked) {
            loginForm.style.display = "none";
            registerForm.style.display = "block";
            if (loginError) loginError.style.display = "none";
        } else {
            loginForm.style.display = "block";
            registerForm.style.display = "none";
            if (registerError) registerError.style.display = "none";
            if (registerSuccess) registerSuccess.style.display = "none";
        }
    });
</script>
</body>
</html>
<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}

$login_error = "";
$register_success = "";
$register_error = "";
$form_mode = "login";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "login") {
    $username = $_POST['login_username'];
    $password = $_POST['login_password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username=? AND is_banned=0");
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            header("Location: ../Home.html");
            exit;
        } else {
            $login_error = "Invalid password!";
        }
    } else {
        $login_error = "User not found or banned!";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "register") {
    $form_mode = "register";
    $username = trim($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $confirm = $_POST['reg_confirm'];

    if ($password !== $confirm) {
        $register_error = "Passwords do not match!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $register_error = "Username already taken!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $insert->bind_param("ss", $username, $hashed);
            if ($insert->execute()) {
                $register_success = "Registered successfully! Please log in.";
                $form_mode = "login";
            } else {
                $register_error = "Registration failed.";
            }
            $insert->close();
        }
        $stmt->close();
    }
}
?>