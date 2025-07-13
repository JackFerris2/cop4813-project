<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: /index.php");
    exit;
}

$timeFilter = $_GET['time'] ?? 'all';
$logFile = '/var/log/apache2/access.log';
$pageCounts = [];

if (file_exists($logFile) && is_readable($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (!preg_match('/\[(.*?)\] "(GET|POST) (.*?) HTTP\//', $line, $matches)) continue;

        $datetime = DateTime::createFromFormat('d/M/Y:H:i:s O', $matches[1]);
        if (!$datetime) continue;

        if ($timeFilter === 'today' && $datetime->format('Y-m-d') !== date('Y-m-d')) continue;
        if ($timeFilter === 'week' && $datetime < (new DateTime('-7 days'))) continue;

        $path = parse_url($matches[3], PHP_URL_PATH);
        if (!$path || !is_string($path)) continue;

        $cleanPath = strtok((string)$path, '?');
        if (!$cleanPath) continue;

        $pageCounts[$cleanPath] = ($pageCounts[$cleanPath] ?? 0) + 1;
    }
}

arsort($pageCounts);
$topPages = array_slice($pageCounts, 0, 10, true);
$jsLabels = json_encode(array_keys($topPages));
$jsCounts = json_encode(array_values($topPages));
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
    <h2 class="mb-4">Most Visited Pages</h2>

    <form method="get" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="time" class="form-label">Time Frame</label>
            <select id="time" name="time" class="form-select">
                <option value="all" <?= $timeFilter === 'all' ? 'selected' : '' ?>>All</option>
                <option value="today" <?= $timeFilter === 'today' ? 'selected' : '' ?>>Today</option>
                <option value="week" <?= $timeFilter === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
        </div>
    </form>

    <?php if (!empty($topPages)) : ?>
        <div>
            <canvas id="trafficChart" height="120"></canvas>
        </div>
    <?php else : ?>
        <div class="alert alert-warning">No access log data available or insufficient permissions.</div>
    <?php endif; ?>
</div>

<script>
const ctx = document.getElementById('trafficChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= $jsLabels ?>,
        datasets: [{
            label: 'Visits',
            data: <?= $jsCounts ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'Most Visited Pages'
            }
        },
        scales: {
            x: {
                ticks: {
                    autoSkip: false,
                    maxRotation: 45,
                    minRotation: 45
                },
                title: {
                    display: true,
                    text: 'Page'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Visits'
                }
            }
        }
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
