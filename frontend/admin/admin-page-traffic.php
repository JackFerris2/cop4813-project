<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /index.php");
    exit;
}
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: /index.php");
    exit;
}

// Handle filters
$timeFrame = $_GET['time'] ?? 'all';
$userRole = $_GET['role'] ?? 'all';

$logPath = '/var/log/apache2/access.log';
$visits = [];

$pageTitles = [
    '/frontend/admin/admin-analytics.php' => 'Analytics',
    '/frontend/admin/admin-dashboard.php' => 'Admin Dashboard',
    '/frontend/dashboard.php' => 'User Dashboard',
    '/frontend/login.php' => 'Login Page',
    '/' => 'Homepage',
    '/favicon.ico' => 'Favicon',
    '/.env' => 'Environment File',
];

if (is_readable($logPath)) {
    $lines = file($logPath);
    $now = time();

    foreach ($lines as $line) {
        if (!preg_match('/\[(.*?)\] \"GET (.*?) HTTP/', $line, $matches)) continue;

        $timestamp = strtotime(str_replace('/', ' ', $matches[1]));
        $uri = strtok($matches[2], '?');

        // Filter by time
        if ($timeFrame === 'today' && date('Y-m-d', $timestamp) !== date('Y-m-d')) continue;
        if ($timeFrame === 'week' && $now - $timestamp > 7 * 24 * 60 * 60) continue;

        // Only track known pages or label as "Other"
        $label = $pageTitles[$uri] ?? $uri;
        $visits[$label] = ($visits[$label] ?? 0) + 1;
    }

    arsort($visits);
} else {
    $visits = null;
}

$labels = json_encode(array_keys($visits ?? []));
$counts = json_encode(array_values($visits ?? []));
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
<?php include '../navbar.php'; ?>
<a href="admin-dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>
<div class="container">
    <h1 class="mb-4">Most Visited Pages</h1>

    <form method="get" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="time" class="form-label">Time Frame</label>
            <select class="form-select" name="time" id="time">
                <option value="all" <?= $timeFrame === 'all' ? 'selected' : '' ?>>All</option>
                <option value="today" <?= $timeFrame === 'today' ? 'selected' : '' ?>>Today</option>
                <option value="week" <?= $timeFrame === 'week' ? 'selected' : '' ?>>This Week</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="role" class="form-label">User Role</label>
            <select class="form-select" name="role" id="role">
                <option value="all" <?= $userRole === 'all' ? 'selected' : '' ?>>All Users</option>
                <option value="admin" <?= $userRole === 'admin' ? 'selected' : '' ?>>Admins Only</option>
                <option value="regular" <?= $userRole === 'regular' ? 'selected' : '' ?>>Regular Users</option>
            </select>
        </div>
        <div class="col-md-4 align-self-end">
            <button class="btn btn-primary w-100" type="submit">Apply Filters</button>
        </div>
    </form>

    <?php if ($visits === null): ?>
        <div class="alert alert-warning">No access log data available or insufficient permissions.</div>
    <?php elseif (empty($visits)): ?>
        <div class="alert alert-info">No visits found for the selected filter.</div>
    <?php else: ?>
        <canvas id="trafficChart" height="100"></canvas>
    <?php endif; ?>
</div>
<script>
<?php if (!empty($visits)): ?>
const ctx = document.getElementById('trafficChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= $labels ?>,
        datasets: [{
            label: 'Visits',
            data: <?= $counts ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
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
<?php endif; ?>
</script>
</body>
</html>