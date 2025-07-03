<?php
// active session check
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /index.php");
    exit;
}
// admin check
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: /index.php");
    exit;
}
?>
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
        <li class="list-group-item"><a href="admin-moderation.php">Task Moderation</a></li>
        <li class="list-group-item"><a href="admin-add-task.php">Admin Task Entry</a></li>
    </ul>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
