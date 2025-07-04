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

$stmt = $conn->prepare("SELECT * FROM users ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<?php include '../navbar.php'; ?>

<div class="container">
    <h1>User Management</h1>
    <table class="table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
	    <th>Admin</th>
            <th>Status</th>
            <th colspan="4">Actions</th>
        </tr>
        </thead>
        <tbody>
	<?php while ($row = $result->fetch_assoc()): ?>
	   <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
		<td><?= $row['admin'] ? 'Yes' : 'No' ?></td>
		<td><?= $row['active'] ? 'Active' : 'Inactive' ?></td>
                <td>
                    <form method="post" action="/backend/toggle-user.php" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['user_id']) ?>">
                        <button type="submit" class="btn btn-warning btn-sm">
                            <?= $row['active'] ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>
                </td>
		<td>
                    <form method="post" action="/backend/toggle-admin.php" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['user_id']) ?>">
                        <button type="submit" class="btn btn-warning btn-sm">
                            <?= $row['admin'] ? 'Remove Admin' : 'Make Admin' ?>
                        </button>
                    </form>
                </td>
		<td>
		    <form method="post" action="/frontend/users/edit-user.php" style="display:inline;">
		        <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['user_id']) ?>">
			<button type="submit" class="btn btn-primary btn-sm">Edit</button>
			<!-- <a href="/frontend/users/edit-user.php?uid=<?= urlencode($row['user_id']) ?>" class="btn btn-primary btn-sm">Edit</a></td> -->
                <td>
                    <form method="post" action="delete-user.php" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['user_id']) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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
<?php
$conn->close();
?>
