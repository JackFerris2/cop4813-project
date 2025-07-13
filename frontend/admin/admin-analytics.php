<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// active session check
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

// connect DB
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// get user stats
$userCount = 0;
$activeCount = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($result) {
    $userCount = $result->fetch_assoc()['total'];
}

$result = $conn->query("SELECT COUNT(*) AS active FROM users WHERE active = 1");
if ($result) {
    $activeCount = $result->fetch_assoc()['active'];
}

$inactiveCount = $userCount - $activeCount;

// Histogram group
$userTime = $_GET['userTime'] ?? '';

// Group-based user count query
switch (strtolower($userTime)) {
    case 'week':
        $sql = "SELECT YEARWEEK(created, 1) AS period, COUNT(*) AS count FROM users GROUP BY period ORDER BY period";
        break;
    case 'month':
        $sql = "SELECT DATE_FORMAT(created, '%Y-%m') AS period, COUNT(*) AS count FROM users GROUP BY period ORDER BY period";
        break;
    case 'day':
    default:
        $sql = "SELECT DATE(created) AS period, COUNT(*) AS count FROM users GROUP BY period ORDER BY period";
        break;
}

$startDates = [];
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (strtolower($userTime) === 'week') {
            $yearWeek = $row['period'];
            $year = substr($yearWeek, 0, 4);
            $week = substr($yearWeek, 4);
            $row['period'] = (new DateTime())->setISODate($year, $week)->format('Y-\WW');
        }
        $startDates[] = $row;
    }
}

$jsTime = json_encode(array_column($startDates, 'period'));
$jsCount = json_encode(array_column($startDates, 'count'));

// TASK STATS
$emailFilter = trim($_GET['email'] ?? '');
$taskTime = strtolower($_GET['taskTime'] ?? 'day');
$taskCount = 0;
$taskCompleteCount = 0;
$blockedCount = 0;

if ($emailFilter) {
    $emailEscaped = $conn->real_escape_string($emailFilter);

    // Join users and tasks by user_id, filter by email
    switch ($taskTime) {
        case 'week':
            $taskTrendQuery = "SELECT YEARWEEK(t.created, 1) AS period, COUNT(*) AS count
                               FROM tasks t
                               JOIN users u ON t.user_id = u.user_id
                               WHERE u.email = '$emailEscaped'
                               GROUP BY period ORDER BY period";
            break;
        case 'month':
            $taskTrendQuery = "SELECT DATE_FORMAT(t.created, '%Y-%m') AS period, COUNT(*) AS count
                               FROM tasks t
                               JOIN users u ON t.user_id = u.user_id
                               WHERE u.email = '$emailEscaped'
                               GROUP BY period ORDER BY period";
            break;
        case 'day':
        default:
            $taskTrendQuery = "SELECT DATE(t.created) AS period, COUNT(*) AS count
                               FROM tasks t
                               JOIN users u ON t.user_id = u.user_id
                               WHERE u.email = '$emailEscaped'
                               GROUP BY period ORDER BY period";
            break;
    }

    // Also filter task status chart:
    $taskStatusQuery = "SELECT t.status, COUNT(*) AS count
                        FROM tasks t
                        JOIN users u ON t.user_id = u.user_id
                        WHERE u.email = '$emailEscaped'
                        GROUP BY t.status";
} else {
    // Default when no email filter
    $taskStatusQuery = "SELECT status, COUNT(*) AS count FROM tasks GROUP BY status";
    switch ($taskTime) {
        case 'week':
            $taskTrendQuery = "SELECT YEARWEEK(created, 1) AS period, COUNT(*) AS count FROM tasks GROUP BY period ORDER BY period";
            break;
	case 'month':
            $taskTrendQuery = "SELECT DATE_FORMAT(created, '%Y-%m') AS period, COUNT(*) AS count FROM tasks GROUP BY period ORDER BY period";
	    break;
	case 'day':
	default:
            $taskTrendQuery = "SELECT DATE(created) AS period, COUNT(*) AS count FROM tasks GROUP BY period ORDER BY period";
	    break;
    }
}

$result = $conn->query("SELECT COUNT(*) AS total FROM tasks");
if ($result) {
    $taskCount = $result->fetch_assoc()['total'];
}

$result = $conn->query("SELECT COUNT(*) AS complete FROM tasks WHERE status = 'completed'");
if ($result) {
    $taskCompleteCount = $result->fetch_assoc()['complete'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE censor = 1");
if ($result) {
    $blockedCount = $result->fetch_assoc()['total'];
}

$completionRate = $taskCount > 0 ? round(($taskCompleteCount / $taskCount) * 100, 2) : 0;
	
// Status distribution
$taskStatusLabels = [];
$taskStatusCounts = [];
$taskStatusColors = [];

// define colors for the chart
$statusColorKey = [
    'completed' => '#00ff00',
    'in_progress' => '#ffff00',
    'not_started' => '#ff0000',
];

$result = $conn->query($taskStatusQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
	$status = trim(strtolower($row['status']));    
	$taskStatusLabels[] = str_replace('_', ' ', $row['status']);
        $taskStatusCounts[] = (int)$row['count'];
	$taskStatusColors[] = $statusColorKey[$status];
    }
}

$jsStatusLabels = json_encode($taskStatusLabels);
$jsStatusCounts = json_encode($taskStatusCounts);
$jsStatusColors = json_encode($taskStatusColors);

// time-based trends
$taskDates = [];
$taskCounts = [];

$trendResult = $conn->query($taskTrendQuery);
if ($trendResult) {
    while ($row = $trendResult->fetch_assoc()) {
        // Format week
        if ($taskTime === 'week') {
            $yearWeek = $row['period'];
            $year = substr($yearWeek, 0, 4);
            $week = substr($yearWeek, 4);
            $row['period'] = (new DateTime())->setISODate($year, $week)->format('Y-\WW');
        }
        $taskDates[] = $row['period'];
        $taskCounts[] = (int)$row['count'];
    }
}

// encode for JS
$jsTTrendLabels = json_encode($taskDates);
$jsTTrendCounts = json_encode($taskCounts);
$jsTTrendDates = json_encode($taskDates);
$jsTTrendDateCounts = json_encode($taskCounts);

function makeGet($key, $value) {
    $query = $_GET;
    $query[$key] = $value;
    return http_build_query($query);
}
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

<?php include __DIR__ . '/../navbar.php'; ?>

<a href="/frontend/admin/admin-dashboard.php" class="btn btn-secondary">Back to Dashboard</a>

<div class="container">
    <h1 class="mb-4">User Statistics</h1>

    <!-- Stats cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text fw-bold text-primary"><?php echo htmlspecialchars($userCount); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Active Users</h5>
                    <p class="card-text fw-bold text-success"><?php echo htmlspecialchars($activeCount); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Inactive Users</h5>
                    <p class="card-text fw-bold text-danger"><?php echo htmlspecialchars($inactiveCount); ?></p>
                </div>
            </div>
        </div>
    </div>


    <!-- Chart container -->
    <div class="mb-5">
        <h4>New User Registrations</h4>	
        <!-- userTime filter buttons -->
        <div class="btn-group mb-4" role="group">
            <a href="?<?php echo makeGet('userTime', 'day'); ?>" class="btn btn-outline-primary <?php echo $userTime == 'day' ? 'active' : ''; ?>">Day</a>
            <a href="?<?php echo makeGet('userTime', 'week'); ?>" class="btn btn-outline-primary <?php echo $userTime == 'week' ? 'active' : ''; ?>">Week</a>
            <a href="?<?php echo makeGet('userTime', 'month'); ?>" class="btn btn-outline-primary <?php echo $userTime == 'month' ? 'active' : ''; ?>">Month</a>
        </div>
        <canvas id="startdateChart" height="100"></canvas>
    </div>
</div>

<!-- Chart JS -->
<script>
const ctx = document.getElementById('startdateChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo $jsTime; ?>,
        datasets: [{
            label: 'New Users',
            data: <?php echo $jsCount; ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: '#007bff',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'New Users by <?php echo ucfirst($userTime); ?>'
            }
        },
        scales: {
            x: {
                title: { display: true, text: 'Time' }
            },
            y: {
                beginAtZero: true,
                title: { display: true, text: 'User Count' }
            }
        }
    }
});
</script>

<div class="container">
    <h1 class="mb-4">Activity Overview</h1>

    <!-- Sets filet emial and passes Get params -->
    <form method="get" class="mb-3 d-flex gap-2">
        <input type="email" name="email" class="form-control" placeholder="Filter by user." value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
	<?php foreach ($_GET as $key => $value): ?>
            <?php if ($key !== 'email'): ?>
                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Tasks</h5>
                    <p class="card-text fw-bold text-primary"><?php echo htmlspecialchars($taskCount); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Blocked Tasks</h5>
                    <p class="card-text fw-bold text-danger"><?php echo htmlspecialchars($blockedCount); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Avg Tasks per User</h5>
                    <p class="card-text fw-bold text-success"><?php echo round($taskCount / $userCount, 2); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Completion Rate</h5>
                    <p class="card-text fw-bold text-info"><?php echo $completionRate; ?>%</p>
                </div>
            </div>
        </div>
    </div>

    
    <!-- Time Trend Chart -->
    <div class="mb-5">
        <h4>Tasks Created Over Time</h4>
	<!-- userTime filter buttons -->
	<div class="btn-group mb-4" role="group">
            <a href="?<?php echo makeGet('taskTime', 'day'); ?>" class="btn btn-outline-primary <?php echo $taskTime == 'day' ? 'active' : ''; ?>">Day</a>
            <a href="?<?php echo makeGet('taskTime', 'week'); ?>" class="btn btn-outline-primary <?php echo $taskTime == 'week' ? 'active' : ''; ?>">Week</a>
            <a href="?<?php echo makeGet('taskTime', 'month'); ?>" class="btn btn-outline-primary <?php echo $taskTime == 'month' ? 'active' : ''; ?>">Month</a>
        </div>
        <canvas id="taskTrendChart" height="100"></canvas>
    </div>
    <!-- Task Status Chart -->
    <div class="mb-5">
        <h4>Tasks by Status</h4>
        <canvas id="taskStatusChart" height="100"></canvas>
    </div>
</div>

<script>
    const statusCtx = document.getElementById('taskStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: <?php echo $jsStatusLabels; ?>,
            datasets: [{
                label: 'Tasks by Status',
                data: <?php echo $jsStatusCounts; ?>,
		backgroundColor: <?php echo $jsStatusColors; ?>,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                title: {
                    display: true,
                    text: 'Tasks by Status'
                }
            }
        }
    });

    const trendCtx = document.getElementById('taskTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo $jsTTrendDates; ?>,
            datasets: [{
                label: 'Tasks Created',
                data: <?php echo $jsTTrendCounts; ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Task Creation by <?php echo ucfirst($taskTime ?: 'day'); ?>'
                }
            },
            scales: {
                x: { title: { display: true, text: 'Date' }},
                y: { title: { display: true, text: 'Tasks' }, beginAtZero: true }
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<a href="/frontend/admin/admin-dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</body>
</html>

<?php $conn->close(); ?>
