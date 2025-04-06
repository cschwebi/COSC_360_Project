<?php
require_once 'configure.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['blog_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$blogId = intval($_POST['blog_id']);
$userId = $_SESSION['user_id'];

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
    
    // Check if already favourited
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM favorites WHERE user_id = :user_id AND blog_id = :blog_id");
    $stmt->execute([':user_id' => $userId, ':blog_id' => $blogId]);
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Already favourited.']);
        exit;
    }
    
    // Insert into favourites
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, blog_id) VALUES (:user_id, :blog_id)");
    $stmt->execute([':user_id' => $userId, ':blog_id' => $blogId]);
    
    echo json_encode(['success' => true, 'message' => 'Added to favourites.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>