<?php
// active session check
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /index.php");
    exit;
}

// get fields
$taskID = $_POST['task_id'] ?? '';

// DB creds
$host = 'localhost';
$dbname = 'taskmanagement';
$username = 'taskmanager';
$password = 'password25';

// connect to DB
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// check if the passed information is empty
if (!empty($taskID) && is_numeric($taskID)) {
    // Get owner
    $msg = $conn->prepare("SELECT * FROM tasks WHERE task_id = ?;");
    $msg->bind_param("i", $taskID);
    $msg->execute();
    $result = $msg->get_result();
    $current = $result->fetch_assoc();
    $msg->close();

    // Check owner is person deleting
    if (!$current || !isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $current['user_id']) {
        header("Location: /index.php");
        exit;
    }

    // delete task
    $msg = $conn->prepare("DELETE FROM tasks WHERE task_id = ?;");
    $msg->bind_param("i", $taskID);
    $msg->execute();

    // go to dashboard
    header("Location: /frontend/dashboard.php");
    $msg->close();
    exit;
} else {
    echo "Missing taskID";
}

// close connection before EOF
$conn->close();
?>
