<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Add Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<?php include '../navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">Add Task (Admin Only)</h1>
    <form method="POST" action="../../backend/add-task.php">
        <div class="mb-3">
            <label for="taskTitle" class="form-label">Task Title</label>
            <input type="text" class="form-control" id="taskTitle" name="taskTitle" required placeholder="Enter title">
        </div>
        <div class="mb-3">
            <label for="taskDescription" class="form-label">Description</label>
            <textarea class="form-control" id="taskDescription" name="taskDescription" rows="4" required placeholder="Enter description"></textarea>
        </div>
        <div class="mb-3">
            <label for="taskStatus" class="form-label">Status</label>
            <select class="form-select" id="taskStatus" name="taskStatus" required>
                <option value="not_started">To Do</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Done</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Create Task</button>
        <a href="admin-dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
