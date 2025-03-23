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

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);

    // Increment like_count
    $stmt = $pdo->prepare("UPDATE blogs SET like_count = like_count + 1 WHERE id = :id");
    $stmt->execute([':id' => $blog_id]);

    // Retrieve updated like count
    $stmt = $pdo->prepare("SELECT like_count FROM blogs WHERE id = :id");
    $stmt->execute([':id' => $blog_id]);
    $result = $stmt->fetch();

    echo json_encode(['success' => true, 'like_count' => $result['like_count']]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>