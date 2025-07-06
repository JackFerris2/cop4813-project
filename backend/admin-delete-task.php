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

// get fields
$taskID = $_POST['task_id'] ?? '';

// check if the passed information is empty
if (!empty($taskID) && is_numeric($taskID)) {
    // delete task
    $msg = $conn->prepare("DELETE FROM tasks WHERE task_id = ?;");
    $msg->bind_param("i", $taskID);
    $msg->execute();
    
    header("Location: /frontend/admin/admin-tasks.php");
    exit;

    // Close mesaage to DB
    $msg->close();
} else {
    echo "Missing taskID";
}

// close connection before EOF
$conn->close();
?>
