<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Travels Review</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
    }
    .center-box {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
    }
    .welcome-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.08);
      padding: 40px 32px 32px 32px;
      max-width: 400px;
      width: 100%;
      text-align: center;
    }
    .welcome-card h1 {
      font-weight: 700;
      margin-bottom: 1.5rem;
      color: #222;
    }
    .welcome-card .btn {
      min-width: 120px;
    }
  </style>
</head>
<body>

  <div class="center-box">
    <div class="welcome-card">
      <h1 class="mb-4">Welcome to Travels Review</h1>
      <div>
        <a href="login.php" class="btn btn-primary me-3">Login</a>
        <a href="register.php" class="btn btn-success">Register</a>
      </div>
    </div>
  </div>

</body>
</html>
