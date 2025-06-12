<?php
// db.php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "travels_review";

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
