<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to regular login
    header("Location: login.html");
    exit;
}

// User is logged in, check role
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // User is admin, redirect to admin panel
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access Denied - CC Blogs</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/register.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div id="header"></div>
    
    <nav class="breadcrumb">
        <a href="index.php">Home</a> &gt; 
        <span>Admin Access</span>
    </nav>
    
    <div class="form-container">
        <h2>Access Denied</h2>
        <p>Must be logged in to an admin account to view the admin portal.</p>
        <div class="form-buttons">
            <a href="index.php" class="button">Return to Home</a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#header").load("header.php");
        });
    </script>
</body>
</html> 