<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Session & admin check
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: /index.php");
    exit;
}

// Analyze Apache logs
$logPath = '/var/log/apache2/access.log'; // Update this if your log is elsewhere
$pageCounts = [];

if (file_exists($logPath)) {
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (preg_match('/"GET\s(.*?)\sHTTP/', $line, $matches)) {
            $page = strtok($matches[1], '?'); // strip query strings
            $pageCounts[$page] = ($pageCounts[$page] ?? 0) + 1;
        }
    }
}

// Top 10 visited
arsort($pageCounts);
$topPages = array_slice($pageCounts, 0, 10);
$labels = json_encode(array_keys($topPages));
$counts = json_encode(array_values($topPages));
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

<div class="container">
    <a href="/frontend/admin/admin-dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>
    <h1 class="mb-4">Most Visited Pages</h1>

    <?php if (!empty($topPages)): ?>
        <canvas id="pageTrafficChart" height="100"></canvas>
    <?php else: ?>
        <div class="alert alert-warning">No access log data available or insufficient permissions.</div>
    <?php endif; ?>
</div>

<script>
const ctx = document.getElementById('pageTrafficChart')?.getContext('2d');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo $labels; ?>,
            datasets: [{
                label: 'Page Visits',
                data: <?php echo $counts; ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
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
                    title: { display: true, text: 'Page' }
                },
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Visits' }
                }
            }
        }
    });
}
</script>
</body>
</html>
