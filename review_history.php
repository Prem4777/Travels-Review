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

$sql = "SELECT r.id, r.rating, r.comment, r.created_at, d.name AS destination_name
        FROM reviews r
        JOIN destinations d ON r.destination_id = d.id
        WHERE r.user_id = $user_id
        ORDER BY r.created_at DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Review History - Travels Review</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Travels Review</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="review_history.php">Review History</a>
        </li>
      </ul>
      <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>
</nav>

<div class="container my-4">
  <h2>Your Review History</h2>

  <?php if(mysqli_num_rows($result) > 0): ?>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Destination</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['destination_name']); ?></td>
              <td><?php echo htmlspecialchars($row['rating']); ?> ⭐</td>
              <td><?php echo htmlspecialchars($row['comment']); ?></td>
              <td><?php echo date("d M Y, H:i", strtotime($row['created_at'])); ?></td>
              <td>
                <a href="edit_review.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="delete_review.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure to delete this review?');" class="btn btn-sm btn-danger">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p>You have not submitted any reviews yet.</p>
  <?php endif; ?>

</div>

</body>
</html>

<?php
mysqli_close($conn);
?>
