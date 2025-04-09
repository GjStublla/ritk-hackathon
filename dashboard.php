<?php
session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] != 1) {
    header("Location: login.php");
    exit;
}
?>
<h1>Welcome Admin</h1>
<a href="logout.php">Logout</a>
