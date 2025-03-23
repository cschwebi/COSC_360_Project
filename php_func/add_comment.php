<?php
require_once 'configure.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to comment.']);
    exit;
}

$blog_id = intval($_POST['blog_id']);
$content = trim($_POST['content']);
$user_id = $_SESSION['user_id'];

if ($content === '') {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
    exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);

    // Insert comment
    $stmt = $pdo->prepare("INSERT INTO comments (blog_id, user_id, content) VALUES (:blog_id, :user_id, :content)");
    $stmt->execute([
        ':blog_id' => $blog_id,
        ':user_id' => $user_id,
        ':content' => $content
    ]);
    
    // Get the inserted comment's creation time
    $stmt = $pdo->prepare("SELECT created_at FROM comments WHERE id = :id");
    $stmt->execute([':id' => $pdo->lastInsertId()]);
    $commentData = $stmt->fetch();
    
    // Return successful response. You can also fetch the user's full name from session or DB.
    echo json_encode([
        'success' => true,
        'content' => htmlspecialchars($content),
        'user' => htmlspecialchars($_SESSION['displayName']),
        'created_at' => date('F j, Y g:i A', strtotime($commentData['created_at']))
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>