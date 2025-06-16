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
  <style>
    body {
      background-color: #f8f9fa;
    }
    .edit-destination-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.08);
      padding: 32px 28px 24px 28px;
      margin: 40px auto;
      max-width: 600px;
    }
    .edit-destination-card h2 {
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: #222;
    }
    .form-label {
      font-weight: 500;
    }
    .btn-warning {
      min-width: 140px;
    }
    .current-thumb {
      border: 1px solid #eee;
      border-radius: 6px;
      margin-bottom: 8px;
      background: #fafafa;
      padding: 8px;
      display: inline-block;
    }
    .thumb-preview {
      display: block;
      margin: 0 auto 1rem auto;
      border-radius: 8px;
      border: 1px solid #eee;
      height: 120px;
      object-fit: cover;
      background: #fafafa;
    }
  </style>
  <script>
    function previewThumb(input) {
      const preview = document.getElementById('thumbPreview');
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
      } else {
        preview.src = '<?= !empty($destination['image']) ? htmlspecialchars($destination['image']) : 'uploads/default.png' ?>';
      }
    }
  </script>
</head>
<body>
  <div class="edit-destination-card">
    <h2 class="mb-4">Edit Destination</h2>
    <?php if ($message): ?>
      <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
      <div class="mb-3 text-center">
        <img
          src="<?= !empty($destination['image']) && file_exists($destination['image']) ? htmlspecialchars($destination['image']) : 'uploads/default.png' ?>"
          id="thumbPreview"
          class="thumb-preview"
          alt="Thumbnail Preview"
        >
      </div>
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
        <label for="image" class="form-label">Change Thumbnail (optional)</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewThumb(this)" />
        <small class="text-muted">If not uploaded, the current or default image will be used.</small>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-warning">Update Destination</button>
        <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>