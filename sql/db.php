<?php
$host = "sql204.infinityfree.com";
$user = "if0_38768092";
$pass = "q2TUArmG4yHx";
$dbname = "if0_38768092_aniweeb";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
