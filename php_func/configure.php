<?php
// Database Configuration Constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'clewendo');
define('DB_USER', 'clewendo');
define('DB_PASS', 'pass');
define('DB_CHARSET', 'utf8mb4');
define('IMAGE_PATH', "/uploads/");
define('PROJ_PATH', "/COSC_360_Project/");

// Construct DSN (Data Source Name)
define('DB_DSN', "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET);

// PDO Options
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);
?> 