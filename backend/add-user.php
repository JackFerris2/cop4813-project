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
$fullName = trim($_POST['fullName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// check if the passed information is empty
if (!empty($fullName) && !empty($email) && !empty($password)) {

    $hashPW = password_hash($password, PASSWORD_DEFAULT);

    // add user
    $msg = $conn->prepare("INSERT INTO users (name, email, pw_hash) VALUES (?, ?, ?)");

    if ($msg) {
        $msg->bind_param("sss", $fullName, $email, $hashPW);

        if ($msg->execute()) {
		header("Location: /frontend/login.php");
		exit;
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
