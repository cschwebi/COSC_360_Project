<?php
session_start();   // Start or resume session
session_unset();   // Unset all session variables
session_destroy(); // Destroy the session entirely

// Optional: Redirect to login page or homepage
header("Location: ..\index.html");
exit;
?>