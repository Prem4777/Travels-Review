<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$mysqli = mysqli_connect("localhost", "root", "", "travels_review");
if (!$mysqli) {
    die("Connection failed: " . mysqli_connect_error());
}

$destination_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($destination_id <= 0) {
    die("Invalid destination ID.");
}

// Fetch destination details and owner
$sql = "SELECT name, description, user_id FROM destinations WHERE id = $destination_id";
$result = mysqli_query($mysqli, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    die("Destination not found.");
}
$row = mysqli_fetch_assoc($result);
$name = $row['name'];
$description = $row['description'];
$destination_owner_id = $row['user_id'];

// Handle photo deletion (only by owner)
if (
    isset($_GET['delete_photo']) &&
    $destination_owner_id == $_SESSION['user_id']
) {
    $photo_id = (int)$_GET['delete_photo'];
    // Get photo path
    $photo_res = mysqli_query($mysqli, "SELECT photo FROM destination_photos WHERE id=$photo_id AND destination_id=$destination_id");
    if ($photo_res && mysqli_num_rows($photo_res) === 1) {
        $photo_row = mysqli_fetch_assoc($photo_res);
        $photo_path = $photo_row['photo'];
        // Delete from DB
        mysqli_query($mysqli, "DELETE FROM destination_photos WHERE id=$photo_id AND destination_id=$destination_id");
        // Delete file from disk
        if (file_exists($photo_path)) {
            @unlink($photo_path);
        }
    }
    header("Location: destination.php?id=$destination_id");
    exit;
}

// Calculate average rating for this destination from reviews table
$avg_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE destination_id = $destination_id";
$avg_result = mysqli_query($mysqli, $avg_sql);
$avg_row = mysqli_fetch_assoc($avg_result);
$avg_rating = $avg_row['avg_rating'] ? round($avg_row['avg_rating'], 1) : 0;
$total_reviews = $avg_row['total_reviews'];

// Handle review submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $user_id = $_SESSION['user_id'];

    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5.";
    }
    if (empty($comment)) {
        $errors[] = "Comment cannot be empty.";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO reviews (destination_id, user_id, rating, comment) VALUES ($destination_id, $user_id, $rating, '" . mysqli_real_escape_string($mysqli, $comment) . "')";
        if (mysqli_query($mysqli, $sql)) {
            // After inserting, redirect to refresh and show updated reviews and avg rating
            header("Location: destination.php?id=" . $destination_id);
            exit;
        } else {
            $errors[] = "Failed to submit review.";
        }
    }
}

// Fetch reviews for this destination
$sql = "SELECT r.rating, r.comment, r.created_at, u.username
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.destination_id = $destination_id
        ORDER BY r.created_at DESC";
$result = mysqli_query($mysqli, $sql);
$reviews = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }
}

// Fetch photos for this destination (get id and photo)
$photos = [];
$gallery_sql = "SELECT id, photo FROM destination_photos WHERE destination_id = $destination_id";
$gallery_result = mysqli_query($mysqli, $gallery_sql);
if ($gallery_result) {
    while ($row = mysqli_fetch_assoc($gallery_result)) {
        $photos[] = $row;
    }
}

mysqli_close($mysqli);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($name) ?> - Reviews | Travels Review</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">

  <h1><?= htmlspecialchars($name) ?></h1>
  <p><?= nl2br(htmlspecialchars($description)) ?></p>

  <!-- Display average rating -->
  <h4>Average Rating: 
    <?php
      // Show stars filled for floor of avg_rating, half star if needed
      $fullStars = floor($avg_rating);
      $halfStar = ($avg_rating - $fullStars) >= 0.5 ? true : false;
      $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

      // Show full stars
      for ($i = 0; $i < $fullStars; $i++) {
          echo '<span class="text-warning">&#9733;</span>'; // filled star
      }
      // Show half star if any
      if ($halfStar) {
          echo '<span class="text-warning">&#9733;</span>'; // Just using full star for simplicity, can do half star with icons if you want
      }
      // Show empty stars
      for ($i = 0; $i < $emptyStars; $i++) {
          echo '<span class="text-secondary">&#9734;</span>'; // empty star
      }
      
      // Show numeric average and total reviews
      echo " (" . $avg_rating . " / 5)";
      if ($total_reviews > 0) {
          echo " from $total_reviews review" . ($total_reviews > 1 ? 's' : '');
      } else {
          echo " (No reviews yet)";
      }
    ?>
  </h4>

  <hr>

  <h3>Reviews</h3>
  <?php if (empty($reviews)) : ?>
    <p>No reviews yet. Be the first to review!</p>
  <?php else: ?>
    <?php foreach ($reviews as $review): ?>
      <div class="border rounded p-3 mb-3">
        <strong><?= htmlspecialchars($review['username']) ?></strong>
        <span class="text-warning"><?= str_repeat("⭐", $review['rating']) ?></span>
        <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
        <small class="text-muted">Posted on <?= $review['created_at'] ?></small>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <hr>

  <h3>Add Your Review</h3>

  <?php if (!empty($errors)) : ?>
    <div class="alert alert-danger">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="mb-3">
      <label for="rating" class="form-label">Rating (1 to 5)</label>
      <select name="rating" id="rating" class="form-select" required>
        <option value="">Select rating</option>
        <?php for ($i=1; $i<=5; $i++): ?>
          <option value="<?= $i ?>" <?= (isset($_POST['rating']) && (int)$_POST['rating'] === $i) ? 'selected' : '' ?>><?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="comment" class="form-label">Comment</label>
      <textarea name="comment" id="comment" rows="4" class="form-control" required><?= isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '' ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Submit Review</button>
    <a href="dashboard.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
  </form>

  <!-- Photo Gallery Section -->
  <div class="mb-4">
    <h4>Photo Gallery</h4>
    <?php if ($destination_owner_id == $_SESSION['user_id']): ?>
      <a href="add_photo.php?destination_id=<?= $destination_id ?>" class="btn btn-success btn-sm mb-2">Add Photo</a>
    <?php endif; ?>
    <div class="row g-2">
      <?php foreach ($photos as $photo): ?>
        <div class="col-6 col-md-3 position-relative">
          <img src="<?= htmlspecialchars($photo['photo']) ?>" class="img-fluid rounded" style="height:120px;object-fit:cover;" alt="Gallery Photo">
          <?php if ($destination_owner_id == $_SESSION['user_id']): ?>
            <a href="destination.php?id=<?= $destination_id ?>&delete_photo=<?= $photo['id'] ?>"
               onclick="return confirm('Delete this photo?');"
               class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
               style="z-index:2;">&times;</a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>
</body>
</html>
