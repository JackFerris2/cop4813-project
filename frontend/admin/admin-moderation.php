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
    <title>Content Moderation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<?php include '../navbar.php'; ?>

<div class="container">
    <h1>Content Moderation</h1>
    <table class="table">
        <thead>
        <tr>
            <th>Task</th>
            <th>User</th>
            <th>Status</th>
            <th colspan="3">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Sample Task</td>
            <td>john@example.com</td>
            <td>Pending</td>
            <td><button class="btn btn-success btn-sm">Approve</button></td>
            <td><button class="btn btn-danger btn-sm">Reject</button></td>
            <td><button class="btn btn-secondary btn-sm">Flag</button></td>
        </tr>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
