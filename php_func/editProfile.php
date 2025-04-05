<?php
require_once 'configure.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $fields = [];
    $params = [':id' => $userId];

    if (!empty($_POST['username'])) {
        $fields[] = 'fullName = :fullName';
        $params[':fullName'] = trim($_POST['username']);
        $_SESSION['displayName'] = $params[':fullName'];
    }

    if (!empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $fields[] = 'email = :email';
        $params[':email'] = trim($_POST['email']);
        $_SESSION['email'] = $params[':email'];
    }

    if (!empty($_POST['password']) && $_POST['password'] === $_POST['confirm-password']) {
        $fields[] = 'password = :password';
        $params[':password'] = trim($_POST['password']);
    }

    if (!empty($_POST['bio'])) {
        $fields[] = 'bio = :bio';
        $params[':bio'] = trim($_POST['bio']);
        $_SESSION['bio'] = $params[':bio'];
    }

    // Handle profile picture upload if provided
    if (isset($_FILES['profile-picture']) && $_FILES['profile-picture']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['profile-picture']['type'], $allowedTypes)) {
            $uploadDir = IMAGE_PATH;
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            //$uniqueName = time() . '_' . basename($_FILES['profile-picture']['name']);
            $uniqueName = basename($_FILES['profile-picture']['name']);
            $uploadPath = $uploadDir . $uniqueName;

            if (move_uploaded_file($_FILES['profile-picture']['tmp_name'], '../' . $uploadPath)) {
                $fields[] = 'profileImage = :profileImage';
                $params[':profileImage'] = $uploadPath;
                $_SESSION['profileImage'] = $uploadPath;
            } else {
                echo "<script>alert('Profile image upload failed.'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Invalid image type.'); window.history.back();</script>";
            exit;
        }
    }

    if (!empty($fields)) {
        try {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
            $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            header("Location: ../profile.php");
            exit;
        } catch (PDOException $e) {
            echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
            exit;
        }
    } else {
        header("Location: ../profile.php");
        exit;
    }
} else {
    header("Location: ../profile.php");
    exit;
}