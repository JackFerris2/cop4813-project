<?php
// active session check
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /index.php");
    exit;
}

// check get passed fields
$taskID = $_POST['task_id'] ?? '';
$taskTitle = $_POST['taskTitle'] ?? '';
$taskDescription = $_POST['taskDescription'] ?? '';
$taskStatus = $_POST['taskStatus'] ?? '';

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
if (!empty($taskID) && !empty($taskTitle) && !empty($taskStatus)) {

    // get task owner
    $msg = $conn->prepare("SELECT * FROM tasks WHERE task_id = ?");
    $msg->bind_param("i", $taskID);
    $msg->execute();
    $result = $msg->get_result();
    $current = $result->fetch_assoc();
    $msg->close();

    // Check if task owner is updating the task
    if(!$current || !isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $current['user_id']) {
	    header("Location: /index.php");
	    exit;
    }

    // update task
    $msg = $conn->prepare("UPDATE tasks SET title = ?, description = ?, status = ? WHERE task_id = ?");
    $msg->bind_param("sssi", $taskTitle, $taskDescription, $taskStatus, $taskID);
    $msg->execute();
    
    header("Location: /frontend/dashboard.php");
    $msg->close();
    exit;

    // Close mesaage to DB
} else {
    echo "All fields are required.";
}

// close connection before EOF
$conn->close();
?>
