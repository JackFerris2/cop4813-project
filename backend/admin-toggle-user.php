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

// check if the passed information is empty or if current user
if ($userID !== 0 && $userID !== $currentUID) {
    // add get active status
    $msg = $conn->prepare("SELECT active FROM users WHERE user_id = ?");

    if ($msg) {    
	$msg->bind_param("i", $userID);
	$msg->execute();
	$msg->bind_result($current_status);

	// if status is retrieved
	if($msg->fetch()) {
		$msg->close();
		if ($current_status == 1) {
			// flip to inactive
			$msg2 = $conn->prepare("UPDATE users SET active = 0 WHERE user_id = ?");
			$msg2->bind_param("i", $userID);
			$msg2->execute();	
			$msg2->close();

			// go to admin-users
			header("Location: /frontend/admin/admin-users.php");
			exit;
		} else {
			$msg2 = $conn->prepare("UPDATE users SET active = 1 WHERE user_id = ?");
			$msg2->bind_param("i", $userID);
			$msg2->execute();
			$msg2->close();

			// fo to admin-users
			header("Location: /frontend/admin/admin-users.php");
			exit;
		}
	} else {
		header("Location: /frontend/admin/admin-users.php");
		exit;
	}
	$msg->close();
    } else {
	    echo "SQL Error" . $conn->error;
    }   
} else {
	header("Location: /frontend/admin/admin-users.php");
	exit;
}


// close connection before EOF
$conn->close();
?>
