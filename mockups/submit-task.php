<?php
$servername = "localhost";
$username = "webuser";
$password = "WebPass4813!"; // set password if needed
$database = "taskmanagement";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$status = $_POST['status'] ?? '';
$user_id = 1; // hardcoded user ID

if ($title && $description && $status) {
    $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, status, created, updated) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("isss", $user_id, $title, $description, $status);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit;
} else {
    echo "Please fill out all fields.";
}

$conn->close();
?>
