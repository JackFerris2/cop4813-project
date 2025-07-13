<?php
<<<<<<< HEAD
// active session check
session_start();
=======
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth checks
>>>>>>> a2641fe5990d0c679d4e824936b6f3b4c3a107fc
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /index.php");
    exit;
}
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

// USER STATS
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

// Histogram group
$group = $_GET['group'] ?? '';

// Group-based user count query
switch (strtolower($group)) {
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
        if (strtolower($group) === 'week') {
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
$taskCount = 0;
$taskCompleteCount = 0;
$blockedCount = 0;

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

// Status distribution
$taskStatusLabels = [];
$taskStatusCounts = [];

$result = $conn->query("SELECT status, COUNT(*) AS count FROM tasks GROUP BY status");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $taskStatusLabels[] = str_replace('_', ' ', $row['status']);
        $taskStatusCounts[] = (int)$row['count'];
    }
}

$jsStatusLabels = json_encode($taskStatusLabels);
$jsStatusCounts = json_encode($taskStatusCounts);
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
    <!-- User Statistics -->
    <h1>User Statistics</h1>
    <p>
        <?php echo "Total Users: " . htmlspecialchars($userCount); ?><br>
        <?php echo "Active Users: " . htmlspecialchars($activeCount); ?><br>
        <?php echo "Inactive Users: " . htmlspecialchars($userCount - $activeCount); ?>
    </p>

    <!-- Time Distribution Buttons -->
    <div class="btn-group mb-3" role="group">
        <a href="?group=Day" class="btn btn-outline-primary <?php echo strtolower($group) == 'day' ? 'active' : ''; ?>">Day</a>
        <a href="?group=Week" class="btn btn-outline-primary <?php echo strtolower($group) == 'week' ? 'active' : ''; ?>">Week</a>
        <a href="?group=Month" class="btn btn-outline-primary <?php echo strtolower($group) == 'month' ? 'active' : ''; ?>">Month</a>
    </div>

    <canvas id="startdateChart"></canvas>

    <script>
        const ctx = document.getElementById('startdateChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $jsTime; ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?php echo $jsCount; ?>,
                    backgroundColor: 'rgba(20, 85, 255, .75)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Signup <?php echo ucfirst($group ?: "Day"); ?>'
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

    <!-- Task Overview -->
    <h1>Activity Overview</h1>
    <p>
        <?php echo "Total Tasks: " . htmlspecialchars($taskCount); ?><br>
        <?php echo "Blocked Tasks: " . htmlspecialchars($blockedCount); ?><br>
        <?php echo "Average tasks per user: " . htmlspecialchars($userCount > 0 ? round($taskCount / $userCount, 2) : 0); ?><br>
        <?php echo "Percent of tasks complete: " . htmlspecialchars($taskCount > 0 ? round(100 * $taskCompleteCount / $taskCount, 2) . '%' : '0%'); ?>
    </p>

    <canvas id="taskStatusChart"></canvas>

    <script>
        const statusCtx = document.getElementById('taskStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: <?php echo $jsStatusLabels; ?>,
                datasets: [{
                    label: 'Tasks by Status',
                    data: <?php echo $jsStatusCounts; ?>,
                    backgroundColor: [
                        'rgba(0, 255, 0, .7)',
                        'rgba(255, 255, 0, .7)',
                        'rgba(255, 0, 0, .7)'
                    ],
                    borderColor: 'black'
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

    <a href="/frontend/admin/admin-dashboard.php" class="btn btn-secondary mt-4">Back to Admin Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
