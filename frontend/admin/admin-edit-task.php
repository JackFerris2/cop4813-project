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

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// get user_id from admin-users.php / redirect if userID is bad
$taskID = trim($_POST['task_id'] ?? '');
if (empty($taskID)) {
    header("Location: /frontend/admin/admin-tasks.php");
    exit;
}

$servername = "localhost";
$username = "taskmanager";
$password = "password25";
$database = "taskmanagement";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// get current values of the task.
$stmt = $conn->prepare("SELECT * FROM tasks WHERE task_id = ?");
$stmt->bind_param("i", $taskID);
$stmt->execute();
$result = $stmt->get_result();
$current = $result->fetch_assoc();
$conn->close();

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT * FROM users ORDER BY email");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<?php include __DIR__ . '/../navbar.php'; ?>
<div class="container">
    <h1>Edit Task</h1>
    <form method="POST" action="/backend/admin-edit-task.php">
	<div class="mb-3">
	    <input type="hidden" name="task_id" value="<?= htmlspecialchars($taskID) ?>">
	</div>
	<div class="mb-3">
            <label for="editTaskTitle" class="form-label">Task Title</label>
            <input type="text" class="form-control" name="taskTitle" value="<?php echo htmlspecialchars($current['title']); ?>">
        </div>
        <div class="mb-3">
            <label for="editTaskDescription" class="form-label">Description</label>
	    <textarea class="form-control" name="taskDescription" rows="4"><?=htmlspecialchars($current['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="editTaskStatus" class="form-label">Status</label>
            <select class="form-select" name="taskStatus" required> 
		<option value="not_started" <?= $current['status'] === 'not_started' ? 'selected' : '' ?>>To Do</option>
		<option value="in_progress" <?= $current['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
		<option value="completed" <?= $current['status'] === 'completed' ? 'selected' : '' ?>>Done</option>
            </select>
	</div>
	<div class="mb-3">
            <label for="taskOwner" class="form-label">Owner</label>
            <select class="form-select" name="taskOwner" required>
                <?php while ($row = $result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['user_id']) ?>"
    				<?= $row['user_id'] == $current['user_id'] ? 'selected' : '' ?>>
    				<?= htmlspecialchars($row['name'] . ' - ' . $row['email']) ?>
			</option>
                <?php endwhile; ?>
            </select>
	</div>
	<div class="mt=d d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save Changes</button>
	    <a href="/frontend/admin/admin-tasks.php" class="btn btn-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>	
</body>
</html>
