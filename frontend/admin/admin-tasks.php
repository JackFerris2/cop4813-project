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

$stmt = $conn->prepare("SELECT tasks.*, users.user_id AS user_id, users.email AS email 
	FROM tasks JOIN users ON tasks.user_id = users.user_id ORDER BY tasks.title;");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Moderation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<?php include '../navbar.php'; ?>

<div class="container">
    <h1>Task Moderation</h1>
    <table class="table">
        <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
	    <th>User</th>
            <th colspan="3">Actions</th>
        </tr>
        </thead>
	<tbody>
	<?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
		<td>
		    <form method="post" action="/frontend/admin/admin-edit-task.php" style="display:inline;">
		        <input type="hidden" name="task_id" value="<?= htmlspecialchars($row['task_id']) ?>">
		        <button class="btn btn-primary btn-sm">Edit</button>
		    </form>
		</td>
		<td>
		    <form method="post" action="/backend/admin-toggle-task.php" style="display:inline;">
			<input type="hidden" name="task_id" value="<?= htmlspecialchars($row['task_id']) ?>">
			<button class="btn btn-warning btn-sm">
		            <?= $row['censor'] ? 'Unflag' : 'Flag' ?>
		        </button>
		    </form>
		</td>
		<td>
		    <form method="post" action="/backend/admin-delete-task.php" style="display:inline;" onsubmit="return confirm('Are you sure?');">
			<input type="hidden" name="task_id" value="<?= htmlspecialchars($row['task_id']) ?>">
		        <button class="btn btn-danger btn-sm">Delete</button>
		    </form>
		</td>
            </tr>
	<?php endwhile; ?>
	</tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
