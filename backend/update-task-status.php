<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

// Connect to database
$servername = "localhost";
$username = "taskmanager";
$password = "password25";
$database = "taskmanagement";

$pdo = new PDO("mysql:host=$servername;dbname=$database;charset=utf8", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Handle AJAX POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = $_POST['taskId'] ?? null;
    $status = $_POST['status'] ?? null;

    // Validate against allowed statuses
    $validStatuses = ['not_started', 'in_progress', 'completed'];

    if ($taskId && $status && in_array($status, $validStatuses)) {
        // Confirm the task belongs to the current user
        $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
        $success = $stmt->execute([$status, $taskId, $_SESSION['user_id']]);

        if ($success) {
            echo "Success";
        } else {
            http_response_code(500);
            echo "Database update failed.";
        }
    } else {
        http_response_code(400);
        echo "Missing or invalid parameters.";
    }
} else {
    http_response_code(405);
    echo "Invalid request method.";
}
?>
