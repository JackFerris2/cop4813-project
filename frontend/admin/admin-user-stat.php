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

// get total users
$result = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($result) {
	$row = $result->fetch_assoc();
	$userCount = $row['total'];
} else {
	die("Error: " . $conn->error);
}

// get active users
$result = $conn->query("SELECT COUNT(*) AS active FROM users WHERE active = 1");
if ($result) {
	$row = $result->fetch_assoc();
	$activeCount = $row['active'];
} else {
	die("Error: " . $conn->error);
}

// set default histogram period
$group = $_GET['group'] ?? '';

// set SQL quety based on period
switch ($group) {
    case 'Week':
        $sql = "SELECT YEARWEEK(created, 1) AS period, COUNT(*) AS count FROM users GROUP BY period ORDER BY period";
        break;
    case 'Month':
        $sql = "SELECT DATE_FORMAT(created, '%Y-%m') AS period, COUNT(*) AS count FROM users GROUP BY period ORDER BY period";
        break;
    case 'Day':
    default:
        $sql = "SELECT DATE(created) AS period, COUNT(*) AS count FROM users GROUP BY period ORDER BY period";
        break;
}

$result = $conn->query($sql);
$startDates = [];

// format dates and put in startDates 
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($group === 'week') {
            $yearWeek = $row['period'];
            $year = substr($yearWeek, 0, 4);
            $week = substr($yearWeek, 4);
            $date = (new DateTime())->setISODate($year, $week)->format('Y-\WW');
            $row['period'] = $date;
        }
        $startDates[] = $row;
    }
}

// encode in JSON
$time = array_column($startDates, 'period');
$count = array_column($startDates, 'count');

$jsTime = json_encode($time);
$jsCount = json_encode($count);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Statistics</title>    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="p-4">

<?php include '/frontend/navbar.php'; ?>

<div class="container">
    <h1>User Statistics</h1>
    <p>
        <?php echo "Total Users: " . htmlspecialchars($userCount); ?><br>
        <?php echo "Active Users: " . htmlspecialchars($activeCount); ?><br>
        <?php echo "Inactive Users: " . htmlspecialchars($userCount - $activeCount); ?>
    </p>
    <!-- Select time distribution -->
    <div class="btn-group mb-3" role="group">
        <a href="?group=Day" class="btn btn-outline-primary <?php echo $group == 'day' ? 'active' : ''; ?>">Day</a>
        <a href="?group=Week" class="btn btn-outline-primary <?php echo $group == 'week' ? 'active' : ''; ?>">Week</a>
        <a href="?group=Month" class="btn btn-outline-primary <?php echo $group == 'month' ? 'active' : ''; ?>">Month</a>
    </div>

    <!-- Chart dimensions --->
    <canvas id="startdateChart"></canvas>

    <!-- Chart Code -->
    <script>
	const ctx = document.getElementById('startdateChart').getContext('2d');
	const signupChart = new Chart(ctx, {
	    type: 'bar',
	    data: {
		labels: <?php echo $jsTime; ?>,
		datasets: [{
		    label: 'New Users',
		    data: <?php echo $jsCount; ?>,
		    backgroundColor: 'rgba(20, 85, 255, .75)' // this color seems to match the blue
		}]
	    },
	    options: {
		responsive: true,
		scales: {
		    x: {
			title: {
			    display: true,
			    text: 'Signup <?php echo $group; ?>'
			}
		    },
		    y: {
			beginAtZero: true,
			title: {
			    display: true,
			    text: 'New Accounts'
			}
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
