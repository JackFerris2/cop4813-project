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

// check if fields are null
$userID = (int) trim($_POST['user_id'] ?? '');
$fullName = trim($_POST['fullName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// check if the passed information is empty
if (!empty($userID) && !empty($fullName) && !empty($email) && !empty($password)) {

    $hashPW = password_hash($password, PASSWORD_DEFAULT);

    // update user row
    $msg = $conn->prepare("UPDATE users SET name = ?, email = ?, pw_hash = ? WHERE user_id = ?");
    $msg->bind_param("sssi", $fullName, $email, $hashPW, $userID);
    $msg->execute();
    
    // Close mesaage to DB
    $msg->close();
    
    header("Location: /frontend/admin/admin-users.php");
    exit;

    // Check if password doesn't need update
} else if (!empty($userID) && !empty($fullName) && !empty($email) && empty($password)) {
    // update user row
    $msg = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
    $msg->bind_param("ssi", $fullName, $email, $userID);
    $msg->execute();

    // Close mesaage to DB
    $msg->close();
    header("Location: /frontend/admin/admin-users.php");
    exit; 
} else {
    echo "All fields are required.";
}

// close connection before EOF
$conn->close();
?>
