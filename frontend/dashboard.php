<?php
// active session check
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
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

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [
    'not_started' => [],
    'in_progress' => [],
    'completed' => []
];

while ($row = $result->fetch_assoc()) {
    $tasks[$row['status']][] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<?php include 'navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">My Tasks</h1>
    <div class="row text-center">
        <?php
        $statuses = ['not_started' => 'To Do', 'in_progress' => 'In Progress', 'completed' => 'Done'];
        foreach ($statuses as $key => $label): ?>
            <div class="col-md-4">
                <h3><?= $label ?></h3>
                <div class="bg-light border rounded p-3" style="min-height: 300px;">
                    <?php foreach ($tasks[$key] as $task): ?>
                        <div class="card mb-2">
                            <div class="card-body">
				<strong>
				    <?php
					if($task['censor'] == True) {
						echo "Censored";
					} else {
						echo htmlspecialchars($task['title']);
					}
				    ?>
				</strong><br>
				<small>
				    <?php
					if($task['censor'] == True) {
						echo "This task has been censored by an administrator.";
					} else {
						echo htmlspecialchars($task['description']);
					}
				    ?>
				</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-end mt-4">
        <a href="/frontend/tasks/create-task.php" class="btn btn-primary">+ Add Task</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
