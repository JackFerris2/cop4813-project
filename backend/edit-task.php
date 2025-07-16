<?php
// active session check
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /index.php");
    exit;
}

// DB connection
$host = 'localhost';
$dbname = 'taskmanagement';
$username = 'taskmanager';
$password = 'password25';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskID = $_POST['task_id'] ?? '';
    $taskTitle = $_POST['taskTitle'] ?? '';
    $taskDescription = $_POST['taskDescription'] ?? '';
    $taskStatus = $_POST['taskStatus'] ?? '';

    if (!empty($taskID) && !empty($taskTitle) && !empty($taskStatus)) {
        $msg = $conn->prepare("SELECT * FROM tasks WHERE task_id = ?");
        $msg->bind_param("i", $taskID);
        $msg->execute();
        $result = $msg->get_result();
        $current = $result->fetch_assoc();
        $msg->close();

        if (!$current || $_SESSION['user_id'] !== $current['user_id']) {
            header("Location: /index.php");
            exit;
        }

        $msg = $conn->prepare("UPDATE tasks SET title = ?, description = ?, status = ? WHERE task_id = ?");
        $msg->bind_param("sssi", $taskTitle, $taskDescription, $taskStatus, $taskID);
        $msg->execute();
        $msg->close();

        header("Location: /frontend/dashboard.php");
        exit;
    } else {
        $error = "All fields are required.";
    }
}

// Handle GET request
$taskID = $_GET['id'] ?? '';
$task = null;
if (!empty($taskID) && is_numeric($taskID)) {
    $msg = $conn->prepare("SELECT * FROM tasks WHERE task_id = ?");
    $msg->bind_param("i", $taskID);
    $msg->execute();
    $result = $msg->get_result();
    $task = $result->fetch_assoc();
    $msg->close();

    if (!$task || $_SESSION['user_id'] !== $task['user_id']) {
        header("Location: /index.php");
        exit;
    }
} else {
    echo "Invalid task ID.";
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '/var/www/cis4813/frontend/navbar.php' ?>
<body class="p-4">
<div class="container">
    <h1 class="mb-4">Edit Task</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="edit-task.php">
        <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['task_id']) ?>">
        <div class="mb-3">
            <label for="taskTitle" class="form-label">Title</label>
            <input type="text" class="form-control" id="taskTitle" name="taskTitle"
                   value="<?= htmlspecialchars($task['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="taskDescription" class="form-label">Description</label>
            <textarea class="form-control" id="taskDescription" name="taskDescription" rows="4"><?= htmlspecialchars($task['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="taskStatus" class="form-label">Status</label>
            <select class="form-select" id="taskStatus" name="taskStatus" required>
                <option value="not_started" <?= $task['status'] === 'not_started' ? 'selected' : '' ?>>To Do</option>
                <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-primary">Update Task</button>
            <a href="/frontend/dashboard.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
