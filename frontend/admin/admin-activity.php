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
$taskCount = $conn->query("SELECT COUNT(*) AS total FROM tasks")->fetch_assoc()['total'] ?? 0;

// get total tasks blocked
$blockedCount = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE censor = 1")->fetch_assoc()['total'] ?? 0;

// get total users
$userCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 1;

// get completed task count
$completedCount = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
$completionRate = $taskCount > 0 ? round(($completedCount / $taskCount) * 100, 2) : 0;

// task status breakdown
$statusResult = $conn->query("SELECT status, COUNT(*) AS count FROM tasks GROUP BY status");
$taskStatusLabels = [];
$taskStatusCounts = [];
while ($row = $statusResult->fetch_assoc()) {
    $taskStatusLabels[] = ucwords(str_replace('_', ' ', $row['status']));
    $taskStatusCounts[] = (int)$row['count'];
}

// time-based trends
$trendResult = $conn->query("SELECT DATE(created) AS day, COUNT(*) AS count FROM tasks GROUP BY day ORDER BY day ASC");
$taskDates = [];
$taskCounts = [];
while ($row = $trendResult->fetch_assoc()) {
    $taskDates[] = $row['day'];
    $taskCounts[] = (int)$row['count'];
}

// encode for JS
$jsStatusLabels = json_encode($taskStatusLabels);
$jsStatusCounts = json_encode($taskStatusCounts);
$jsDates = json_encode($taskDates);
$jsDateCounts = json_encode($taskCounts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="p-4 bg-light">

<?php include '/frontend/navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">Activity Overview</h1>

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

    <!-- Task Status Chart -->
    <div class="mb-5">
        <h4>Tasks by Status</h4>
        <canvas id="taskStatusChart" height="100"></canvas>
    </div>

    <!-- Time Trend Chart -->
    <div class="mb-5">
        <h4>Tasks Created Over Time</h4>
        <canvas id="taskTrendChart" height="100"></canvas>
    </div>

    <a href="/frontend/admin/admin-dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
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
                backgroundColor: ['#4caf50', '#ffeb3b', '#f44336'],
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
            labels: <?php echo $jsDates; ?>,
            datasets: [{
                label: 'Tasks Created',
                data: <?php echo $jsDateCounts; ?>,
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
                    text: 'Task Creation Over Time'
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
</body>
</html>
<?php
$conn->close();
?>
