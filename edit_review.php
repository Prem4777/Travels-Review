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

if ($review_id <= 0) {
    header("Location: review_history.php");
    exit;
}

// Fetch the review to edit, verify ownership
$sql = "SELECT r.rating, r.comment, d.name AS destination_name
        FROM reviews r
        JOIN destinations d ON r.destination_id = d.id
        WHERE r.id = $review_id AND r.user_id = $user_id";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) !== 1) {
    // Review not found or does not belong to user
    header("Location: review_history.php");
    exit;
}

$review = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    // Basic validation
    if ($rating < 1 || $rating > 5) {
        $error = "Rating must be between 1 and 5.";
    } elseif (empty($comment)) {
        $error = "Comment cannot be empty.";
    } else {
        // Update review in DB
        $update_sql = "UPDATE reviews SET rating = $rating, comment = '" . mysqli_real_escape_string($conn, $comment) . "' WHERE id = $review_id AND user_id = $user_id";
        if (mysqli_query($conn, $update_sql)) {
            mysqli_close($conn);
            header("Location: review_history.php");
            exit;
        } else {
            $error = "Failed to update review. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Review - Travels Review</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4" style="max-width: 600px;">
  <h2>Edit Review for "<?php echo htmlspecialchars($review['destination_name']); ?>"</h2>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label for="rating" class="form-label">Rating (1-5)</label>
      <input type="number" name="rating" id="rating" class="form-control" min="1" max="5" value="<?php echo isset($_POST['rating']) ? intval($_POST['rating']) : htmlspecialchars($review['rating']); ?>" required>
    </div>

    <div class="mb-3">
      <label for="comment" class="form-label">Comment</label>
      <textarea name="comment" id="comment" rows="4" class="form-control" required><?php echo isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : htmlspecialchars($review['comment']); ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Update Review</button>
    <a href="review_history.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
