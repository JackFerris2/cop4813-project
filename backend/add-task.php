<?php
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
$taskTitle = $_POST['taskTitle'] ?? '';
$taskDescription = $_POST['taskDescription'] ?? '';
$taskStatus = $_POST['taskStatus'] ?? '';

$user_id = 1;

// set status to enum in database
if ($taskStatus === "todo") {
	$status = "not_started";
} elseif ($taskStatus === "inprogress"){
	$status = "in_progress";
} else {
	$status = "completed";
}

// check if the passed information is empty
if (!empty($user_id) && !empty($taskTitle) && !empty($taskDescription) && !empty($taskStatus)) {

    // add task
    $msg = $conn->prepare("INSERT INTO tasks (user_id, title, description, status) VALUES (?, ?, ?, ?)");

    if ($msg) {
        $msg->bind_param("isss", $user_id, $taskTitle, $taskDescription, $status);

        if ($msg->execute()) {
            echo "Task created successfully";
        } else {
            echo "Database error: " . $msg->error;
        }

	// Close mesaage to DB
        $msg->close();
    } else {
        echo "SQL prepare failed: " . $conn->error;
    }
} else {
    echo "All fields are required.";
}

// close connection before EOF
$conn->close();
?>
