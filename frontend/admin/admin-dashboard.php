<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<?php include '../navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">Admin Dashboard</h1>
    <ul class="list-group">
        <li class="list-group-item"><a href="admin-users.php">User Management</a></li>
        <li class="list-group-item"><a href="admin-moderation.php">Content Moderation</a></li>
        <li class="list-group-item"><a href="admin-add-task.php">Manual Task Entry</a></li>
    </ul>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
