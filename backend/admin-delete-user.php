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

// get user to activate/deactivate
$userID = (int) $_POST['user_id'] ?? 0;
$currentUID = (int) $_SESSION['user_id'];

// check if the passed information is empty
if ($userID !== 0 && $userID !== $currentUID) {
    // remove user
    $msg = $conn->prepare("DELETE FROM users WHERE user_id = ?");    
    $msg->bind_param("i", $userID);
    $msg->execute();
    $msg->close();
    // Remove tasks for that user
    $msg = $conn->prepare("DELETE FROM tasks WHERE user_id = ?");    
    $msg->bind_param("i", $userID);
    $msg->execute();
    $msg->close();
}

// go to admin-users
header("Location: /frontend/admin/admin-users.php");
exit;

// close connection before EOF
$conn->close();
?>
