<?php
session_start();
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = "Please enter both username and password.";
    } else {
        $sql = "SELECT id, password FROM users WHERE username='$username'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {
            list($id, $hashed_password) = mysqli_fetch_row($result);

            if (password_verify($password, $hashed_password)) {
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $id;
                header("Location: dashboard.php");
                exit;
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "Username not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login - Travels Review</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
    }
    .login-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.08);
      padding: 32px 28px 24px 28px;
      margin: 60px auto;
      max-width: 400px;
    }
    .login-card h2 {
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: #222;
      text-align: center;
    }
    .form-label {
      font-weight: 500;
    }
    .btn-primary {
      min-width: 120px;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <h2>Login</h2>
    <?php if ($message): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required />
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Login</button>

      </div>
      <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register here</a></p>
    </form>
  </div>
</body>
</html>
