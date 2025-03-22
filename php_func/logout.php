<?php
echo "<script>console.log('PHP says: " . $_SESSION["user_id"] . "');</script>";
session_start();   // Start or resume session
session_unset();   // Unset all session variables
session_destroy(); // Destroy the session entirely

var_dump($_SESSION);
header("Location: ../index.html");
exit;
?>