<?php
// active session check
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /index.php");
    exit;
}
?>

<h1>Create a New Task</h1>
<form action="/backend/submit-task.php" method="POST">
    <div class="mb-3">
        <label for="taskTitle" class="form-label">Task Title</label>
        <input type="text" class="form-control" id="taskTitle" name="title" required placeholder="Enter title">
    </div>
    <div class="mb-3">
        <label for="taskDescription" class="form-label">Description</label>
        <textarea class="form-control" id="taskDescription" name="description" rows="4" required placeholder="Enter description"></textarea>
    </div>
    <div class="mb-3">
        <label for="taskStatus" class="form-label">Status</label>
        <select class="form-select" id="taskStatus" name="status" required>
            <option value="not_started">Not Started</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
        </select>
    </div>
    <button type="submit" class="btn btn-success">Create Task</button>
    <a href="/frontend/dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
</form>
</div>
</body>
</html>
