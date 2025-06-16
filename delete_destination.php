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

// Delete all reviews for this destination
mysqli_query($conn, "DELETE FROM reviews WHERE destination_id = $destination_id");

// Delete all photos for this destination and remove files from disk
$photo_res = mysqli_query($conn, "SELECT photo FROM destination_photos WHERE destination_id = $destination_id");
while ($row = mysqli_fetch_assoc($photo_res)) {
    $photo_path = $row['photo'];
    if (file_exists($photo_path)) {
        @unlink($photo_path);
    }
}
mysqli_query($conn, "DELETE FROM destination_photos WHERE destination_id = $destination_id");

// Only allow deletion if the destination belongs to the logged-in user
$sql = "DELETE FROM destinations WHERE id = $destination_id AND user_id = $user_id";
mysqli_query($conn, $sql);

header("Location: dashboard.php");
exit;
?>