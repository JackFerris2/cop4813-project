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

// get total tasks
$result = $conn->query("SELECT COUNT(*) AS total FROM tasks");
if ($result) {
	$row = $result->fetch_assoc();
	$taskCount = $row['total'];
} else {
	die("Error: " . $conn->error);
}

// get total tasks blocked
$result = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE censor = 1");
if ($result) {
	$row = $result->fetch_assoc();
	$blockedCount = $row['total'];
} else {
	die("Error: " . $conn->error);
}

// get total users
$result = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($result) {
	$row = $result->fetch_assoc();
	$userCount = $row['total'];
} else {
	die("Error: " . $conn->error);
}

// get task status counts
$statusResult = $conn->query("SELECT status, COUNT(*) AS count FROM tasks GROUP BY status");

$taskStatusLabels = [];
$taskStatusCounts = [];

if ($statusResult) {
    while ($row = $statusResult->fetch_assoc()) {
        $taskStatusLabels[] = str_replace('_', ' ', $row['status']);
        $taskStatusCounts[] = (int)$row['count'];
    }
} else {
    die("Error: " . $conn->error);
}

// encode for JS
$jsStatusLabels = json_encode($taskStatusLabels);
$jsStatusCounts = json_encode($taskStatusCounts);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Overview</title>    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="p-4">

<?php include '/frontend/navbar.php'; ?>

	<div class="container">
	    <h1>Activity Overview</h1>
	    <p>
		<?php echo "Total Tasks: " . htmlspecialchars($taskCount); ?><br>
		<?php echo "Blocked Tasks: " . htmlspecialchars($blockedCount); ?><br>
		<?php echo "Average tasks per user: " . htmlspecialchars($taskCount / $userCount); ?><br>
	    </p> 

	    <!-- Chart dimensions --->
	    <canvas id="taskStatusChart"></canvas>

	    <!-- Chart Code -->
	    <script>
		const statusCtx = document.getElementById('taskStatusChart').getContext('2d');
		const statusChart = new Chart(statusCtx, {
		    type: 'pie',
		    data: {
			labels: <?php echo $jsStatusLabels; ?>,
			datasets: [{
			    label: 'Tasks by Status',
			    data: <?php echo $jsStatusCounts; ?>,
			    backgroundColor: [
				'rgba(0, 255, 0, .7)',    // Not Started
				'rgba(255, 255, 0, .7)',    // In Progress
				'rgba(255, 0, 0, .7)'     // Completed
			    ],
			    borderColor: 'black'
			}]
		    },
		    options: {
			responsive: true,
			plugins: {
			    legend: {
				position: 'bottom'
			    },
			    title: {
				display: true,
				text: 'Task by Status'
			    }
			}
		    }
		});
	    </script>
	</div>

    <a href="/frontend/admin/admin-dashboard.php" class="btn btn-secondary ms-2">Dashboard</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
