<?php
// clears session and sends user to homepage.
session_start();
session_unset();
session_destroy();
header("Location: /index.php"); // Redirect to homepage
exit;
?>
