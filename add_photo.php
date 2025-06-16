<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['destination_id'])) {
    header("Location: dashboard.php");
    exit;
}

$destination_id = (int)$_GET['destination_id'];
$message = "";

// Optional: Check if the logged-in user owns the destination
$check = mysqli_query($conn, "SELECT * FROM destinations WHERE id=$destination_id AND user_id=" . $_SESSION['user_id']);
if (!$check || mysqli_num_rows($check) == 0) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_FILES['gallery']['name'][0])) {
    $upload_dir = 'uploads/gallery/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    foreach ($_FILES['gallery']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['gallery']['error'][$key] == UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['gallery']['name'][$key], PATHINFO_EXTENSION);
            $new_name = uniqid('gallery_', true) . '.' . $ext;
            $target = $upload_dir . $new_name;
            if (move_uploaded_file($tmp_name, $target)) {
                $gallery_sql = "INSERT INTO destination_photos (destination_id, photo) VALUES ($destination_id, '$target')";
                mysqli_query($conn, $gallery_sql);
            }
        }
    }
    header("Location: destination.php?id=$destination_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Gallery Photos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5" style="max-width:400px;">
  <h3>Add Photos to Gallery</h3>
  <?php if ($message): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="gallery" class="form-label">Select Photos</label>
      <input type="file" name="gallery[]" id="gallery" class="form-control" accept="image/*" multiple required>
      <small class="text-muted">You can select multiple images.</small>
    </div>
    <button type="submit" class="btn btn-success">Upload</button>
    <a href="destination.php?id=<?= $destination_id ?>" class="btn btn-secondary ms-2">Back</a>
  </form>
</div>
</body>
</html>