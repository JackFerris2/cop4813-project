<?php
// Secure session and admin check
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: /index.php");
    exit;
}

// Whitelisted pages and labels
$pageMap = [
    '/frontend/index.php' => 'Home',
    '/frontend/login.php' => 'Login',
    '/frontend/dashboard.php' => 'Dashboard',
    '/frontend/tasks/create-task.php' => 'Create Task',
    '/frontend/tasks/edit-task.php' => 'Edit Task',
    '/frontend/admin/admin-dashboard.php' => 'Admin Dashboard',
    '/frontend/admin/admin-analytics.php' => 'Analytics',
    '/frontend/admin/admin-users.php' => 'User Management',
    '/frontend/admin/admin-tasks.php' => 'Task Moderation',
    '/frontend/admin/admin-page-traffic.php' => 'Traffic Report'
];

$logPath = '/var/log/apache2/access.log';
$visitCounts = [];

if (is_readable($logPath)) {
    $logLines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($logLines as $line) {
        preg_match('/"GET (.*?) HTTP/', $line, $matches);
        if (!isset($matches[1])) continue;

        $url = strtok($matches[1], '?'); // Strip query params

        // Only count known pages
        if (isset($pageMap[$url])) {
            $label = $pageMap[$url];
            if (!isset($visitCounts[$label])) {
                $visitCounts[$label] = 0;
            }
            $visitCounts[$label]++;
        }
    }
}

arsort($visitCounts);
$topPages = array_slice($visitCounts, 0, 10, true);
$pageLabels = json_encode(array_keys($topPages));
$pageCounts = json_encode(array_values($topPages));
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

<a href="/frontend/admin/admin-dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

<div class="container">
    <h2 class="mb-4">Most Visited Pages</h2>

    <canvas id="visitChart" height="100"></canvas>
</div>

<script>
const ctx = document.getElementById('visitChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo $pageLabels; ?>,
        datasets: [{
            label: 'Visits',
            data: <?php echo $pageCounts; ?>,
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
                title: { display: false } // <--- just this
            },
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Visits' }
            }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
