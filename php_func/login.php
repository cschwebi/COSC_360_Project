<?php
require_once 'configure.php';

if (!isset($_SESSION['user_id'])){
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Retrieve and sanitize input
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        try {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
        } catch (PDOException $e) {
            //n production, log this error instead of displaying it
            die("Database connection failed: " . $e->getMessage());
        }

        // fetch the user from the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $userRecord = $stmt->fetch();

        $hashed = md5($password);

        //if ($userRecord && password_verify($password, $userRecord['password'])) {
        if ($userRecord && $userRecord['password'] === $hashed) {
            // Successful login, store necessary data in session
            $_SESSION['user_id']      = $userRecord['id'];
            $_SESSION['displayName']  = $userRecord['fullName'];
            $_SESSION['email']        = $userRecord['email'];
            $_SESSION['profileImage'] = $userRecord['profileImage'];
            $_SESSION['bio']          = $userRecord['bio'];
            $_SESSION['role']         = $userRecord['role'];
            $_SESSION['created_at']   = $userRecord['created_at'];
            $_SESSION['updated_at']   = $userRecord['updated_at'];
            // Redirect to a protected page, e.g., profile.php or index.php
            header("Location: ..\profile.php");
            exit;
        } else {
            // Failed login: Optionally, you can redirect back with an error message
            // For now, we show  error
            echo "<p>Invalid email or password. Please try again.<p></br>";
            header("Location: ..\login.html");
        }
    } else {
        // if not a POST request
        header("Location: ..\login.html");
        exit;
    }
}else{
    header("Location: index.php");
    exit;
}
?>