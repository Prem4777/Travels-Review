<?php
session_start();
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password)) {
        $message = "Please fill all fields.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $check_sql = "SELECT id FROM users WHERE username='$username' OR email='$email'";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $message = "Username or email already taken.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
            if (mysqli_query($conn, $insert_sql)) {
                $_SESSION['username'] = $username;
                header("Location: login.php");
                exit;
            } else {
                $message = "Registration failed. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register - Travels Review</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
    }
    .register-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.08);
      padding: 32px 28px 24px 28px;
      margin: 60px auto;
      max-width: 500px;
    }
    .register-card h2 {
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
  <div class="register-card">
    <h2>Register</h2>
    <?php if ($message): ?>
      <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" action="register.php">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required />
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" required />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required />
      </div>
      <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required />
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Register</button>
      </div>
      <p class="mt-3 text-center">Already have an account? <a href="login.php">Login here</a></p>
    </form>
  </div>
</body>
</html>
