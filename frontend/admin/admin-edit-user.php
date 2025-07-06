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

$servername = "localhost";
$username = "taskmanager";
$password = "password25";
$database = "taskmanagement";

// get user_id from admin-users.php / redirect if userID is bad
$userID = trim($_POST['user_id'] ?? '');
if (empty($userID)) {
    header("Location: /frontend/admin/admin-users.php");
    exit;
}

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<?php include __DIR__ . '/../navbar.php'; ?>
<div class="container">
    <h1>Edit an Existing User</h1>
    <form method="POST" action="/backend/admin-edit-user.php">
	 <div class="mb-3">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
        </div>
	<div class="mb-3">
            <label for="fullName" class="form-label">Name</label>
            <input type="text" class="form-control" name="fullName" value="<?php echo htmlspecialchars($user['name']); ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
	    <p class="form-text">Leave blank to leave password as it is.</p>
            <input type="text" class="form-control" name="password" placeholder="Enter Password">
        </div>
	<div class="mb-3">
            <label for="created" class="form-label">Creation Date</label>
	    <input type="text" class="form-control" name="created" value="
	        <?php echo htmlspecialchars($user['created']); ?>" readonly>
	</div>
	<button type="submit" class="btn btn-success">Submit</button>
        <a href="/frontend/admin/admin-users.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
