<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = mysqli_connect("localhost", "root", "", "travels_review");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

// Get review id from URL
$review_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($review_id > 0) {
    // Delete review only if belongs to this user
    $sql = "DELETE FROM reviews WHERE id = $review_id AND user_id = $user_id";
    mysqli_query($conn, $sql);
}

mysqli_close($conn);
header("Location: review_history.php");
exit;
