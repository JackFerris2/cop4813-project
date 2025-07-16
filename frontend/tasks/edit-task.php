<?php
// error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
    <title>Edit Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<?php include '/frontend/navbar.php' ?>
<div class="container">
    <h1>Edit Task</h1>
    <form>
        <div class="mb-3">
            <label for="editTaskTitle" class="form-label">Task Title</label>
            <input type="text" class="form-control" id="editTaskTitle" value="Sample Task Title">
        </div>
        <div class="mb-3">
            <label for="editTaskDescription" class="form-label">Description</label>
            <textarea class="form-control" id="editTaskDescription" rows="4">Sample task description.</textarea>
        </div>
        <div class="mb-3">
            <label for="editTaskStatus" class="form-label">Status</label>
            <select class="form-select" id="editTaskStatus">
                <option value="todo">To Do</option>
                <option value="inprogress" selected>In Progress</option>
                <option value="done">Done</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="../dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
