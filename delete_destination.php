<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$destination_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Only allow deletion if the destination belongs to the logged-in user
$sql = "DELETE FROM destinations WHERE id = $destination_id AND user_id = $user_id";
mysqli_query($conn, $sql);

header("Location: dashboard.php");
exit;
?>