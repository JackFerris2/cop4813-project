<?php
// active session check
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create a New Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1>Create a New Task</h1>
    <form method="POST" action="../../backend/add-task.php">
        <div class="mb-3">
            <label for="taskTitle" class="form-label">Task Title</label>
            <input type="text" class="form-control" name="taskTitle" placeholder="Enter title">
        </div>
        <div class="mb-3">
            <label for="taskDescription" class="form-label">Description</label>
            <textarea class="form-control" name="taskDescription" rows="4" placeholder="Enter description"></textarea>
        </div>
        <div class="mb-3">
            <label for="taskStatus" class="form-label">Status</label>
            <select class="form-select" name="taskStatus">
                <option value="todo">To Do</option>
                <option value="inprogress">In Progress</option>
                <option value="done">Done</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Create Task</button>
        <a href="/frontend/dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
</body>
</html>
