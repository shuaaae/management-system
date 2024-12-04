<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'users_db');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Start session
session_start();

// Session timeout settings
define('SESSION_TIMEOUT', 1800); // 30 minutes

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    // If the session has been inactive for too long, destroy it
    session_unset();
    session_destroy();
    header("Location: /management-system/"); // Redirect to login page
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time
?>
