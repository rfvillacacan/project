<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Unset temporary registration session variables
unset($_SESSION['temp_secret']);
unset($_SESSION['temp_username']);
unset($_SESSION['temp_role']);
// Redirect to registration page
header('Location: ?p=register.php');
exit(); 
