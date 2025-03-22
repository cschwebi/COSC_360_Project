<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Database connection parameters
    $host = 'localhost';
    $db   = 'blogDB';
    $user = 'root';      // Use a dedicated user, not root
    $pass = ''; // Replace with your actual password
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // In production, log this error instead of displaying it
        die("Database connection failed: " . $e->getMessage());
    }

    // Fetch the user from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $userRecord = $stmt->fetch();

    //if ($userRecord && password_verify($password, $userRecord['password'])) {
    if ($userRecord && $userRecord['password'] === $password) {
        // Successful login, store necessary data in session
        $_SESSION['user_id'] = $userRecord['id'];
        $_SESSION['displayName'] = $userRecord['displayName'];
        // Redirect to a protected page, e.g., profile.php or index.html
        header("Location: ..\profile.html");
        exit;
    } else {
        // Failed login: Optionally, you can redirect back with an error message
        // For now, we'll just show an error
        echo "<p>Invalid email or password. Please try again.<p></br>";
    }
} else {
    // If not a POST request, redirect to login page
    header("Location: login.html");
    exit;
}
?>