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
