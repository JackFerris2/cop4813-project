<?php
// active session check
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /index.php");
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "taskmanager";
$password = "password25";
$database = "taskmanagement";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Fetch user tasks
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [
    'not_started' => [],
    'in_progress' => [],
    'completed' => []
];

while ($row = $result->fetch_assoc()) {
    $tasks[$row['status']][] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .task-column {
            min-height: 300px;
        }
        .task-column.drag-over {
            background-color: #f1f1f1;
            border: 2px dashed #007bff;
        }
    </style>
</head>
<body class="p-4">

<?php include 'navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">My Tasks</h1>
    <div class="row text-center">
        <?php
        $statuses = ['not_started' => 'To Do', 'in_progress' => 'In Progress', 'completed' => 'Done'];
        foreach ($statuses as $key => $label): ?>
            <div class="col-md-4">
                <h3><?= htmlspecialchars($label) ?></h3>
                <div class="bg-light border rounded p-3 task-column" id="<?= htmlspecialchars($key) ?>">
                    <?php foreach ($tasks[$key] as $task): ?>
                        <div class="card mb-2" draggable="true" data-id="<?php echo htmlspecialchars($task['id']); ?>">
                            <div class="card-body">
                                <strong>
                                    <?= $task['censor'] ? "Censored" : htmlspecialchars($task['title']) ?>
                                </strong><br>
                                <small>
                                    <?= $task['censor'] ? "This task has been censored by an administrator." : htmlspecialchars($task['description']) ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-end mt-4">
        <a href="/frontend/tasks/create-task.php" class="btn btn-primary">+ Add Task</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.querySelectorAll('.card[draggable="true"]').forEach(card => {
    card.addEventListener('dragstart', e => {
        e.dataTransfer.setData("text/plain", card.dataset.id);
    });
});

document.querySelectorAll('.task-column').forEach(column => {
    column.addEventListener('dragover', e => {
        e.preventDefault();
        column.classList.add('drag-over');
    });

    column.addEventListener('dragleave', () => {
        column.classList.remove('drag-over');
    });

    column.addEventListener('drop', e => {
        e.preventDefault();
        column.classList.remove('drag-over');

        const taskId = e.dataTransfer.getData("text/plain");
        const taskCard = document.querySelector(`.card[data-id='${taskId}']`);

        if (!column.contains(taskCard)) {
            column.appendChild(taskCard);
        }

        const newStatus = column.id;
        console.log(`Sending update: taskId=${taskId}, status=${newStatus}`);

        fetch('../backend/update-task-status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `taskId=${encodeURIComponent(taskId)}&status=${encodeURIComponent(newStatus)}`
        })
        .then(response => response.text().then(text => {
            if (!response.ok || text !== "Success") {
                alert("Failed to update task status: " + text);
            }
        }))
        .catch(error => {
            console.error(error);
            alert("Error contacting the server.");
        });
    });
});
</script>

</body>
</html>
