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
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 210px;
      background: #222;
      color: #fff;
      padding-top: 40px;
      z-index: 100;
    }
    .sidebar h3 {
      color: #fff;
      text-align: center;
      margin-bottom: 2rem;
      font-size: 1.4rem;
      letter-spacing: 1px;
    }
    .sidebar a {
      display: block;
      color: #fff;
      padding: 12px 30px;
      text-decoration: none;
      font-size: 1.05rem;
      transition: background 0.2s;
    }
    .sidebar a.active, .sidebar a:hover {
      background: #444;
      color: #ffc107;
    }
    .sidebar .logout-btn {
      margin: 2rem 30px 0 30px;
      display: block;
      background: #dc3545;
      color: #fff;
      border: none;
      padding: 10px 0;
      text-align: center;
      border-radius: 4px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.2s;
    }
    .sidebar .logout-btn:hover {
      background: #b52a37;
      color: #fff;
    }
    .main-content {
      margin-left: 220px;
      padding: 30px 20px 20px 20px;
    }
    @media (max-width: 700px) {
      .sidebar {
        width: 100vw;
        height: auto;
        position: static;
        padding-top: 10px;
      }
      .main-content {
        margin-left: 0;
        padding: 15px 5px;
      }
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h3>Travels Review</h3>
    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">Home</a>
    <a href="review_history.php" class="<?= basename($_SERVER['PHP_SELF']) === 'review_history.php' ? 'active' : '' ?>">Review History</a>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <div class="main-content">
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
