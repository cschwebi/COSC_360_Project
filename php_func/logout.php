<?php
session_start();   // Start or resume session
session_unset();   // Unset all session variables
session_destroy(); // Destroy the session entirely

$folder = '../uploads/';
$files = glob($folder . '*'); // get all file names

foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file); // delete files in folder (cahched photos)
    }
}


var_dump($_SESSION);
header("Location: ../index.php");
exit;
?>