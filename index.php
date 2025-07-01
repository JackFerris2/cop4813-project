<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome to Task Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container text-center">
  <h1 class="mb-4">Welcome to the Task Manager</h1>
  <p class="lead">Please log in or create an account to continue.</p>
  <div class="d-grid gap-3 col-6 mx-auto mt-4">
    <a href="/frontend/admin/admin-login.html" class="btn btn-primary btn-lg">Admin Login</a>
    <a href="/frontend/users/create-user.html" class="btn btn-outline-secondary btn-lg">Create Account</a>
    <a href="/frontend/tasks/create-task.html" class="btn btn-success btn-lg">Continue as User</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
