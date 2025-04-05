<?php
require_once 'configure.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize form values
    $fullName       = trim($_POST['fullName']);
    $email          = trim($_POST['email']);
    $password       = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $profilePicture = $_FILES['profilePicture'];

    // Validation
    $errors = [];

    if ($fullName === "") $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    if ($password === "") $errors[] = "Password is required.";
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";
    if (!$profilePicture || $profilePicture['error'] !== 0) $errors[] = "Profile picture upload failed.";

    // Validate image file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($profilePicture['type'], $allowedTypes)) {
        $errors[] = "Invalid image type. Only JPG and PNG are allowed.";
    }

    if (!empty($errors)) {
        foreach ($errors as $e) echo "<p>$e</p>";
        exit;
    }

    // Prepare file upload
    $uploadDir = IMAGE_PATH;
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $uniqueName = time() . '_' . basename($profilePicture['name']);
    $uploadPath = $uploadDir . $uniqueName;

    if (!move_uploaded_file($profilePicture['tmp_name'], '../' . $uploadPath)) {
        echo "<script>alert('Image upload failed'); window.history.back();</script>";
        exit;
    }

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);

        // s3ee if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            echo "<script>alert('Email already registered.'); window.history.back();</script>";
            exit;
        }

        $hashed = md5($password);

        //Insert user 
        $stmt = $pdo->prepare("INSERT INTO users (fullName, email, password, profileImage) VALUES (:fullName, :email, :password, :profileImage)");

        $stmt->execute([
            ':fullName'     => $fullName,
            ':email'        => $email,
            ':password'     => $hashed, // hashed pass
            ':profileImage' => $uploadPath
        ]);

        $userId = $pdo->lastInsertId();

        //log the user in
        $_SESSION['user_id']      = $userId;
        $_SESSION['displayName']  = $fullName;
        $_SESSION['email']        = $email;
        $_SESSION['profileImage'] = $uploadPath;
        $_SESSION['bio']          = '';
        $_SESSION['role']         = 'user';
        $_SESSION['created_at']   = date('Y-m-d H:i:s');
        $_SESSION['updated_at']   = date('Y-m-d H:i:s');

        header("Location: ../profile.php");
        exit;

    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit;
    }
} else {
    header("Location: ../register.html");
    exit;
}
?>