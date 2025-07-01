<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Task Manager</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .card {
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      background-color: #fff;
      width: 100%;
      max-width: 400px;
    }
  </style>
</head>
<body>

<div class="card">
  <h3 class="mb-4 text-center">Login to Task Manager</h3>
  <form method="POST" action="/backend/authenticate.php">
    <div class="mb-3">
      <label for="email" class="form-label">Email address</label>
      <input type="email" class="form-control" id="email" name="email" required placeholder="you@example.com">
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" id="password" name="password" required placeholder="Your password">
    </div>
    <button type="submit" class="btn btn-primary w-100">Login</button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
