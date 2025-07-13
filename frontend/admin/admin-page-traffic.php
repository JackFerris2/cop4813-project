<?php
// Enable errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Auth checks
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: /index.php");
    exit;
}

// Filter GET
$timeFilter = $_GET['time'] ?? 'all';

// Define readable names
$pageNameMap = [
    '/' => 'Homepage',
    '/index.php' => 'Homepage',
    '/frontend/login.php' => 'Login Page',
    '/frontend/dashboard.php' => 'User Dashboard',
    '/frontend/admin/admin-dashboard.php' => 'Admin Dashboard',
    '/frontend/admin/admin-analytics.php' => 'Analytics',
    '/frontend/admin/admin-users.php' => 'User Management',
    '/frontend/admin/admin-tasks.php' => 'Task Moderation',
];

// Parse log
$logPath = '/var/log/apache2/access.log';
$pageCounts = [];

if (is_readable($logPath)) {
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (!preg_match('#\[(.*?)\] "GET (.*?) HTTP#', $line, $matches)) continue;

        $timestamp = strtotime($matches[1]);
        $path = parse_url($matches[2], PHP_URL_PATH);

        if ($timeFilter === 'today' && date('Y-m-d', $timestamp) !== date('Y-m-d')) continue;
        if ($timeFilter === 'week' && date('o-W', $timestamp) !== date('o-W')) continue;

        $cleanPath = strtok($path, '?');
        if (!$cleanPath) continue;

        $pageCounts[$cleanPath] = ($pageCounts[$cleanPath] ?? 0) + 1;
    }

    arsort($pageCounts);
    $pageCounts = array_slice($pageCounts, 0, 10, true);
}

$jsLabels = json_encode(array_map(fn($p) => $pageNameMap[$p] ?? $p, array_keys($pageCounts)));
$jsCounts = json_encode(array_values($pageCounts));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Most Visited Pages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="p-4">
<?php include __DIR__ . '/../navbar.php'; ?>
<a href="/frontend/admin/admin-dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

<div class="container">
    <h1 class="mb-4">Most Visited Pages</h1>

    <form method="get" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="time" class="form-label">Time Frame</label>
            <select id="time" name="time" class="form-select">
                <option value="all" <?= $timeFilter === 'all' ? 'selected' : '' ?>>All</option>
                <option value="today" <?= $timeFilter === 'today' ? 'selected' : '' ?>>Today</option>
                <option value="week" <?= $timeFilter === 'week' ? 'selected' : '' ?>>This Week</option>
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
        </div>
    </form>

    <?php if (!empty($pageCounts)): ?>
        <canvas id="visitsChart" height="100"></canvas>
    <?php else: ?>
        <div class="alert alert-warning">No access log data available or insufficient permissions.</div>
    <?php endif; ?>
</div>

<?php if (!empty($pageCounts)): ?>
<script>
const ctx = document.getElementById('visitsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= $jsLabels ?>,
        datasets: [{
            label: 'Visits',
            data: <?= $jsCounts ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: '#007bff',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Most Visited Pages'
            },
            legend: { display: false }
        },
        scales: {
            x: {
                title: { display: true, text: 'Page' },
                ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 }
            },
            y: {
                title: { display: true, text: 'Visits' },
                beginAtZero: true
            }
        }
    }
});
</script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
