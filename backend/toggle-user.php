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
$email = trim($_POST['email'] ?? '');

// check if the passed information is empty
if (!empty($email)) {
    // add get active status
    $msg = $conn->prepare("SELECT active FROM users WHERE email = ?");

    if ($msg) {    
	$msg->bind_param("s", $email);
	$msg->execute();
	$msg->bind_result($current_status);

	// if status is retrieved
	if($msg->fetch()) {
		$msg->close();
		if ($current_status == 1) {
			// flip to inactive
			$msg2 = $conn->prepare("UPDATE users SET active = 0 WHERE email = ?");
			$msg2->bind_param("s", $email);
			$msg2->execute();	
			$msg2->close();

			// go to admin-users
			header("Location: /frontend/admin/admin-users.php");
			exit;
		} else {
			$msg2 = $conn->prepare("UPDATE users SET active = 1 WHERE email = ?");
			$msg2->bind_param("s", $email);
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
