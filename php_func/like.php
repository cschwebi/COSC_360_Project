<?php
require_once 'configure.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to like posts.']);
    exit;
}

$blog_id = intval($_POST['blog_id']);
$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
    
    // Check if post alrready liked by user
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = :user_id AND blog_id = :blog_id");
    $stmt->execute([':user_id' => $user_id, ':blog_id' => $blog_id]);
    $existing_like = $stmt->fetch();

    if ($existing_like) {
        // User has already liked the post, so unlike it
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = :user_id AND blog_id = :blog_id");
        $stmt->execute([':user_id' => $user_id, ':blog_id' => $blog_id]);
        $action = 'unliked';
    } else {
        // User hasn't liked the post, so like it
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, blog_id) VALUES (:user_id, :blog_id)");
        $stmt->execute([':user_id' => $user_id, ':blog_id' => $blog_id]);
        $action = 'liked';
    }

    // Get the updated like count
    $stmt = $pdo->prepare("SELECT like_count FROM blogs WHERE id = :blog_id");
    $stmt->execute([':blog_id' => $blog_id]);
    $result = $stmt->fetch();

    echo json_encode([
        'success' => true, 
        'like_count' => $result['like_count'],
        'action' => $action
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>