<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome to Task Manager</title>
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
    }
    .btn-lg {
      padding: 0.75rem 1.5rem;
      font-size: 1.25rem;
    }
  </style>
</head>
<body>

<div class="card text-center">
  <h1 class="mb-3">Welcome to Task Manager</h1>
  <p class="mb-4">Log in to your account or create one to get started.</p>

  <div class="d-grid gap-3">
    <a href="/frontend/login.php" class="btn btn-primary btn-lg">Login</a>
    <a href="/frontend/users/create-user.html" class="btn btn-outline-secondary btn-lg">Create Account</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
