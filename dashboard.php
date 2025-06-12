<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

$username = $_SESSION['username'];

// search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// avg
$sql = "SELECT destinations.*, 
        (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE destination_id = destinations.id) AS avg_rating,
        users.username AS creator_username
        FROM destinations
        LEFT JOIN users ON destinations.user_id = users.id";

if ($search !== '') {
    $search_lower = strtolower($search);
    $sql .= " WHERE LOWER(destinations.name) LIKE '%$search_lower%'";
}

$sql .= " GROUP BY destinations.id";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User Dashboard - Travels Review</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
  <!-- navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="dashboard.php">Travels Review</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="review_history.php">Review History</a>
          </li>
        </ul>
        <a href="logout.php" class="btn btn-danger">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container my-4">
    <h1 class="mb-4">Welcome, <?= htmlspecialchars($username); ?>!</h1>

    <!-- Add Destination Button -->
    <div class="mb-3">
      <a href="add_destination.php" class="btn btn-success">Add Destination</a>
    </div>

    <!-- Search Bar -->
    <form method="GET" action="dashboard.php" class="mb-4">
      <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Search destinations..." value="<?= htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary">Search</button>
      </div>
    </form>

    <!-- destinations -->
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <?php
        if ($result && mysqli_num_rows($result) > 0) {
          while ($dest = mysqli_fetch_assoc($result)) {
            echo '<div class="col">';
            echo '  <div class="card h-100">';
            // Show thumbnail if available
            if (!empty($dest['image'])) {
              echo '    <img src="' . htmlspecialchars($dest['image']) . '" class="card-img-top" style="height:180px;object-fit:cover;" alt="Thumbnail">';
            }
            echo '    <div class="card-body">';
            echo '      <h5 class="card-title">' . htmlspecialchars($dest['name']) . '</h5>';
            echo '      <p class="card-text">' . htmlspecialchars($dest['description']) . '</p>';
            echo '      <p>Average Rating: ' . number_format($dest['avg_rating'], 1) . ' ⭐</p>';
            echo '      <p class="text-muted mb-1" style="font-size:0.95em;">Added by: ' . htmlspecialchars($dest['creator_username']) . '</p>';
            echo '      <a href="destination.php?id=' . $dest['id'] . '" class="btn btn-primary mb-2">View & Review</a> ';
            // Only show Edit/Delete if the logged-in user added this destination
            if (isset($_SESSION['user_id']) && $dest['user_id'] == $_SESSION['user_id']) {
              echo '      <a href="edit_destination.php?id=' . $dest['id'] . '" class="btn btn-warning mb-2">Edit</a> ';
              echo '      <a href="delete_destination.php?id=' . $dest['id'] . '" class="btn btn-danger mb-2" onclick="return confirm(\'Are you sure you want to delete this destination?\')">Delete</a>';
            }
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
          }
        } else {
          echo '<p>No destinations found.</p>';
        }
      ?>
    </div>
  </div>

</body>
</html>
