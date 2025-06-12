<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

$message = "";
$destination = null;

// Get destination ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$destination_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the destination and check ownership
$sql = "SELECT * FROM destinations WHERE id = $destination_id AND user_id = $user_id";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) === 1) {
    $destination = mysqli_fetch_assoc($result);
} else {
    // Not found or not owned by user
    header("Location: dashboard.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $image_path = $destination['image'];

    // Handle new image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid('thumb_', true) . '.' . $ext;
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $target = $upload_dir . $new_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = $target;
        } else {
            $message = "Image upload failed.";
        }
    }

    if (empty($name) || empty($location) || empty($description)) {
        $message = "Please fill in all fields.";
    } else {
        $update_sql = "UPDATE destinations SET name='$name', location='$location', description='$description', image='$image_path' WHERE id=$destination_id AND user_id=$user_id";
        if (mysqli_query($conn, $update_sql)) {
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "Failed to update destination. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Destination - Travels Review</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
  <div class="container mt-5" style="max-width: 600px;">
    <h2 class="mb-4">Edit Destination</h2>
    <?php if ($message): ?>
      <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="name" class="form-label">Destination Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($destination['name']) ?>" required />
      </div>
      <div class="mb-3">
        <label for="location" class="form-label">Location</label>
        <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($destination['location']) ?>" required />
      </div>
      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($destination['description']) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Current Thumbnail</label><br>
        <?php if (!empty($destination['image'])): ?>
          <img src="<?= htmlspecialchars($destination['image']) ?>" alt="Current Thumbnail" style="height:120px;object-fit:cover;">
        <?php else: ?>
          <span class="text-muted">No image uploaded.</span>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label for="image" class="form-label">Change Thumbnail (optional)</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/*" />
      </div>
      <button type="submit" class="btn btn-warning">Update Destination</button>
      <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
  </div>
</body>
</html>