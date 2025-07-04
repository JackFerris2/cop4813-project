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

// DB Info
$servername = "localhost";
$username = "taskmanager";
$password = "password25";
$database = "taskmanagement";

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
    <title>Admin Add Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<?php include '../navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">Add Task (Admin Only)</h1>
    <form method="POST" action="/backend/add-admintask.php">
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
	 <div class="mb-3">
            <label for="taskOwner" class="form-label">Owner</label>
            <select class="form-select" id="taskOwner" name="taskOwner" required>
		<?php while ($row = $result->fetch_assoc()): ?>
			<option value="<?= htmlspecialchars($row['user_id']) ?>">
				<?= htmlspecialchars($row['name'] . ' - ' . $row['email']) ?>
			</option> 
		<?php endwhile; ?>
            </select>
        </div>
	<button type="submit" class="btn btn-success">Create Task</button>
        <a href="/frontend/admin/admin-dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
