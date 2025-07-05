<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to database
$servername = "localhost";
$username = "taskmanager";
$password = "password25";
$database = "taskmanagement";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database connection error: " . $e->getMessage();
    exit;
}

// Handle AJAX POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log or display what was sent
    error_log("POST DATA: " . print_r($_POST, true));

    $taskId = $_POST['taskId'] ?? null;
    $status = $_POST['status'] ?? null;

    $validStatuses = ['not_started', 'in_progress', 'completed'];

    if ($taskId && $status && in_array($status, $validStatuses)) {
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
        echo "Failed to update task status: Missing or invalid parameters.";
    }
} else {
    http_response_code(405);
    echo "Invalid request method.";
}
