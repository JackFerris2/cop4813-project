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
$userCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;

// get active users
$activeCount = $conn->query("SELECT COUNT(*) AS active FROM users WHERE active = 1")->fetch_assoc()['active'] ?? 0;
$inactiveCount = $userCount - $activeCount;

// histogram group
$group = strtolower($_GET['group'] ?? 'day');

// SQL by period
switch ($group) {
    case 'week':
        $sql = "SELECT YEARWEEK(created, 1) AS period, COUNT(*) AS count FROM users GROUP BY period ORDER BY period";
        break;
    case 'month':
        $sql = "SELECT DATE_FORMAT(created, '%Y-%m') AS period, COUNT(*) AS count FROM users GROUP BY period ORDER BY period";
        break;
    default:
        $sql = "SELECT DATE(created) AS period, COUNT(*) AS count FROM users GROUP BY period ORDER BY period";
        break;
}

$result = $conn->query($sql);
$startDates = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($group === 'week') {
            $yearWeek = $row['period'];
            $year = substr($yearWeek, 0, 4);
            $week = substr($yearWeek, 4);
            $row['period'] = (new DateTime())->setISODate($year, $week)->format('Y-\WW');
        }
        $startDates[] = $row;
    }
}

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
<body class="p-4 bg-light">

<?php include '/frontend/navbar.php'; ?>

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

    <!-- Time filter buttons -->
    <div class="btn-group mb-4" role="group">
        <a href="?group=Day" class="btn btn-outline-primary <?php echo $group == 'day' ? 'active' : ''; ?>">Day</a>
        <a href="?group=Week" class="btn btn-outline-primary <?php echo $group == 'week' ? 'active' : ''; ?>">Week</a>
        <a href="?group=Month" class="btn btn-outline-primary <?php echo $group == 'month' ? 'active' : ''; ?>">Month</a>
    </div>

    <!-- Chart container -->
    <div class="mb-5">
        <h4>New User Registrations</h4>
        <canvas id="startdateChart" height="100"></canvas>
    </div>

    <a href="/frontend/admin/admin-dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
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
                text: 'New Users by <?php echo ucfirst($group); ?>'
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
