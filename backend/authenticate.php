<?php
session_start();

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
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// check if the passed information is empty
if (!empty($email) && !empty($password)) {
    // add user
    $msg = $conn->prepare("SELECT user_id, pw_hash, admin, active FROM users WHERE email = ?");

    if ($msg) {
	// get stored pw    
	$msg->bind_param("s", $email);
	$msg->execute();
	$msg->bind_result($user_id, $user_pwh, $user_admin, $active);

	// compare hashed pw
	if($msg->fetch()) {	
		if (password_verify($password, $user_pwh)) {
			// check if account is active
			if ($active == False) {
				header("Location: /frontend/login.php?error=inactive");
				exit;
			}
			
			// Set Session and go to dashboard
			$_SESSION['user_id'] = $user_id;
			$_SESSION['user_email'] = $email;
			$_SESSION['logged_in'] = true;
			// Set any admin permissions by level
			switch ($user_admin) {
			case 1:
				$_SESSION['is_admin'] = $user_admin;
				break;
			default:
				$_SESSION['is_admin'] = 0;
				break;
			}

			// go to the dashboard.
			header("Location: /frontend/dashboard.php");
			exit;
		} else {
			// bad credentials redirect to login with invalid credential error
			header("Location: /frontend/login.php?error=invalid");
			exit;
		}	
	} else {
		header("Location: /frontend/login.php?error=invalid");
		exit;
	}
	$msg->close();
    } else {
	    echo "SQL Error" . $conn->error;
    }   
} else {
	header("Location: /frontend/login.php?error=invalid");
	exit;
}


// close connection before EOF
$conn->close();
?>
