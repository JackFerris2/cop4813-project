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

// get task_id to flag
$taskID = (int) ($_POST['task_id'] ?? 0);

// check if the passed information is empty or if current user
if ($taskID !== 0) {
    // add get active status
    $msg = $conn->prepare("SELECT censor FROM tasks WHERE task_id = ?");

    if ($msg) {    
	$msg->bind_param("i", $taskID);
	$msg->execute();
	$msg->bind_result($current_flag);

	// if status is retrieved
	if($msg->fetch()) {
		$msg->close();
		if ($current_flag == 1) {
			// flip to inactive
			$msg2 = $conn->prepare("UPDATE tasks SET censor = 0 WHERE task_id = ?");
			$msg2->bind_param("i", $taskID);
			$msg2->execute();	
			$msg2->close();

			// go to admin-users
			header("Location: /frontend/admin/admin-tasks.php");
			exit;
		} else {
			$msg2 = $conn->prepare("UPDATE tasks SET censor = 1 WHERE task_id = ?");
			$msg2->bind_param("i", $taskID);
			$msg2->execute();
			$msg2->close();

			// fo to admin-users
			header("Location: /frontend/admin/admin-tasks.php");
			exit;
		}
	} else {
		header("Location: /frontend/admin/admin-tasks.php");
		exit;
	}
	$msg->close();
    } else {
	    echo "SQL Error" . $conn->error;
    }   
} else {
	header("Location: /frontend/admin/admin-tasks.php");
	exit;
}


// close connection before EOF
$conn->close();
?>
