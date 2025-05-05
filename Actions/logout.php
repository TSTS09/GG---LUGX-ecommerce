<?php
// Actions/logout.php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Remove session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Redirect to home page
header("Location: ../index.php");
exit;
?>