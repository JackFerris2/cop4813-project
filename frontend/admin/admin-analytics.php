<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /index.php");
    exit;
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: /index.php");
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

$userCount = 0;
$activeCount = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($result) $userCount = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) AS active FROM users WHERE active = 1");
if ($result) $activeCount = $result->fetch_assoc()['active'];

$inactiveCount = $userCount - $activeCount;

$userTime = $_GET['userTime'] ?? '';

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

$emailFilter = trim($_GET['email'] ?? '');
$taskTime = strtolower($_GET['taskTime'] ?? 'day');
$taskCount = $taskCompleteCount = $blockedCount = 0;

if ($emailFilter) {
    $emailEscaped = $conn->real_escape_string($emailFilter);
    switch ($taskTime) {
        case 'week':
            $taskTrendQuery = "SELECT YEARWEEK(t.created, 1) AS period, COUNT(*) AS count FROM tasks t JOIN users u ON t.user_id = u.user_id WHERE u.email = '$emailEscaped' GROUP BY period ORDER BY period";
            break;
        case 'month':
            $taskTrendQuery = "SELECT DATE_FORMAT(t.created, '%Y-%m') AS period, COUNT(*) AS count FROM tasks t JOIN users u ON t.user_id = u.user_id WHERE u.email = '$emailEscaped' GROUP BY period ORDER BY period";
            break;
        default:
            $taskTrendQuery = "SELECT DATE(t.created) AS period, COUNT(*) AS count FROM tasks t JOIN users u ON t.user_id = u.user_id WHERE u.email = '$emailEscaped' GROUP BY period ORDER BY period";
            break;
    }
    $taskStatusQuery = "SELECT t.status, COUNT(*) AS count FROM tasks t JOIN users u ON t.user_id = u.user_id WHERE u.email = '$emailEscaped' GROUP BY t.status";
} else {
    switch ($taskTime) {
        case 'week':
            $taskTrendQuery = "SELECT YEARWEEK(created, 1) AS period, COUNT(*) AS count FROM tasks GROUP BY period ORDER BY period";
            break;
        case 'month':
            $taskTrendQuery = "SELECT DATE_FORMAT(created, '%Y-%m') AS period, COUNT(*) AS count FROM tasks GROUP BY period ORDER BY period";
            break;
        default:
            $taskTrendQuery = "SELECT DATE(created) AS period, COUNT(*) AS count FROM tasks GROUP BY period ORDER BY period";
            break;
    }
    $taskStatusQuery = "SELECT status, COUNT(*) AS count FROM tasks GROUP BY status";
}

$result = $conn->query("SELECT COUNT(*) AS total FROM tasks");
if ($result) $taskCount = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) AS complete FROM tasks WHERE status = 'completed'");
if ($result) $taskCompleteCount = $result->fetch_assoc()['complete'];

$result = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE censor = 1");
if ($result) $blockedCount = $result->fetch_assoc()['total'];

$completionRate = $taskCount > 0 ? round(($taskCompleteCount / $taskCount) * 100, 2) : 0;

$statusColorKey = [
    'completed' => '#00ff00',
    'in_progress' => '#ffff00',
    'not_started' => '#ff0000',
];

$taskStatusLabels = $taskStatusCounts = $taskStatusColors = [];

$result = $conn->query($taskStatusQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['status']);
        $taskStatusLabels[] = str_replace('_', ' ', $row['status']);
        $taskStatusCounts[] = (int)$row['count'];
        $taskStatusColors[] = $statusColorKey[$status];
    }
}

$jsStatusLabels = json_encode($taskStatusLabels);
$jsStatusCounts = json_encode($taskStatusCounts);
$jsStatusColors = json_encode($taskStatusColors);

$taskDates = $taskCounts = [];
$trendResult = $conn->query($taskTrendQuery);
if ($trendResult) {
    while ($row = $trendResult->fetch_assoc()) {
        if ($taskTime === 'week') {
            $year = substr($row['period'], 0, 4);
            $week = substr($row['period'], 4);
            $row['period'] = (new DateTime())->setISODate($year, $week)->format('Y-\WW');
        }
        $taskDates[] = $row['period'];
        $taskCounts[] = (int)$row['count'];
    }
}

$jsTTrendDates = json_encode($taskDates);
$jsTTrendCounts = json_encode($taskCounts);

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

<div class="container">
    <a href="/frontend/admin/admin-dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>
    <h1>User Statistics</h1>

    <!-- Stats Cards Here (same as before) -->

    <h4 class="mt-5">Tasks by Status</h4>
    <div class="mx-auto mb-5" style="max-width: 500px;">
        <canvas id="taskStatusChart"></canvas>
    </div>
</div>

<script>
const statusCtx = document.getElementById('taskStatusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: <?php echo $jsStatusLabels; ?>,
        datasets: [{
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
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
